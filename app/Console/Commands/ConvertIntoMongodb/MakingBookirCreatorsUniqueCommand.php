<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\MakingBookirCreatorsUniqueJob;
use Illuminate\Console\Command;

class MakingBookirCreatorsUniqueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:bookir_creators:unique';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'making bookircreators collection unique and delete repeated creators';

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
        MakingBookirCreatorsUniqueJob::dispatch();
    }
}
