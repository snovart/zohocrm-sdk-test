<?php

namespace App\Console\Commands;

use App\Services\Zoho\ZohoSyncService;
use Illuminate\Console\Command;
use Throwable;

class ZohoPullCommand extends Command
{
    // Example: php artisan zoho:sync:pull Contacts --since="2025-01-01T00:00:00Z"
    protected $signature = 'zoho:sync:pull
                            {module : Zoho module name, e.g. Contacts, Deals, Leads}
                            {--since= : ISO8601 timestamp for incremental pull}';

    protected $description = 'Pull records from Zoho CRM into the local database';

    public function handle(ZohoSyncService $service): int
    {
        $module = (string) $this->argument('module');
        $since  = $this->option('since') ? (string) $this->option('since') : null;

        try {
            $count = $service->pull($module, $since);
            $this->info("Pulled {$count} records from {$module}");
            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Sync failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
