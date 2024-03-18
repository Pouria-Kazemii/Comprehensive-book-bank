<?php

namespace App\Console\Commands\CorrectInfo;

use App\Models\BookKetabrah;
use App\Models\Crawler as CrawlerM;
use Illuminate\Console\Command;

class correctRepeatedRecordNumberInKetabrah extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:correctRepeatedRecordNumberInKetabrah {crawlerId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'delete repeted recordNumber in table Ketabrah Command';

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
        $total = BookKetabrah::where('check_status',0)->count();
        try {

            $startC = 1;
            $endC = $total;

            $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
            $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-correct-repeated-recordNumber-in-Ketabrah-' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 5));
        } catch (\Exception $e) {
            $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ---------=-- ");
        }

        if (isset($newCrawler)) {
            $bar = $this->output->createProgressBar($total);
            $bar->start();
            BookKetabrah::where('check_status',0)->orderBy('id', 'DESC')->chunk(200, function ($books) use ($bar, $newCrawler) {
                foreach ($books as $book) {
                    // DB::enableQueryLog();
                    $book->check_status = 1;
                    $book->save();
                    BookKetabrah::where('recordNumber',$book->recordNumber)->where('id','!=',$book->id)->update(['check_status' => 2]);

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
