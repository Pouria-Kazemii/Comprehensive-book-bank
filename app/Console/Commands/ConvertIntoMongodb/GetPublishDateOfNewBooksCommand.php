<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Models\BookirBook;
use App\Models\MongoDBModels\NewBookPublishDate;
use Illuminate\Console\Command;
use Morilog\Jalali\Jalalian;

class GetPublishDateOfNewBooksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:book_publishdate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'get publishdate of new book';

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
        $uniqueYears = [];
        $years = BookirBook::select('xpublishdate')->whereNull('mongo_id')->distinct()->get();
        foreach ($years as $year){
            $shamsi_date = convertToSolarHijriYear($year->xpublishdate);
            if (!in_array($shamsi_date , $uniqueYears)){
               $uniqueYears [] = $shamsi_date;
            }
        }

        NewBookPublishDate::updateOrCreate(
            [
                'created_at' => getDateNow()
            ],
            [
                'years' => $uniqueYears,
                'checked' => false,
        ]);
        return true;
    }
}
