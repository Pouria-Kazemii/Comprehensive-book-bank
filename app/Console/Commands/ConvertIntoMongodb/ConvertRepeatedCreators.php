<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\ConvertCreatorsJob;
use App\Jobs\ConvertRepeatedCreatorsJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConvertRepeatedCreators extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert:creators_repeated';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'convert repeated creators';

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
        DB::table('bookir_partner')
            ->whereRaw('xcreatorname IN (
                SELECT xcreatorname
                FROM bookir_partner
                GROUP BY xcreatorname
                HAVING COUNT(*) > 1
            )')
            ->orderBy('xcreatorname')
            ->chunk(1000, function ($books) {
                ConvertRepeatedCreatorsJob::dispatch($books);
            });
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');

        return 1;
    }

}
