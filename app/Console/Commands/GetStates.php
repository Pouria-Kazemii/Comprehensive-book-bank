<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Location\State;

class GetStates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:states' , $states = array(), $stateCodes = array();

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will get the states data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->states = State::all()->toArray();
        foreach($this->states as $state){
            array_push($this->stateCodes, $state['stateCode']);
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
        $response = Http::get('Samanpl.ir/api/api/srvStateList?Date=16747');
        $bar = $this->output->createProgressBar(count($response['results']));
        $bar->start();
        foreach($response['results'] as $resault){
            if(!in_array($resault['stateCode'], $this->stateCodes)){
                State::create($resault);
                $counter += 1;
            }
            $bar->advance();
        }
        $bar->finish();
        print("\n$counter new states added!");
        
    }
}
