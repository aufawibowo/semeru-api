<?php

#nyontek dari https://tutsforweb.com/how-to-set-up-task-scheduling-cron-job-in-laravel/

namespace App\Console\Commands;
use Illuminate\Console\Command;

class CheckDiskNotificationToTelegram extends Command
{
/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';
 
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
 
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
        //
    }
}