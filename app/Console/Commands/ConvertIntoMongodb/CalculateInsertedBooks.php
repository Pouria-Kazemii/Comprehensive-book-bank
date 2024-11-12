<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Models\BookirBook;
use App\Models\MongoDBModels\BookIrDaily;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;

class CalculateInsertedBooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insert:daily_books_count {date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'insert count of books added daily in mongodb';

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
     * @return int
     */
    public function handle()
    {
        $this->info('start to build bookir_daily table');
        $startDate = $this->argument('date');
        $books = BookirBook::where('xregdate', '>=', Jalalian::fromFormat('Y-m-d', $startDate)->getTimestamp());

        $booksGroupedByDate = $books
            ->select(DB::raw('FROM_UNIXTIME(xregdate, "%Y-%m-%d") as date'), DB::raw('count(*) as count'))
            ->groupBy(DB::raw('FROM_UNIXTIME(xregdate, "%Y-%m-%d")'));


        $totalRows = $booksGroupedByDate->count();
        $progressBar = $this->output->createProgressBar($totalRows);
        $progressBar->start();
        $startTime = microtime(true);
        $dates = $booksGroupedByDate->get();

        foreach ($dates as $date) {
            $year = Jalalian::fromCarbon(\Carbon\Carbon::parse($date->date))->getYear();
            $month = Jalalian::fromCarbon(\Carbon\Carbon::parse($date->date))->getMonth();
            $day = Jalalian::fromCarbon(\Carbon\Carbon::parse($date->date))->getday();
            BookIrDaily::updateOrCreate([
                'year' => $year,
                'month' => $month,
                'day' => $day,
                'date' =>"$year/$month/$day" ,
                'count' => $date->count
            ]);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->line('');
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;
    }
}
