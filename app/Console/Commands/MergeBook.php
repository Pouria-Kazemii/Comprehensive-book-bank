<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Book as BookM;
use App\Models\Book30book as B30BookM;
use App\Models\BookGisoom as GBookM;
use App\Models\United\UBook as UBook;
use App\Models\United\UAuthor as UAuthor;
use App\Models\United\UTag as UTag;
use App\Models\United\ULibrary as ULibrary;
use App\Models\Library\Library;


class MergeBook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'merge:book {mergeCount}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merge All Book To United';

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

        $bar = $this->output->createProgressBar($this->argument('mergeCount'));
        $bar->start();
        $books = BookM::with(['authors', 'libraries'])->where('')->take($this->argument('mergeCount'))->get();
        foreach ($books as $book){

        }


        return 0;
    }
}
