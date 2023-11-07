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
        // Commands\UpdateIsTranslateDataInBookirBook::class, // is_translate:update
        Commands\GetDigi::class, 
        Commands\GetDigi1::class, 
        Commands\GetDigi2::class, 
        Commands\GetDigi3::class, 
        Commands\GetDigi4::class, 
        Commands\GetDigi5::class, 
        Commands\GetDigi6::class, 
        Commands\GetDigi7::class, 
        Commands\GetDigi8::class, 
        Commands\GetDigi9::class, 
        Commands\GetDigi10::class, 
        Commands\GetDigi11::class, 
        Commands\GetDigiNewestBook::class, 
        Commands\GetIranketab::class, 
        Commands\GetKetabRah::class, 
        Commands\get30Book::class, 
        Commands\GetMajmaForCorrectInfo::class, 

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
        // digi category
        $schedule->command('get:digiCategoryForeignPrintedBook 1')->dailyAt('12:40');
        $schedule->command('get:digiCategoryChildrenBook 1')->dailyAt('12:40');
        $schedule->command('get:digiCategoryPrintedBookOfBiographyAndEncyclopedia 1')->dailyAt('12:40');
        $schedule->command('get:digiCategoryAppliedSciencesTechnologyAndEngineering 1')->dailyAt('12:40');
        $schedule->command('get:digiCategoryPrintedHistoryAndGeographyBook 1')->dailyAt('12:40');
        $schedule->command('get:digiCategoryPrintedBookOfPhilosophyAndPsychology 1')->dailyAt('12:40');
        $schedule->command('get:digiCategoryTextbookTutorialsAndTests 1')->dailyAt('12:40');
        $schedule->command('get:digiCategoryLanguageBooks 1')->dailyAt('12:40');
        $schedule->command('get:digiCategoryPrintedBookOfArtAndEntertainment 1')->dailyAt('12:40');
        $schedule->command('get:digiCategoryReligiousPrintedBook 1')->dailyAt('12:40');
        $schedule->command('get:digiCategoryPrintedBookOfSocialSciences 1')->dailyAt('12:40');
        $schedule->command('get:digiCategoryPrintedBookOfPoetryAndLiterature 1')->dailyAt('12:40');
        //digi new books
        $schedule->command('get:digiNewestBook 1')->dailyAt('12:40');
        // fidibo 
        $schedule->command('get:fidibo 1')->dailyAt('12:40');
        //ketabrah
        $schedule->command('get:ketabRah 1')->dailyAt('12:40');
        //30book 
        // $schedule->command('get:30book 1')->dailyAt('02:00');

        $schedule->command('get:iranKetab 1')->everyMinute();
        $schedule->command('get:GetMajmaForCorrectInfo 1')->everyMinute()->timezone('Asia/Tehran')->between('02:00', '6:00');
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
