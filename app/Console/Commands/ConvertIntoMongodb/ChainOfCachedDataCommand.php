<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Models\MongoDBModels\BookIrCreator;
use App\Models\MongoDBModels\BookIrPublisher;
use App\Models\MongoDBModels\NewBookPublishDate;
use Illuminate\Console\Command;

class ChainOfCachedDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chart:all_cache_data {date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'this is a chain of all commands belong to cache data';

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
        //TODO : NEW
        $date = $this->argument('date');
        $new = NewBookPublishDate::where('created_at', $date)->first();
        $years = $new->years;
        $creators = $new->creators;
        $publishers = $new->publishers;

        if ($years != null) {
            foreach ($years as $year) {
                $this->call("chart:book_total_paragraph_yearly" , ['year' => $year]);
                $this->call("chart:book_price_average_yearly" , ['year' => $year]);
                $this->call("chart:book_total_circulation_yearly", ['year' => $year]);
                $this->call("chart:book_total_count_yearly", ['year' => $year]);
                $this->call("chart:book_total_pages_yearly", ['year' => $year]);
                $this->call("chart:book_total_price_yearly", ['year' => $year]);
                $this->call("chart:top_circulation_creators_yearly", ['year' => $year]);
                $this->call("chart:top_circulation_publishers_yearly", ['year' => $year]);
                $this->call("chart:top_price_publishers_yearly", ['year' => $year]);
                $this->call("chart:top_price_creators_yearly", ['year' => $year]);
                if ($creators != null) {
                    foreach ($creators as $creator) {
                        $creatorId = BookIrCreator::where('xsqlid', $creator)->first()->_id;
                        $this->call("chart:creators", ['year' => $year , 'id' => $creatorId]);
                        $this->call("chart:creators_average", ['year' => $year , 'id' => $creatorId]);
                        $this->call("chart:creators_firstprintnumber", ['year' => $year , 'id' => $creatorId]);
                        $this->call("chart:creators_firstprintnumber_average", ['year' => $year , 'id' => $creatorId]);
                        $this->call("chart:creators_paragraph", ['year' => $year , 'id' => $creatorId]);
                        $this->call("chart:creator_alltime_average", ['--S' => true , 'id' => $creatorId]);
                        $this->call("chart:creator_alltime", ['--S' => true , 'id' => $creatorId]);
                        $this->call("chart:creator_alltime_paragraph", ['--S' => true , 'id' => $creatorId]);
                    }
                }
                if ($publishers != null) {
                    foreach ($publishers as $publisher) {
                        $publisherId = BookIrPublisher::where('xsqlid', $publisher)->first()->_id;
                        $this->call("chart:publishers", ['year' => $year , 'id' => $publisherId]);
                        $this->call("chart:publishers_average", ['year' => $year , 'id' => $publisherId]);
                        $this->call("chart:publishers_firstprintnumber", ['year' => $year , 'id' => $publisherId]);
                        $this->call("chart:publishers_firstprintnumber_average", ['year' => $year , 'id' => $publisherId]);
                        $this->call("chart:publishers_paragraph", ['year' => $year , 'id' => $publisherId]);
                        $this->call("chart:publisher_alltime_paragraph", ['--S' => true , 'id' => $publisherId]);
                        $this->call("chart:publisher_alltime", ['--S' => true , 'id' => $publisherId]);
                        $this->call("chart:publisher_alltime_average", ['--S' => true , 'id' => $publisherId]);
                    }
                }
            }
            $new->update(
                ['checked' => true]
            );
        }
        return true;
    }
}
