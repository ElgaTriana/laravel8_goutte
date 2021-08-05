<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\ScrapPortal::class,
        Commands\ScrapLiputannam::class,
        Commands\ScrapInews::class,
        Commands\ScrapOkezone::class,
        Commands\ScrapSindo::class,
        Commands\ScrapIdntimes::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        // $schedule->command('scrap:portal')
        // ->timezone('Asia/Jakarta')  
        // ->everyFifteenMinutes();

        // $schedule->command('scrap:liputannam')
        // ->timezone('Asia/Jakarta')  
        // ->everyFifteenMinutes();

        // $schedule->command('scrap:inews')
        // ->timezone('Asia/Jakarta')  
        // ->everyFifteenMinutes();

        // $schedule->command('scrap:sindo')
        // ->timezone('Asia/Jakarta')  
        // ->everyFifteenMinutes();

        // $schedule->command('scrap:okezone')
        // ->timezone('Asia/Jakarta')  
        // ->everyFifteenMinutes();

        // $schedule->command('scrap:idn')
        // ->timezone('Asia/Jakarta')  
        // ->everyFifteenMinutes();
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
