<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\SyncLog;
use Carbon\Carbon;
use Illuminate\Support\Str;
use SimpleXMLElement;
use Throwable;

/**
 * Pulls listings from the eBay store into the products table.
 *
 *  - Full sync:        GetSellerList over every listing ending in the next 119 days
 *                      (covers all active listings, incl. Good 'Til Cancelled).
 *                      Resumable: a run can be split across several HTTP/cron calls.
 *                      At the end, active items eBay no longer returns are marked "ended".
 *  - Incremental sync: GetSellerEvents — only items created/changed/sold since last run.
 *  - Item sync:        GetItem for one item ID.
 */
class EbaySync
{
    private array $counts = ['created' => 0, 'updated' => 0, 'ended' => 0];

    public function __construct(private EbayClient $ebay) {}

    /** What the cron endpoint calls. Decides full vs incremental automatically. */
    public function auto(int $budgetSeconds = 45): SyncLog
    {
        $state = $this->fullState();
        $lastFull = Setting::get('ebay_last_full_sync_at');

        if ($state || ! $lastFull || Carbon::parse($lastFull)->lt(now()->subHours(24))) {
            return $this->full($budgetSeconds);
        }

        return $this->incremental();
    }

    // ------------------------------------------------------------------ full

    public function full(int $budgetSeconds = 0, bool $restart = false): SyncLog
    {
        $start = microtime(true);
        $state = $restart ? null : $this->fullState();

        if (! $state) {
            $log = SyncLog::create(['type' => 'full', 'status' => 'running', 'started_at' => now()]);
            $state = [
                'log_id' => $log->id,
                'page' => 1,
                'total_pages' => null,
                'run_started_at' => now()->toIso8601String(),
                'end_from' => now()->subDay()->utc()->format('Y-m-d\TH:i:s.v\Z'),
                'end_to' => now()->addDays(119)->utc()->format('Y-m-d\TH:i:s.v\Z'),
            ];
            $this->saveFullState($state);
        }

        $log = SyncLog::find($state['log_id']) ?? SyncLog::create(['type' => 'full', 'status' => 'running', 'started_at' => now()]);
        $this->counts = ['created' => $log->created_count, 'updated' => $log->updated_count, 'ended' => 0];

        try {
            do {
                $res = $this->ebay->getSellerListPage($state['page'], $state['end_from'], $state['end_to']);
                $state['total_pages'] = max(1, (int) $res->PaginationResult->TotalNumberOfPages);

                foreach ($res->ItemArray->Item ?? [] as $item) {
                    $this->upsert($item);
                }

                $state['page']++;
                $this->saveFullState($state);
                $log->update(['created_count' => $this->counts['created'], 'updated_count' => $this->counts['updated'],
                    'message' => 'Page '.($state['page'] - 1).' of '.$state['total_pages']]);

                $outOfTime = $budgetSeconds > 0 && (microtime(true) - $start) > $budgetSeconds;
            } while ($state['page'] <= $state['total_pages'] && ! $outOfTime);

            if ($state['page'] > $state['total_pages']) {
                // Finished: anything active from eBay that we did not see in this run has ended / been removed.
                $this->counts['ended'] = Product::where('source', 'ebay')
                    ->where('status', 'active')
                    ->where(fn ($q) => $q->whereNull('ebay_seen_at')->orWhere('ebay_seen_at', '<', Carbon::parse($state['run_started_at'])))
                    ->update(['status' => 'ended']);

                $this->saveFullState(null);
                Setting::set('ebay_last_full_sync_at', now()->toIso8601String());
                Setting::set('ebay_last_incremental_at', $state['run_started_at']);
                $log->update([
                    'status' => 'success',
                    'ended_count' => $this->counts['ended'],
                    'finished_at' => now(),
                    'message' => 'Synced '.$state['total_pages'].' page(s).',
                ]);
            } else {
                $log->update(['message' => 'In progress — page '.$state['page'].' of '.$state['total_pages'].' next (continues on next run).']);
            }
        } catch (Throwable $e) {
            $log->update(['status' => 'failed', 'finished_at' => now(), 'message' => Str::limit($e->getMessage(), 1000)]);
            $this->saveFullState(null);
            throw $e;
        }

        return $log->fresh();
    }

    // ----------------------------------------------------------- incremental

    public function incremental(): SyncLog
    {
        $log = SyncLog::create(['type' => 'incremental', 'status' => 'running', 'started_at' => now()]);
        $runAt = now();

        try {
            $last = Setting::get('ebay_last_incremental_at');
            $from = $last ? Carbon::parse($last)->subMinutes(5) : now()->subHours(47);
            if ($from->lt(now()->subHours(47))) {
                $from = now()->subHours(47); // GetSellerEvents allows max 48h window
            }

            $res = $this->ebay->getSellerEvents(
                $from->utc()->format('Y-m-d\TH:i:s.v\Z'),
                $runAt->copy()->utc()->format('Y-m-d\TH:i:s.v\Z')
            );

            $fetchedDescriptions = 0;
            foreach ($res->ItemArray->Item ?? [] as $item) {
                $product = $this->upsert($item);
                // GetSellerEvents may omit the description — fetch it for new items (capped per run)
                if ($product && blank($product->description) && $fetchedDescriptions < 15) {
                    $fetchedDescriptions++;
                    $this->upsert($this->ebay->getItem($product->ebay_item_id));
                }
            }

            Setting::set('ebay_last_incremental_at', $runAt->toIso8601String());
            $log->update([
                'status' => 'success', 'finished_at' => now(),
                'created_count' => $this->counts['created'], 'updated_count' => $this->counts['updated'],
                'message' => 'Checked changes since '.$from->format('d M H:i'),
            ]);
        } catch (Throwable $e) {
            $log->update(['status' => 'failed', 'finished_at' => now(), 'message' => Str::limit($e->getMessage(), 1000)]);
            throw $e;
        }

        return $log;
    }

    public function item(string $itemId): Product
    {
        $log = SyncLog::create(['type' => 'item', 'status' => 'running', 'started_at' => now()]);
        try {
            $product = $this->upsert($this->ebay->getItem($itemId));
            $log->update(['status' => 'success', 'finished_at' => now(),
                'created_count' => $this->counts['created'], 'updated_count' => $this->counts['updated'],
                'message' => 'Imported item '.$itemId]);

            return $product;
        } catch (Throwable $e) {
            $log->update(['status' => 'failed', 'finished_at' => now(), 'message' => Str::limit($e->getMessage(), 1000)]);
            throw $e;
        }
    }

    public function fullState(): ?array
    {
        $raw = Setting::get('ebay_full_sync_state');
        $state = $raw ? json_decode($raw, true) : null;

        // A stuck run older than 6 hours is abandoned
        if ($state && Carbon::parse($state['run_started_at'])->lt(now()->subHours(6))) {
            SyncLog::where('id', $state['log_id'])->update(['status' => 'failed', 'finished_at' => now(), 'message' => 'Abandoned (timed out).']);
            $this->saveFullState(null);

            return null;
        }

        return $state;
    }

    private function saveFullState(?array $state): void
    {
        Setting::set('ebay_full_sync_state', $state ? json_encode($state) : null);
    }

    // --------------------------------------------------------------- mapping

    public function upsert(SimpleXMLElement $item): ?Product
    {
        $itemId = (string) $item->ItemID;
        if ($itemId === '') {
            return null;
        }

        $data = $this->map($item);
        $product = Product::where('ebay_item_id', $itemId)->first();

        if (! $product) {
            if ($data['status'] !== 'active') {
                return null; // don't import listings that are already finished
            }
            $product = new Product(['ebay_item_id' => $itemId, 'source' => 'ebay']);
            $product->fill($data);
            $product->slug = Product::uniqueSlug($data['title']);
            $product->save();
            $this->counts['created']++;

            return $product;
        }

        if ($product->lock_content) {
            $data = array_diff_key($data, array_flip(['title', 'subtitle', 'description', 'category_id', 'images']));
        }

        // Partial payloads (e.g. GetSellerEvents) may omit some fields — don't wipe existing data.
        foreach (['description', 'images', 'subtitle', 'condition'] as $field) {
            if (array_key_exists($field, $data) && blank($data[$field])) {
                unset($data[$field]);
            }
        }

        $product->fill($data)->save();
        $this->counts['updated']++;

        return $product;
    }

    public function map(SimpleXMLElement $item): array
    {
        $status = (string) $item->SellingStatus->ListingStatus;
        $qty = (int) $item->Quantity;
        $sold = (int) $item->SellingStatus->QuantitySold;
        $available = max(0, $qty - $sold);

        $productStatus = match (true) {
            $status === 'Active' && $available > 0, $status === '' && $available > 0 => 'active',
            $available === 0 && $sold > 0 => 'sold',
            default => 'ended',
        };

        $price = (string) ($item->SellingStatus->CurrentPrice ?: $item->StartPrice);
        $currency = (string) ($item->SellingStatus->CurrentPrice['currencyID'] ?? $item->Currency ?? 'GBP') ?: 'GBP';
        $original = (string) ($item->DiscountPriceInfo->OriginalRetailPrice ?? '');

        $images = [];
        foreach ($item->PictureDetails->PictureURL ?? [] as $url) {
            $images[] = $this->bigImage((string) $url);
        }
        if (! $images && (string) $item->PictureDetails->GalleryURL) {
            $images[] = $this->bigImage((string) $item->PictureDetails->GalleryURL);
        }

        $categoryName = (string) $item->PrimaryCategory->CategoryName;

        $data = [
            'title' => Str::limit(html_entity_decode((string) $item->Title), 250, ''),
            'subtitle' => (string) $item->SubTitle ?: null,
            'description' => HtmlSanitizer::clean((string) $item->Description),
            'price' => (float) $price,
            'original_price' => $original !== '' && (float) $original > (float) $price ? (float) $original : null,
            'currency' => $currency,
            'quantity' => $available,
            'quantity_sold' => $sold,
            'condition' => (string) $item->ConditionDisplayName ?: null,
            'ebay_category_id' => (string) $item->PrimaryCategory->CategoryID ?: null,
            'ebay_category_name' => $categoryName ?: null,
            'images' => $images,
            'ebay_url' => (string) $item->ListingDetails->ViewItemURL ?: 'https://www.ebay.co.uk/itm/'.$item->ItemID,
            'status' => $productStatus,
            'watch_count' => (int) $item->WatchCount,
            'listed_at' => (string) $item->ListingDetails->StartTime ? Carbon::parse((string) $item->ListingDetails->StartTime) : null,
            'ends_at' => (string) $item->ListingDetails->EndTime ? Carbon::parse((string) $item->ListingDetails->EndTime) : null,
            'ebay_synced_at' => now(),
            'ebay_seen_at' => now(),
        ];

        if ($categoryName) {
            $data['category_id'] = $this->categoryFor($categoryName)?->id;
        }

        return $data;
    }

    /** Map eBay's "Home, Furniture & DIY:Kitchen:..." to a site category (top level). */
    private function categoryFor(string $ebayName): ?Category
    {
        $top = trim(explode(':', $ebayName)[0]);
        if ($top === '') {
            return null;
        }

        return Category::firstOrCreate(['ebay_key' => $top], [
            'name' => $top,
            'icon' => $this->iconFor($top),
            'sort_order' => 100,
        ]);
    }

    private function iconFor(string $name): string
    {
        $n = strtolower($name);
        $map = [
            'toy' => '🧸', 'game' => '🎮', 'home' => '🏠', 'garden' => '🌿', 'kitchen' => '🍳', 'health' => '💊',
            'beauty' => '💄', 'cloth' => '👕', 'fashion' => '👗', 'shoe' => '👟', 'jewel' => '💍', 'watch' => '⌚',
            'sport' => '⚽', 'phone' => '📱', 'mobile' => '📱', 'computer' => '💻', 'electronic' => '🔌', 'sound' => '🎧',
            'camera' => '📷', 'baby' => '🍼', 'pet' => '🐾', 'book' => '📚', 'music' => '🎵', 'film' => '🎬', 'car' => '🚗',
            'vehicle' => '🚗', 'craft' => '✂️', 'art' => '🎨', 'office' => '🖇️', 'business' => '💼', 'collect' => '🏆',
            'party' => '🎉', 'tool' => '🛠️', 'diy' => '🛠️', 'food' => '🍫', 'video' => '🎮',
        ];
        foreach ($map as $needle => $icon) {
            if (str_contains($n, $needle)) {
                return $icon;
            }
        }

        return '🛍️';
    }

    /** eBay image URLs come in many sizes; ask for the large one. */
    private function bigImage(string $url): string
    {
        $url = str_replace('http://', 'https://', $url);
        $url = preg_replace('#/s-l\d+\.(jpg|jpeg|png|webp)#i', '/s-l1600.$1', $url);

        return preg_replace('#\$_\d+\.(JPG|JPEG|PNG)#i', '\$_57.$1', $url);
    }
}
