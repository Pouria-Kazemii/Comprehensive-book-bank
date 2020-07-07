<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Library\Library;

class GetLibraries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:libraries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $response = Http::get('Samanpl.ir/api/api/srvLibList?Date=16747');
        $bar = $this->output->createProgressBar(count($response['results']));
        $bar->start();
        foreach($response['results'] as $result){
            $allowed = ['stateCode','libraryCode','libraryName','townshipCode','partCode','cityCode','villageCode','address','phone','libTypeCode','postCode'];
            $filtered = array_filter(
                $result,
                function ($key) use ($allowed) {
                    return in_array($key, $allowed);
                },
                ARRAY_FILTER_USE_KEY
            );
            Library::firstOrCreate($filtered);
            $bar->advance();
        }
        $bar->finish();
    }
}
