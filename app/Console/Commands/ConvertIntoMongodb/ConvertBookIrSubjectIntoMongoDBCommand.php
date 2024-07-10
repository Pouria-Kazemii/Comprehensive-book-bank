<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\ConvertBookirSubjectsTableIntoMongoDBJob;
use App\Models\BookirSubject;
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
    protected $description = 'convert bookir_subjects table into bookir_subjects collection';

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
        $this->info("Start converting bookir_subjects table");
        $totalSubjects = BookirSubject::count();
        $progressBar = $this->output->createProgressBar($totalSubjects);
        $progressBar->start();
        $startTime = microtime(true);
        BookirSubject::chunk(1000, function ($subjects) use($progressBar) {
            foreach ($subjects as $subject) {
                ConvertBookirSubjectsTableIntoMongoDBJob::dispatch($subject);
                $progressBar->advance();
            }
        });
        $progressBar->finish();
        $this->line('');
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;
    }
}
