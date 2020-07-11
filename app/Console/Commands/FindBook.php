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
    protected $signature = 'find:book {start} {end}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Commands description';

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
        $bar = $this->output->createProgressBar($this->argument('start') - $this->argument('end'));
        $bar->start();
        for ($x = $this->argument('start'); $x <= $this->argument('end'); $x++) {
            try {
                $response = Http::retry(10, 100)->get('http://www.samanpl.ir/api/SearchAD/Libs_Show/', [
                    'materialId' => 1,
                    'recordnumber' => $x,
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
                    // return $result['OrgId'];
                    $library = Library::where('libraryCode', $result['OrgId'])->first();
                    if ($library) {
                        array_push($libraryIds, $library->id);
                    }
                }
            }
            $book = Book::where('recordNumber', $x)->first();
            if ($book) {
                $book->libraries()->detach();
                $book->libraries()->attach($libraryIds);
            }
            $bar->advance();
        }
        $bar->finish();
    }
}
