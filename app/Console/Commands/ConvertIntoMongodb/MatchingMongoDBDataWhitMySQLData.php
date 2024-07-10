<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Models\BookirBook;
use Illuminate\Console\Command;

class MatchingMongoDBDataWhitMySQLData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'match:mongodb';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create new sql data with mongodb collections';

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
        $this->info('start create new sql data');
        $books = BookirBook::where('mongo_id', null)->where('xpageurl2', '!=' , null);
        return true;
    }
}
