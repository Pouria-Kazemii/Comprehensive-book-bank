<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Book;
use App\Models\Library\Library;

class FindBook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'find:book {count}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find Book in Libraries';

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
        $bar = $this->output->createProgressBar($this->argument('count'));
        $bar->setOverwrite(false);
        $bar->setMessage('Starting...');
        $bar->start();

        $books = Book::doesntHave('libraries')->orderBy('created_at', 'desc')->take($this->argument('count'))->get();
        foreach($books as $book){
            $bar->setMessage(" \n ---------- Find BOOK ".$book->id." ---- ");
            try {
                $response = Http::retry(5, 100)->get('http://www.samanpl.ir/api/SearchAD/Libs_Show/', [
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
            $bar->setMessage(" \n ---------- Found BOOK IN ".count($libraryIds)." Libraries ---- ");
                $book->libraries()->detach();
                $book->libraries()->attach($libraryIds);
            $bar->advance();
        }
        $bar->finish();

    }
}
