<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Storage;
use Log;
																																																																																				

class CloseManifest extends Command
{
    private $service;

/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:closeManifest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Request DPD to close the manifest to trigger courier pickup';

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
        $dpd = new \App\Http\Controllers\DPDController();
        $dpd->cli_close();
    }


}
