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
            $recordNumber = ($this->argument('recordNumber'))?$this->argument('recordNumber'):($lastGotBook + $countWalker);
            try {
                $this->info(" \n ---------- Try Get BOOK ".$recordNumber."              ---------- ");
                $response = Http::retry(3, 100)->timeout(10)->get('www.samanpl.ir/api/SearchAD/Details', [
                    'materialId' => 1,
                    'recordNumber' => $recordNumber ,
                ]);
            } catch (\Exception $e) {
                $response = null;
                $this->info(" \n ---------- Failed Get  ".$recordNumber."              ---------=-- ");
            }
            if($response) {
                $result = $response['Results'][0];
                $allowed = ['Creator','barcode', 'MahalNashr', 'Title', 'mozoe', 'Yaddasht', 'TedadSafhe', 'saleNashr', 'EjazeReserv', 'EjazeAmanat', 'shabak', 'Nasher', 'matName', 'langName', 'RadeAsliD', 'RadeFareiD','ShomareKaterD','PishRade','Image_Address'];
                $filtered = array_filter(
                    $result,
                    function ($key) use ($allowed) {
                        return in_array($key, $allowed);
                    },
                    ARRAY_FILTER_USE_KEY
                );

                $filtered['all'] = $response['Results'][0];
                $filtered['recordNumber'] = $recordNumber;

                $this->info(" \n ---------- Create Book   ".$recordNumber."              ---------- ");

                // filter textvalue
                $filtered['TedadSafhe'] = enNumberKeepOnly(faCharToEN($filtered['TedadSafhe']));
                $filtered['saleNashr'] = enNumberKeepOnly(faCharToEN($filtered['saleNashr']));
                $filtered['MahalNashr'] = faAlphabetKeep(faCharToEN($filtered['MahalNashr']));
                $filtered['Nasher'] = faAlphabetKeep(faCharToEN($filtered['Nasher']));

                // Filter no image book Image_Address
                if(strpos($filtered['Image_Address'], 'no-picture')!==FALSE)unset($filtered['Image_Address']);

                $authorObjectArray = array();
                if($filtered['Creator'] !=""){
                    $authorsArray=Author::authorSeprator($filtered['Creator']);

                    foreach($authorsArray as $author){
                        $authorObject = Author::firstOrCreate(array("d_name" => faAlphabetKeep($author)));
                        $authorObjectArray[] = $authorObject->id;
                    }
                }
                $this->info(" \n ---------- Author Book   ".$recordNumber."              ---------- ");

                if(trim($filtered['shabak']) != ""){
                    $shabakArray = Book::getShabakArray($filtered['shabak']);
                    foreach($shabakArray as $shabak){
                        if(! Book::where('shabak',$shabak)->first()){
                            $filtered['shabak'] = $shabak;
                            $book = Book::firstOrCreate($filtered);
                            $this->info(" \n ---------- Inserted Book   ".$recordNumber."  =   $shabak         ---------- ");
                            if(count($authorObjectArray)>0){
                                $book->authors()->attach($authorObjectArray);
                                $this->info(" \n ---------- Attach Author Book   ".$recordNumber."          ---------- ");
                            }
                        }
                    }
                }else{
                    $book = Book::firstOrCreate($filtered);
                    $this->info(" \n ---------- Inserted Book   ".$recordNumber."          ---------- ");
                    if(count($authorObjectArray)>0){
                        $book->authors()->attach($authorObjectArray);
                        $this->info(" \n ---------- Attach Author Book   ".$recordNumber."          ---------- ");
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
