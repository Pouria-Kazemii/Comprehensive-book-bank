<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use Illuminate\Console\Command;

class ConvertBookIrSubjectIntoMongoDBCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert:bookir_subjects';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'convert bi_book_subjects table into bookir_subjects collection';

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
        return 0;
    }
}
