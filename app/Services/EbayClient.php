<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;
use Throwable;

/**
 * Thin wrapper around eBay OAuth + the Trading API (XML).
 *
 * Why the Trading API? Listings created in Seller Hub / the eBay app are only
 * visible through the Trading API (GetSellerList / GetSellerEvents / GetItem).
 * The newer REST Inventory API only sees listings that were created through it.
 */
class EbayClient
{
    public function cfg(string $key): mixed
    {
        return config('services.ebay.'.$key);
    }

    public function sandbox(): bool
    {
        return $this->cfg('env') === 'sandbox';
    }

    public function isConfigured(): bool
    {
        return filled($this->cfg('client_id')) && filled($this->cfg('client_secret')) && filled($this->cfg('runame'));
    }

    public function isConnected(): bool
    {
        return filled($this->refreshToken());
    }

    // ------------------------------------------------------------------ OAuth

    public function authorizeUrl(string $state): string
    {
        $base = $this->sandbox() ? 'https://auth.sandbox.ebay.com' : 'https://auth.ebay.com';

        return $base.'/oauth2/authorize?'.http_build_query([
            'client_id' => $this->cfg('client_id'),
            'response_type' => 'code',
            'redirect_uri' => $this->cfg('runame'),
            'scope' => $this->cfg('scopes'),
            'state' => $state,
            'prompt' => 'login',
        ], '', '&', PHP_QUERY_RFC3986);
    }

    /** Exchange the ?code= from eBay's consent screen for tokens and store the refresh token. */
    public function exchangeCode(string $code): void
    {
        $data = $this->tokenRequest([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->cfg('runame'),
        ]);

        if (empty($data['refresh_token'])) {
            throw new EbayException('eBay did not return a refresh token.');
        }

        Setting::set('ebay_refresh_token', Crypt::encryptString($data['refresh_token']));
        Setting::set('ebay_refresh_token_expires_at', now()->addSeconds((int) ($data['refresh_token_expires_in'] ?? 0))->toIso8601String());
        Cache::put($this->tokenCacheKey(), $data['access_token'], now()->addSeconds(max(60, (int) $data['expires_in'] - 120)));

        try {
            $user = $this->call('GetUser', '');
            Setting::set('ebay_user_id', (string) $user->User->UserID);
        } catch (Throwable) {
            // not critical
        }
    }

    public function disconnect(): void
    {
        Setting::set('ebay_refresh_token', null);
        Setting::set('ebay_user_id', null);
        Cache::forget($this->tokenCacheKey());
    }

    public function refreshToken(): ?string
    {
        $stored = Setting::get('ebay_refresh_token');
        if ($stored) {
            try {
                return Crypt::decryptString($stored);
            } catch (Throwable) {
                // APP_KEY changed — fall through to env token
            }
        }

        return $this->cfg('refresh_token') ?: null;
    }

    public function accessToken(): string
    {
        $cached = Cache::get($this->tokenCacheKey());
        if ($cached) {
            return $cached;
        }

        $refresh = $this->refreshToken();
        if (! $refresh) {
            throw new EbayException('eBay is not connected yet. Go to Admin → eBay and click "Connect eBay account".');
        }

        $data = $this->tokenRequest([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh,
            'scope' => $this->cfg('scopes'),
        ]);

        Cache::put($this->tokenCacheKey(), $data['access_token'], now()->addSeconds(max(60, (int) $data['expires_in'] - 120)));

        return $data['access_token'];
    }

    private function tokenRequest(array $form): array
    {
        $url = $this->sandbox()
            ? 'https://api.sandbox.ebay.com/identity/v1/oauth2/token'
            : 'https://api.ebay.com/identity/v1/oauth2/token';

        $res = Http::asForm()
            ->withBasicAuth((string) $this->cfg('client_id'), (string) $this->cfg('client_secret'))
            ->timeout(20)
            ->post($url, $form);

        if (! $res->successful() || ! $res->json('access_token')) {
            $msg = $res->json('error_description') ?: $res->json('error') ?: $res->body();
            throw new EbayException('eBay OAuth failed: '.$msg);
        }

        return $res->json();
    }

    private function tokenCacheKey(): string
    {
        return 'ebay.access_token.'.md5((string) $this->cfg('client_id'));
    }

    // ------------------------------------------------------------ Trading API

    /**
     * Call a Trading API method. $innerXml is the request body inside the <XxxRequest> element.
     */
    public function call(string $callName, string $innerXml): SimpleXMLElement
    {
        $url = $this->sandbox() ? 'https://api.sandbox.ebay.com/ws/api.dll' : 'https://api.ebay.com/ws/api.dll';

        $body = '<?xml version="1.0" encoding="utf-8"?>'
            .'<'.$callName.'Request xmlns="urn:ebay:apis:eBLBaseComponents">'
            .'<ErrorLanguage>en_GB</ErrorLanguage><WarningLevel>High</WarningLevel>'
            .$innerXml
            .'</'.$callName.'Request>';

        $res = Http::withHeaders([
            'X-EBAY-API-CALL-NAME' => $callName,
            'X-EBAY-API-SITEID' => (string) $this->cfg('site_id'),
            'X-EBAY-API-COMPATIBILITY-LEVEL' => (string) $this->cfg('compat_level'),
            'X-EBAY-API-IAF-TOKEN' => $this->accessToken(),
            'Content-Type' => 'text/xml',
        ])->timeout(60)->withBody($body, 'text/xml')->post($url);

        if (! $res->successful()) {
            throw new EbayException("eBay {$callName} HTTP {$res->status()}: ".mb_substr($res->body(), 0, 300));
        }

        // Drop the default namespace so SimpleXML property access is straightforward.
        $xml = @simplexml_load_string(str_replace('xmlns="urn:ebay:apis:eBLBaseComponents"', '', $res->body()));
        if (! $xml) {
            throw new EbayException("eBay {$callName}: could not parse response.");
        }

        $ack = (string) $xml->Ack;
        if ($ack === 'Failure' || $ack === 'PartialFailure') {
            $errors = [];
            foreach ($xml->Errors as $e) {
                $errors[] = trim((string) ($e->LongMessage ?: $e->ShortMessage)).' ('.$e->ErrorCode.')';
            }
            // 21916984 / 931 / 932 etc. = invalid or expired token: drop the cached access token
            Cache::forget($this->tokenCacheKey());
            throw new EbayException("eBay {$callName} failed: ".implode('; ', $errors));
        }

        return $xml;
    }

    public function getSellerListPage(int $page, string $endFrom, string $endTo, int $perPage = 100): SimpleXMLElement
    {
        return $this->call('GetSellerList',
            '<DetailLevel>ReturnAll</DetailLevel>'
            .'<IncludeWatchCount>true</IncludeWatchCount>'
            .'<EndTimeFrom>'.$endFrom.'</EndTimeFrom>'
            .'<EndTimeTo>'.$endTo.'</EndTimeTo>'
            .'<Pagination><EntriesPerPage>'.$perPage.'</EntriesPerPage><PageNumber>'.$page.'</PageNumber></Pagination>'
        );
    }

    public function getSellerEvents(string $modFrom, string $modTo): SimpleXMLElement
    {
        return $this->call('GetSellerEvents',
            '<DetailLevel>ReturnAll</DetailLevel>'
            .'<IncludeWatchCount>true</IncludeWatchCount>'
            .'<ModTimeFrom>'.$modFrom.'</ModTimeFrom>'
            .'<ModTimeTo>'.$modTo.'</ModTimeTo>'
        );
    }

    public function getItem(string $itemId): SimpleXMLElement
    {
        $id = preg_replace('/\D/', '', $itemId);

        return $this->call('GetItem',
            '<ItemID>'.$id.'</ItemID><DetailLevel>ReturnAll</DetailLevel><IncludeWatchCount>true</IncludeWatchCount>'
        )->Item;
    }
}
