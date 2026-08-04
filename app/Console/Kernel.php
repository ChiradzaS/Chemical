<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Spatie\Backup\Commands\BackupCommand;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [

        'App\Console\Commands\DatabaseBackUp',
        
        \App\Console\Commands\MyCustomCommand::class,
       
         \App\Console\Commands\MqttSubscribe::class,
        
    ];
  
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(BackupCommand::class, ['--only-db'])->dailyAt('04:57');
    }
  
    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
  
        require base_path('routes/console.php');
    }


    

   

  

}