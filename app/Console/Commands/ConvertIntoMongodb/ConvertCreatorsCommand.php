<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\ConvertCreatorsJob;
use App\Models\BookirPartner;
use App\Models\MongoDBModels\BookIrCreator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


class ConvertCreatorsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert:creators';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert creators table in mongodb';

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
        $this::info("Start converting bookir_creators table");

        $startTime = microtime(true);
        BookirPartner::chunk(1000, function ($books) {
            foreach ($books as $book) {
                ConvertCreatorsJob::dispatch($book);
            }
        });
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
    }
}
