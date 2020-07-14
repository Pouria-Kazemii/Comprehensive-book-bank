<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Book;


class GetBook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:book {count}';

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
        BOOK::getLastBook();
        $bar = $this->output->createProgressBar($this->argument('count'));
        $bar->start();
        for ($x = $this->argument('start'); $x <= $this->argument('end'); $x++) {
            try {
                $response = Http::retry(10, 100)->get('www.samanpl.ir/api/SearchAD/Details', [
                    'materialId' => 1,
                    'recordNumber' => $x,
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
                $filtered['recordNumber'] = $x;
                Book::firstOrCreate($filtered);
            }
            $bar->advance();
        }
        $bar->finish();
    }
}
