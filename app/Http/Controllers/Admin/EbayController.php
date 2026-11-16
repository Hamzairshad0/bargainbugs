<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Setting;
use App\Models\SyncLog;
use App\Services\EbayClient;
use App\Services\EbaySync;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;

class EbayController extends Controller
{
    public function index(EbayClient $ebay, EbaySync $sync)
    {
        return view('admin.ebay', [
            'configured' => $ebay->isConfigured(),
            'connected' => $ebay->isConnected(),
            'ebayUser' => Setting::get('ebay_user_id'),
            'tokenExpires' => Setting::get('ebay_refresh_token_expires_at'),
            'lastFull' => Setting::get('ebay_last_full_sync_at'),
            'lastIncremental' => Setting::get('ebay_last_incremental_at'),
            'inProgress' => $sync->fullState(),
            'logs' => SyncLog::latest('id')->take(25)->get(),
            'ebayCount' => Product::where('source', 'ebay')->count(),
            'callbackUrl' => route('ebay.callback'),
            'cronUrl' => route('cron.ebay').'?key='.(config('services.cron_secret') ?: 'SET_CRON_SECRET'),
            'env' => config('services.ebay.env'),
        ]);
    }

    public function connect(EbayClient $ebay)
    {
        if (! $ebay->isConfigured()) {
            return back()->with('error', 'Add EBAY_CLIENT_ID, EBAY_CLIENT_SECRET and EBAY_RUNAME to your environment first.');
        }

        $state = Str::random(40);
        session(['ebay_oauth_state' => $state]);

        return redirect()->away($ebay->authorizeUrl($state));
    }

    public function callback(Request $request, EbayClient $ebay)
    {
        if ($request->filled('error') || ! $request->filled('code')) {
            return redirect()->route('admin.ebay')->with('error', 'eBay connection was cancelled or declined.');
        }

        if ($request->filled('state') && $request->state !== session('ebay_oauth_state')) {
            return redirect()->route('admin.ebay')->with('error', 'Security check failed — please try connecting again.');
        }

        try {
            $ebay->exchangeCode($request->code);
        } catch (Throwable $e) {
            return redirect()->route('admin.ebay')->with('error', $e->getMessage());
        }

        return redirect()->route('admin.ebay')->with('success', 'eBay account connected! Now click “Sync now” to import your listings.');
    }

    public function disconnect(EbayClient $ebay)
    {
        $ebay->disconnect();

        return back()->with('success', 'eBay disconnected. Existing products are kept.');
    }

    public function sync(Request $request, EbaySync $sync)
    {
        @set_time_limit(300);

        try {
            $log = $request->input('type') === 'incremental' ? $sync->incremental() : $sync->full(50, $request->boolean('restart'));
            Cache::forget('nav.categories');

            $msg = "Sync {$log->status}: {$log->created_count} new, {$log->updated_count} updated, {$log->ended_count} ended.";
            if ($log->status === 'running') {
                $msg = 'Big store! '.$log->message.' Click “Sync now” again to continue (or let the cron finish it).';
            }

            return back()->with('success', $msg);
        } catch (Throwable $e) {
            return back()->with('error', 'Sync failed: '.$e->getMessage());
        }
    }

    public function import(Request $request, EbaySync $sync)
    {
        $data = $request->validate(['item' => 'required|string|max:200']);
        // Accept either an item number or a full eBay URL
        preg_match('/(\d{9,15})/', $data['item'], $m);
        if (empty($m[1])) {
            return back()->with('error', 'Could not find an eBay item number in that text.');
        }

        try {
            $p = $sync->item($m[1]);
            Cache::forget('nav.categories');

            return back()->with('success', 'Imported “'.$p->title.'”.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
