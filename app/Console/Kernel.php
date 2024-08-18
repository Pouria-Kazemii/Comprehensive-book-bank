<?php

namespace App\Console;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Morilog\Jalali\Jalalian;

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
        Commands\CrawlerSites\GetDigiBook::class,
        Commands\CorrectInfo\CheckIsBookDigi::class,

        Commands\CrawlerSites\GetFidiboNewestBook::class,
        Commands\CrawlerSites\GetFidibo::class,
        Commands\CrawlerSites\GetIranketab::class,
        Commands\CrawlerSites\GetKetabRah::class,
        Commands\CrawlerSites\GetShahreKetabOnline::class,
        Commands\CrawlerSites\GetBarKhatBookNewestBook::class,
        commands\CrawlerSites\GetBarKhatBook::class,
        commands\CrawlerSites\GetketabejamNewestBook::class,
        commands\CrawlerSites\Getketabejam::class,
        Commands\CrawlerSites\GetGissom::class,
        Commands\CrawlerSites\get30Book::class,
        Commands\GetKetabirForNewBooks::class,
        Commands\GetKetabirLastDays::class,
        Commands\GetKetabirFutureDays::class,

        commands\CorrectInfo\RecheckNotfoundBooks::class,

        commands\ConvertIntoMongodb\ConvertBookIr_bookCommand1::class,
        commands\ConvertIntoMongodb\ConvertCreatorsCommand::class,
        commands\ConvertIntoMongodb\ConvertPublishersCommand::class,

        Commands\ConvertIntoMongodb\CalculateInsertedBooks::class,
        Commands\ConvertIntoMongodb\MatchingMongoBookWithSQLCommand::class,
        Commands\ConvertIntoMongodb\MatchingMongoCreatorsWithSQLCommand::class,
        Commands\ConvertIntoMongodb\MatchingMongoPublisherWithSQLCommand::class,
        Commands\ConvertIntoMongodb\MatchingMongoSubjectsWithSQLCommand::class,

        Commands\ConvertIntoMongodb\CachedCharts\MakingTopPricePublishersEveryYearCommand::class,
        Commands\ConvertIntoMongodb\CachedCharts\MakingTopPriceCreatorsEveryYearCommand::class,
        Commands\ConvertIntoMongodb\CachedCharts\MakingTopCirculationCreatorsEveryYearCommand::class,
        Commands\ConvertIntoMongodb\CachedCharts\MakingTopCirculationPublishersEveryYearCommand::class,
        Commands\ConvertIntoMongodb\CachedCharts\MakingBookTotalPriceEveryYearCommand::class,
        Commands\ConvertIntoMongodb\CachedCharts\MakingBookTotalCountEveryYearCommand::class,
        Commands\ConvertIntoMongodb\CachedCharts\MakingBookPriceAverageEveryYearCommand::class,
        Commands\ConvertIntoMongodb\CachedCharts\MakingBookTotalCirculationEveryYearCommand::class,


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

        $schedule->command('get:KetabirFutureDays 1')->dailyAt('02:00');
        $schedule->command('get:KetabirLastDays 1')->dailyAt('02:30');
        $schedule->command('get:KetabirForNewBookInfo 1')->dailyAt('03:00');

        $schedule->command('get:RecheckNotfoundBooks 1')->dailyAt('05:00');
        // $schedule->command('get:RecheckNotfoundBooks 1')->everyMinute()->timezone('Asia/Tehran')->between('02:00', '6:00');
        $schedule->command('match:mongodb_subjects')
            ->dailyAt('07:00')
            ->then(function () use ($schedule) {
                logCommandResult('match:mongodb_subjects', true);

                $schedule->command('match_mongodb:creators')
                    ->then(function () use ($schedule) {
                        logCommandResult('match_mongodb:creators', true);

                        $schedule->command('match:mongodb_publishers')
                            ->then(function () use ($schedule) {
                                logCommandResult('match:mongodb_publishers', true);

                                $schedule->command('match:mongodb_books')
                                    ->then(function () use($schedule) {
                                        logCommandResult('match:mongodb_books', true);

                                        $schedule->command("insert:daily_books_count ".getDateNow())
                                            ->then(function () {
                                                logCommandResult('insert:daily_books_count'.getDateNow(), true);
                                            })
                                            ->onFailure(function () {
                                                logCommandResult('insert:daily_books_count'.getDateNow(), false);
                                            });
                                    })
                                    ->onFailure(function () {
                                        logCommandResult('match:mongodb_books', false);
                                    });
                            })
                            ->onFailure(function () {
                                logCommandResult('match:mongodb_publishers', false);
                            });
                    })
                    ->onFailure(function () {
                        logCommandResult('match_mongodb:creators', false);
                    });
            })
            ->onFailure(function () {
                logCommandResult('match:mongodb_subjects', false);
            });

        /////////////////////////////////////////////////////////////////////////////////////////////////
        // fidibo
        $schedule->command('get:fidiboNewestBooks 1')->dailyAt('15:45');
        $schedule->command('get:fidibo 1')->dailyAt('16:00');
        //ketabrah
        $schedule->command('get:ketabRah 1')->dailyAt('16:15');
        //shahreketabonline
        $schedule->command('get:shahreketabonline 1')->dailyAt('16:30');
        // barkhat book
        $schedule->command('get:barkhatbookNewestBook 1 2')->dailyAt('17:00');
        $schedule->command('get:barkhatbookNewestBook 1 1')->monthlyOn(20, '17:15'); // for check amin categories
        $schedule->command('get:barkhatbook 1')->dailyAt('17:30');

        // ketabejam
        $schedule->command('get:ketabejamNewestBooks 1 2')->dailyAt('18:00');
        $schedule->command('get:ketabejamNewestBooks 1 1')->monthlyOn(20, '18:15'); // for check amin categories
        $schedule->command('get:ketabejam')->dailyAt('18:30');

        //gissom
        $schedule->command('get:gissom 1')->dailyAt('17:00');
        //30book
        // $schedule->command('get:30book 1')->dailyAt('02:00');

        // $schedule->command('get:iranKetab 1')->everyMinute();   // stop from kandoo news

        //////////////////////////////// digi category//////////////////////////////////////////////
        $schedule->command('get:digiCategoryForeignPrintedBook 1')->dailyAt('21:00');
        $schedule->command('get:digiCategoryChildrenBook 1')->dailyAt('21:15');
        $schedule->command('get:digiCategoryPrintedBookOfBiographyAndEncyclopedia 1')->dailyAt('21:30');
        $schedule->command('get:digiCategoryAppliedSciencesTechnologyAndEngineering 1')->dailyAt('21:45');
        $schedule->command('get:digiCategoryPrintedHistoryAndGeographyBook 1')->dailyAt('22:00');
        $schedule->command('get:digiCategoryPrintedBookOfPhilosophyAndPsychology 1')->dailyAt('22:15');
        $schedule->command('get:digiCategoryTextbookTutorialsAndTests 1')->dailyAt('22:30');
        $schedule->command('get:digiCategoryLanguageBooks 1')->dailyAt('22:45');
        $schedule->command('get:digiCategoryPrintedBookOfArtAndEntertainment 1')->dailyAt('23:00');
        $schedule->command('get:digiCategoryReligiousPrintedBook 1')->dailyAt('23:15');
        $schedule->command('get:digiCategoryPrintedBookOfSocialSciences 1')->dailyAt('23:30');
        $schedule->command('get:digiCategoryPrintedBookOfPoetryAndLiterature 1')->dailyAt('23:45');
        //digi new books
        $schedule->command('get:digiNewestBook 1')->dailyAt('00:00');
        $schedule->command('get:digiBook 1')->dailyAt('01:00');
        $schedule->command('get:digiBook 1')->dailyAt('07:00');

        //$schedule->command('get:CheckIsBookDigi 1')->dailyAt('16:00');



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
