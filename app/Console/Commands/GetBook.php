<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Book;
use App\Models\Author;


class GetBook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:book {count} {recordNumber?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'this command will get books!';

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
        $lastGotBook = BOOK::getLastBookRecordNumber();
        $bar = $this->output->createProgressBar($this->argument('count'));
        $bar->start();
        $itemGotten = 1;
        $countWalker = 1;

        while ($itemGotten <= $this->argument('count')){
            try {
                $response = Http::retry(5, 100)->get('www.samanpl.ir/api/SearchAD/Details', [
                    'materialId' => 1,
                    // 'recordNumber' => $lastGotBook + $countWalker,
                    'recordNumber' => ($this->argument('recordNumber'))?$this->argument('recordNumber'):($lastGotBook + $countWalker) ,
                ]);
            } catch (\Exception $e) {
                $response = null;
            }
            if($response) {
                $result = $response['Results'][0];
                $allowed = ['Creator', 'MahalNashr', 'Title', 'mozoe', 'Yaddasht', 'TedadSafhe', 'saleNashr', 'EjazeReserv', 'EjazeAmanat', 'shabak'];
                $filtered = array_filter(
                    $result,
                    function ($key) use ($allowed) {
                        return in_array($key, $allowed);
                    },
                    ARRAY_FILTER_USE_KEY
                );

                $filtered['all'] = $response['Results'][0];

                $authorObjectArray = array();
                if($filtered['Creator'] !=""){
                    $authorsArray=Author::authorSeprator($filtered['Creator']);

                    foreach($authorsArray as $author){
                        $authorObject = Author::firstOrCreate(array("d_name" => $author));
                        $authorObjectArray[] = $authorObject->id;
                    }
                }


                if(trim($filtered['shabak']) != ""){
                    $shabakArray = Book::getShabakArray($filtered['shabak']);
                    foreach($shabakArray as $shabak){
                        if(! Book::where('shabak',$shabak)->first()){
                            $filtered['shabak'] = $shabak;
                            $book = Book::firstOrCreate($filtered);
                            if(count($authorObjectArray)>0){
                                $book->authors()->attach($authorObjectArray);
                            }
                        }
                    }
                }else{
                    $book = Book::firstOrCreate($filtered);
                    if(count($authorObjectArray)>0){
                        $book->authors()->attach($authorObjectArray);
                    }
                }


                $itemGotten ++;
                $bar->advance();
            }
            $countWalker ++;
        }

        $bar->finish();
    }
}
