<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Book;
use App\Models\Library\Library;
use Carbon\Carbon;
use App\Models\Crawler;

class FindBookMulti extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'find:bookwc {crawlerId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find Book in libraries with crawler';

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
        try{
            $lastCrawler = Crawler::where('type',1)->orderBy('end', 'desc')->first();
            if(isset($lastCrawler->end))$startC = $lastCrawler->end +1;
            else $startC=1;
            $endC   = $startC + Crawler::$crawlerSize;
            $this->info(" \n ---------- Create Crawler  ".$this->argument('crawlerId')."     $startC  -> $endC         ---------=-- ");
            $newCrawler = Crawler::firstOrCreate(array('name'=>'Crawler-'.$this->argument('crawlerId'), 'start'=>$startC, 'end'=>$endC, 'status'=>1, 'type'=>1));
        }catch (\Exception $e){
            $this->info(" \n ---------- Failed Crawler  ".$this->argument('crawlerId')."              ---------=-- ");
        }

        $bar = $this->output->createProgressBar(Crawler::$crawlerSize);
        $bar->start();

        $books = Book::where('lastCheckLibraries',null)->where('recordNumber', '>', $startC-1)->where('recordNumber', '<', $endC+1)->orderBy('id')->get();
        foreach($books as $book){
            $this->info(" \n ---------- Find BOOK ".$book->id."              ---------- ");
            try {
                $response = Http::retry(3, 100)->timeout(10)->get('http://www.samanpl.ir/api/SearchAD/Libs_Show/', [
                    'materialId' => 1,
                    'recordnumber' => $book->recordNumber,
                    'OrgIdOstan' => 0,
                    'OrgIdShahr' => 0,
                ]);
                $response = json_decode($response, true);
            } catch (\Exception $e) {
                $response = null;
            }
            $libraryIds = array();

            if ($response) {
                foreach ($response['Results'] as $result) {
                    $library = Library::where('libraryCode', $result['OrgId'])->first();
                    if ($library) {
                        array_push($libraryIds, $library->id);
                    }
                }
            }
            $this->info("---------- Found BOOK IN ".count($libraryIds)." Libraries ---------- ");
                $book->libraries()->detach();
                $book->libraries()->attach($libraryIds);
                $book->lastCheckLibraries = Carbon::now()->toDateTimeString();
                $book->save();

            $bar->advance();
        }
        $newCrawler->status = 2;
        $newCrawler->save();
        $this->info(" \n ---------- Finish Crawler  ".$this->argument('crawlerId')."     $startC  -> $endC         ---------=-- ");
        $bar->finish();
    }
}
