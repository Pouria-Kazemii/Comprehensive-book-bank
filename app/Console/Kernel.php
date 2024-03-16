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
        Commands\CrawlerSites\GetDigiCategoryForeignPrintedBook::class, 
        Commands\CrawlerSites\GetDigiCategoryChildrenBook::class, 
        Commands\CrawlerSites\GetDigiCategoryPrintedBookOfBiographyAndEncyclopedia::class, 
        Commands\CrawlerSites\GetDigiCategoryAppliedSciencesTechnologyAndEngineering::class, 
        Commands\CrawlerSites\GetDigiCategoryPrintedHistoryAndGeographyBook::class, 
        Commands\CrawlerSites\GetDigiCategoryPrintedBookOfPhilosophyAndPsychology::class, 
        Commands\CrawlerSites\GetDigiCategoryTextbookTutorialsAndTests::class, 
        Commands\CrawlerSites\GetDigiCategoryLanguageBooks::class, 
        Commands\CrawlerSites\GetDigiCategoryPrintedBookOfArtAndEntertainment::class, 
        Commands\CrawlerSites\GetDigiCategoryReligiousPrintedBook::class, 
        Commands\CrawlerSites\GetDigiCategoryPrintedBookOfSocialSciences::class, 
        Commands\CrawlerSites\GetDigiCategoryPrintedBookOfPoetryAndLiterature::class, 
        Commands\CrawlerSites\GetDigiNewestBook::class, 
        Commands\CrawlerSites\GetDigiBooksInfo::class, 
        Commands\CorrectInfo\CheckIsBookDigi::class, 


        Commands\CrawlerSites\GetIranketab::class, 
        Commands\CrawlerSites\GetKetabRah::class, 
        Commands\CrawlerSites\GetShahreKetabOnline::class, 
        Commands\CrawlerSites\get30Book::class, 
        Commands\GetKetabirForNewBooks::class,
        Commands\GetKetabirLastDays::class,
        Commands\GetKetabirFutureDays::class,
        commands\CrawlerSites\GetBarKhatBook::class, 

        commands\CorrectInfo\RecheckNotfoundBooks::class, 


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
        //////////////////////////////// digi category//////////////////////////////////////////////
        $schedule->command('get:digiBooksInfo 1')->dailyAt('6:00');
        $schedule->command('get:CheckIsBookDigi 1')->dailyAt('7:00');
        $schedule->command('get:digiCategoryForeignPrintedBook 1')->dailyAt('09:00');
        $schedule->command('get:digiCategoryChildrenBook 1')->dailyAt('09:30');
        $schedule->command('get:digiCategoryPrintedBookOfBiographyAndEncyclopedia 1')->dailyAt('10:00');
        $schedule->command('get:digiCategoryAppliedSciencesTechnologyAndEngineering 1')->dailyAt('10:30');
        $schedule->command('get:digiCategoryPrintedHistoryAndGeographyBook 1')->dailyAt('11:00');
        $schedule->command('get:digiCategoryPrintedBookOfPhilosophyAndPsychology 1')->dailyAt('11:30');
        $schedule->command('get:digiCategoryTextbookTutorialsAndTests 1')->dailyAt('12:00');
        $schedule->command('get:digiCategoryLanguageBooks 1')->dailyAt('12:30');
        $schedule->command('get:digiCategoryPrintedBookOfArtAndEntertainment 1')->dailyAt('13:00');
        $schedule->command('get:digiCategoryReligiousPrintedBook 1')->dailyAt('13:30');
        $schedule->command('get:digiCategoryPrintedBookOfSocialSciences 1')->dailyAt('14:00');
        $schedule->command('get:digiCategoryPrintedBookOfPoetryAndLiterature 1')->dailyAt('14:30');
        //digi new books
        $schedule->command('get:digiNewestBook 1')->dailyAt('15:00');
        $schedule->command('get:digiBooksInfo 1')->dailyAt('15:45');
        $schedule->command('get:CheckIsBookDigi 1')->dailyAt('16:00');
        /////////////////////////////////////////////////////////////////////////////////////////////////
        // fidibo 
        $schedule->command('get:fidibo 1')->dailyAt('16:00');
        //ketabrah
        $schedule->command('get:ketabRah 1')->dailyAt('16:30');
        //shahreketabonline
        $schedule->command('get:shahreketabonline 1')->dailyAt('17:00');
        // barkhat book
        $schedule->command('get:get:barkhatbook 1 2')->dailyAt('17:30');
        //30book 
        // $schedule->command('get:30book 1')->dailyAt('02:00');

        // $schedule->command('get:iranKetab 1')->everyMinute();   // stop from kandoo news

        $schedule->command('get:KetabirFutureDays 1')->dailyAt('02:00');
        $schedule->command('get:KetabirLastDays 1')->dailyAt('02:30');
        $schedule->command('get:KetabirForNewBookInfo 1')->dailyAt('03:00');

        $schedule->command('get:RecheckNotfoundBooks 1')->dailyAt('05:00');
        // $schedule->command('get:RecheckNotfoundBooks 1')->everyMinute()->timezone('Asia/Tehran')->between('02:00', '6:00');


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
