<?php

namespace App\Console\Commands\CorrectInfo;

use App\Models\BookShahreKetabOnline;
use App\Models\Crawler as CrawlerM;
use Illuminate\Console\Command;

class correctRepeatedRecordNumberInShahreOnlineKetab extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:correctRepeatedRecordNumberInShahreOnlineKetab {crawlerId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'delete repeted recordNumber in table shahronlineketab Command';

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
        $total = BookShahreKetabOnline::where('book_master_id',0)->count();
        try {

            $startC = 1;
            $endC = $total;

            $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
            $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-correct-repeated-recordNumber-in-shahreOnlineKetab-' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 5));
        } catch (\Exception $e) {
            $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ---------=-- ");
        }

        if (isset($newCrawler)) {
            $bar = $this->output->createProgressBar($total);
            $bar->start();
            BookShahreKetabOnline::where('book_master_id',0)->orderBy('id', 'ASC')->chunk(200, function ($books) use ($bar, $newCrawler) {
                foreach ($books as $book) {
                    // DB::enableQueryLog();
                    $book->book_master_id = 1;
                    $book->save();
                    BookShahreKetabOnline::where('recordNumber',$book->recordNumber)->where('id','!=',$book->id)->update(['book_master_id' => 2]);

                    $bar->advance();
                    $newCrawler->last = $book->id;
                    $newCrawler->save();
                }

            });
            $newCrawler->status = 2;
            $newCrawler->save();
            $this->info(" \n ---------- Finish Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
            $bar->finish();
        }
    }
}
