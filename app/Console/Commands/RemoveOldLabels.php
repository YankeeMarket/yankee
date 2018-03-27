<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Storage;
use Log;
use App\Http\Controllers\DPDController;


class RemoveOldLabels extends Command
{
    private $service;

/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:removeOldLabels';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'go through the database and remove old labels';

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
        $dpd = new DPDController();
        $dpd->find_old_labels();
    }


}
