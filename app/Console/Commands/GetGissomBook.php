<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;

class GetGissomBook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:gissomBook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'crawle Gisoom book from site html';

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
        $client = new Client(HttpClient::create(['timeout' => 30]));
        $crawler = $client->request('GET', 'https://www.gisoom.com/book/11000000/');
        print_r($crawler);
        exit;
    }
}
