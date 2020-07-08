<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Library\Library;

class locateBook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'find:book';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'this command will find books';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->libraries = Library::where('libraryCode', '<', 300000)->get();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $bar = $this->output->createProgressBar($this->libraries->count());
        $bar->start();
        foreach ($this->libraries as $library) {
            try {
                $response = Http::retry(10, 100)->get('samanpl.ir/api/SearchAD/isbnSearch', [
                    'OrgIdType' => 10000,
                    'OrgIdOstan' => $library->stateCode,
                    'OrgIdShahr' => $library->townshipCode,
                    'OrgId' => $library->libraryCode,
                    'QueryStatement' => 9789644059285,
                    'Page' => 1,
                    'Pagesize' => 10,
                ]);
                $bar->advance();
            } catch (\Exception $e) {
            }
        }
        $bar->finish();
        print($this->libraries->count());
    }
}
