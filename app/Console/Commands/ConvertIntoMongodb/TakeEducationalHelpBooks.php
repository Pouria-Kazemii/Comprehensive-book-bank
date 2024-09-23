<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Models\MongoDBModels\BookIrBook2;
use Illuminate\Console\Command;

class TakeEducationalHelpBooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'take:educational_help_books';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'take and slice books with subject of educational help ';

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
        $books = BookIrBook2::raw();
    }
}
