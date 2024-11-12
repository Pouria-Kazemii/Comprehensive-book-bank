<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\NewBookPublishDate;
use Illuminate\Console\Command;

class ChainOfCachedDioSubjectsDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dio_chart:all_cached_data {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is a  chain of all commands belongs to dio subjects cached data';

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
     * @return Bool
     */
    public function handle()
    {
        $date = $this->argument('date');
        if ($date) {
            $new = NewBookPublishDate::where('created_at', $date)->first();
            $years = $new->years;

            $books = BookIrBook2::where('diocode_subject', [])->get();
            if ($books != null) {
                foreach ($books as $book) {
                    $this->call('add:dio_subject', ['id' => $book->_id]);
                    $this->call('take:educational_help_books', ['id' => $book->id]);
                }
            }

            if ($years != null) {
                foreach ($years as $year) {
                    $this->call('dio_chart:book_total_paragraph_yearly', ['year' => $year]);
                    $this->call('dio_chart:book_total_paragraph_yearly', ['--F' => true, 'year' => $year]);
                    $this->call('dio_chart:book_price_average_yearly', ['year' => $year]);
                    $this->call('dio_chart:book_price_average_yearly', ['--F' => true, 'year' => $year]);
                    $this->call('dio_chart:book_total_circulation_yearly', ['year' => $year]);
                    $this->call('dio_chart:book_total_circulation_yearly', ['--F' => true, 'year' => $year]);
                    $this->call('dio_chart:book_total_count_yearly', ['year' => $year]);
                    $this->call('dio_chart:book_total_count_yearly', ['--F' => true, 'year' => $year]);
                    $this->call('dio_chart:book_total_pages_yearly', ['year' => $year]);
                    $this->call('dio_chart:book_total_pages_yearly', ['--F' => true, 'year' => $year]);
                    $this->call('dio_chart:book_total_price_yearly', ['year' => $year]);
                    $this->call('dio_chart:book_total_price_yearly', ['--F' => true, 'year' => $year]);
                    $this->call('dio_chart:book_data_all_times');
                    $this->call('dio_chart:book_average_all_times');
                    $this->call('dio_chart:book_average_all_times', ['--F' => true]);
                    $this->call('dio_chart:top_circulation_creator_yearly', ['year' => $year]);
                    $this->call('dio_chart:top_circulation_publisher_yearly', ['year' => $year]);
                    $this->call('dio_chart:top_price_creator_yearly', ['year' => $year]);
                    $this->call('dio_chart:top_price_publisher_yearly', ['year' => $year]);

                }
            }
        } else {
            $this->call('add:dio_subject');
            $this->call('take:educational_help_books');
            $this->call('dio_chart:book_total_paragraph_yearly', ['year' => 1340 , '--A' => true]);
            $this->call('dio_chart:book_total_paragraph_yearly', ['--F' => true, 'year' => 1340 , '--A' => true]);
            $this->call('dio_chart:book_price_average_yearly', ['year' => 1340 , '--A' => true]);
            $this->call('dio_chart:book_price_average_yearly', ['--F' => true, 'year' => 1340 , '--A' => true]);
            $this->call('dio_chart:book_total_circulation_yearly', ['year' => 1340 , '--A' => true]);
            $this->call('dio_chart:book_total_circulation_yearly', ['--F' => true, 'year' => 1340 , '--A' => true]);
            $this->call('dio_chart:book_total_count_yearly', ['year' => 1340 , '--A' => true]);
            $this->call('dio_chart:book_total_count_yearly', ['--F' => true, 'year' => 1340 , '--A' => true]);
            $this->call('dio_chart:book_total_pages_yearly', ['year' => 1340 , '--A' => true]);
            $this->call('dio_chart:book_total_pages_yearly', ['--F' => true, 'year' => 1340 , '--A' => true]);
            $this->call('dio_chart:book_total_price_yearly', ['year' => 1340 , '--A' => true]);
            $this->call('dio_chart:book_total_price_yearly', ['--F' => true, 'year' => 1340 , '--A' => true]);
            $this->call('dio_chart:book_data_all_times');
            $this->call('dio_chart:book_average_all_times');
            $this->call('dio_chart:book_average_all_times', ['--F' => true]);
            $this->call('dio_chart:top_circulation_creator_yearly', ['year' => 1340 , '--A' => true]);
            $this->call('dio_chart:top_circulation_publisher_yearly', ['year' => 1340 , '--A' => true]);
            $this->call('dio_chart:top_price_creator_yearly', ['year' => 1340 , '--A' => true]);
            $this->call('dio_chart:top_price_publisher_yearly', ['year' => 1340 , '--A' => true]);
        }
        return  true;
    }
}
