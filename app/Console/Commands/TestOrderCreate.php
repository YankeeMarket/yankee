<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Storage;
use Log;
use App\Http\Controllers\DPDController;
use App\Http\Controllers\WebhookController;


class TestOrderCreate extends Command
{
    private $service;

/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:testOrderCreate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'use order 100 to test webhook code';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $whc = new WebhookController();
        $details = $whc->retrieve(100);
        $dpd = new DPDController();
        $dpd->initiate_order($details);
    }


}
