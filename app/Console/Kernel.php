<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use DB;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        'App\Console\Commands\InsertData',
        'App\Console\Commands\CheckOrders',
        'App\Console\Commands\CheckTrades',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
      //  $schedule->command('insert:data')
      //  ->sendOutputTo(storage_path('logs/data_cron.log'))
      //  ->cron('*/2 * * * *');
        $schedule->command('check:orders')
        ->sendOutputTo(storage_path('logs/order_cron.log'))
        ->cron('* * * * *');
        $schedule->command('check:trades')
        ->sendOutputTo(storage_path('logs/trade_cron.log'))
        ->cron('* * * * *');
    }
}
