<?php

namespace App\Console\Commands;

use App\Services\EbaySync;
use Illuminate\Console\Command;
use Throwable;

class EbaySyncCommand extends Command
{
    protected $signature = 'ebay:sync
        {--full : Re-sync every listing (default is automatic: full once a day, otherwise only changes)}
        {--incremental : Only fetch listings changed recently}
        {--item= : Import / refresh a single eBay item ID}';

    protected $description = 'Sync products from the eBay store into the website';

    public function handle(EbaySync $sync): int
    {
        try {
            if ($id = $this->option('item')) {
                $p = $sync->item($id);
                $this->info("Synced: {$p->title}");

                return self::SUCCESS;
            }

            $log = match (true) {
                $this->option('full') => $sync->full(0, true),
                $this->option('incremental') => $sync->incremental(),
                default => $sync->auto(0),
            };

            $this->info(sprintf('[%s] %s — created %d, updated %d, ended %d. %s',
                $log->type, $log->status, $log->created_count, $log->updated_count, $log->ended_count, $log->message));

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
