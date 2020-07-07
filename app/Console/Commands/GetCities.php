<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Location\City;

class GetCities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:cities', $cities = array(), $townshipCodes = array();

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will get the cities data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->cities = City::all()->toArray();
        foreach($this->cities as $city){
            array_push($this->townshipCodes, $city['townshipCode']);
        }

    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $counter = 0;
        $response = Http::get('Samanpl.ir/api/api/srvtownShipList?Date=16747');
        $bar = $this->output->createProgressBar(count($response['results']));
        $bar->start();
        foreach($response['results'] as $resault){
            if(!in_array($resault['townshipCode'], $this->townshipCodes)){
                City::create($resault);
                $counter += 1;
            }
            $bar->advance();
        }
        $bar->finish();
        print("\n$counter new cities added!");
    }
}
