<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Book as BookM;
use App\Models\Book30book as B30BookM;
use App\Models\BookGisoom as GBookM;
use App\Models\United\UBook as UBook;
use App\Models\United\UAuthor as UAuthor;
use App\Models\United\UTag as UTag;
use App\Models\United\ULibrary as ULibrary;
use App\Models\Library\Library;


class MergeBook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'merge:book {mergeCount}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merge All Book To United';

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

        $bar = $this->output->createProgressBar($this->argument('mergeCount'));
        $this->info(" \n ----------  SATRT MERGE BOOK MODEL   ----------");
        $bar->start();
        $books = BookM::with(['authors', 'libraries'])->where('saveBook',false)->take($this->argument('mergeCount'))->get();
        foreach ($books as $book){
            $this->info($book->Title);

            $temp['title'] = $book->Title;
            $temp['nasher'] = $book->Nasher;
            $temp['recordNumber'] = $book->recordNumber;
            $temp['shabak'] = $book->shabak;
            $temp['barcode'] = $book->barcode;
            $temp['mozoe'] = $book->mozoe;
            $temp['salenashr'] = $book->saleNashr;
            $temp['mahalnashr'] = $book->MahalNashr;
            $temp['tedadsafhe'] = $book->TedadSafhe;
            $temp['image'] = $book->Image_Address;
            $temp['lang'] = $book->langName;
            $temp['radeasliD'] = $book->RadeAsliD;
            $temp['radefareiD'] = $book->RadeFareiD;
            $temp['katerD'] = $book->ShomareKaterD;
            $temp['pishrade'] = $book->PishRade;
            $temp['authors'] = array();
                    foreach($book->authors as $author){
                        $temp['authors'][] = $author->d_name;
                    }
            $temp['libraries'] = array();
                foreach($book->libraries as $key=>$library){
                    $temp['libraries'][$key]['code']      = $library->libraryCode;
                    $temp['libraries'][$key]['name']      = $library->libraryName;
                    $temp['libraries'][$key]['address']   = $library->address;
                    $temp['libraries'][$key]['postcode']  = $library->postCode;
                    $temp['libraries'][$key]['phone']     = $library->phone;
                    $temp['libraries'][$key]['state']     = $library->state->stateName;
                    $temp['libraries'][$key]['city']      = $library->city->townshipName;
                }



            $bar->advance();
        }
        $bar->finish();
        $this->info(" \n ----------  FINISH MERGE BOOK MODEL  ----------");


        return 0;
    }
}
