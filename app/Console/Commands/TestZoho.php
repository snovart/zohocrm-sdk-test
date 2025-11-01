<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use ZohoCrmSDK\Api\ZohoCrmApi;

class TestZoho extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-zoho';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $records = ZohoCrmApi::getInstance()
                    ->setModule('Accounts')
                    ->records()
                    ->getRecords()
                    ->request();
        dd($records);
    }
}
