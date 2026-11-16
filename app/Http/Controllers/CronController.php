<?php

namespace App\Http\Controllers;

use App\Services\EbayClient;
use App\Services\EbaySync;
use Illuminate\Http\Request;
use Throwable;

/**
 * Called by Vercel Cron (Authorization: Bearer CRON_SECRET) or any external
 * cron service (https://yoursite/cron/ebay-sync?key=CRON_SECRET).
 */
class CronController extends Controller
{
    public function ebaySync(Request $request, EbaySync $sync, EbayClient $ebay)
    {
        $secret = (string) config('services.cron_secret');
        $given = (string) ($request->bearerToken() ?: $request->query('key'));

        abort_unless($secret !== '' && hash_equals($secret, $given), 403);

        if (! $ebay->isConnected()) {
            return response()->json(['ok' => false, 'error' => 'eBay not connected'], 409);
        }

        @set_time_limit(120);

        try {
            $log = $sync->auto(45);

            return response()->json(['ok' => true, 'type' => $log->type, 'status' => $log->status,
                'created' => $log->created_count, 'updated' => $log->updated_count, 'ended' => $log->ended_count, 'message' => $log->message]);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
