<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Models\BiBookBiPublisher;
use App\Models\BookirBook;
use App\Models\BookirPartnerrule;
use App\Models\MongoDBModels\NewBookPublishDate;
use Illuminate\Console\Command;

class GetPublishersIdAndCreatorsIdOfNewBook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:book_publishers_and_creators';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'take publsihers and creators of new books';

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
        $uniqueCreators = [];
        $uniquePublishers = [];
        $books = BookirBook::select('xid')->whereNull('mongo_id')->get();
        foreach ($books as $book) {
            $creatorIds = BookirPartnerrule::where('xbookid', $book->xid)
                ->distinct()
                ->pluck('xcreatorid');
            $publisherIds = BiBookBiPublisher::where('bi_book_xid', $book->xid)
                ->distinct()
                ->pluck('bi_publisher_xid');
            foreach ($creatorIds as $creatorId) {
                if (!in_array($creatorId, $uniqueCreators)) {
                    $uniqueCreators [] = $creatorId;
                }
            }
            foreach ($publisherIds as $publisherId) {
                if (!in_array($publisherId, $uniquePublishers)) {
                    $uniquePublishers [] = $publisherId;
                }
            }
        }
        NewBookPublishDate::updateOrCreate(
            [
                'created_at' => getDateNow()
            ],
            [
                'creators' => $uniqueCreators,
                'publishers' => $uniquePublishers,
                'checked' => false,
            ]);
        return true;
    }
}
