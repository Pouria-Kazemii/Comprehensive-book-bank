<?php

namespace App\Console\Commands;

use App\Models\BookTaaghche;
use App\Models\Crawler as CrawlerM;
use Exception;
use Goutte\Client;
use Illuminate\Console\Command;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

class GetTaaghcheContradictionsList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:taaghcheContradictionsList {rowId} {miss?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Contradictions List Command';

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
        if ($this->argument('miss') && $this->argument('miss') == 1) {
            try {
                $lastCrawler = CrawlerM::where('type', 2)->where('status', 1)->orderBy('end', 'ASC')->first();
                if (isset($lastCrawler->end)) {
                    $startC = $lastCrawler->start;
                    $endC = $lastCrawler->end;
                    $this->info(" \n ---------- Check  " . $this->argument('rowId') . "     $startC  -> $endC         ---------=-- ");
                    $newCrawler = $lastCrawler;
                }
            } catch (\Exception $e) {
                $this->info(" \n ---------- Failed Crawler  " . $this->argument('rowId') . "              ---------=-- ");
            }
        } else {
            try {
                $lastCrawler = CrawlerM::where('name', 'LIKE', 'Contradictions-Taaghche-%')->where('type', 2)->orderBy('end', 'desc')->first();
                if (isset($lastCrawler->end)) {
                    $startC = $lastCrawler->end + 1;
                } else {
                    $startC = 1;
                }

                $endC = $startC + CrawlerM::$crawlerSize;
                $this->info(" \n ---------- Check  " . $this->argument('rowId') . "     $startC  -> $endC         ---------=-- ");
                $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Contradictions-Taaghche-' . $this->argument('rowId'), 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 2));
            } catch (\Exception $e) {
                $this->info(" \n ---------- Check  " . $this->argument('rowId') . "              ---------=-- ");
            }
        }
        if (isset($newCrawler)) {
            $rowId = $startC;
            while ($rowId <= $endC) {
                $book_data = BookTaaghche::where('id',$rowId)->first();
                if(isset($book_data->shabak) AND $book_data->shabak != NULL){
                    $this->info($book_data->shabak);
                    $data = "isbn=" . $book_data->shabak;
                    $url = "http://dcapi.k24.ir/api/web/v1/book/find";
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'token:$2y$10$5JQeRM6hBYXyg2A3THx/ze/QuIK1dnRyLD7H3pPGYxvpkixkV2IZe',
                    ));
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $response = curl_exec($ch);
                    if (curl_errno($ch)) {
                        $this->info(" \n ---------- Try Get BOOK " . $rowId . "              ---------- ");
                        echo 'error:' . curl_error($ch);
                    } else {
                        $response = json_decode($response);
                        if (isset($response->status) and $response->status == 200 AND !empty($response->data->list) and $response->data->list != null) {
                            $update_data = array(
                                'check_status'=>1
                            );
                        
                        }else{
                            $update_data = array(
                                'check_status'=>2
                            );
                        }
    
                    }
                }else{
                    $this->info('row no isbn');
                    $update_data = array(
                        'check_status'=>3
                    );
                }

                BookTaaghche::where('id',$rowId)->update($update_data);
                $rowId++;

            }

        }else{
            $this->info('else');
        }
    }
}