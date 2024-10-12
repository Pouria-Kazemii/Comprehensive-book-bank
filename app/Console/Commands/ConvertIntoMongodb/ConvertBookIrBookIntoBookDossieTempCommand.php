<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use Illuminate\Console\Command;

class ConvertBookIrBookIntoBookDossieTempCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'covert:bookdossier_temp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'make a temp table for book dossier';

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
