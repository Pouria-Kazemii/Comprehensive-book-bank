<?php

namespace App\Jobs;

use App\Models\MongoDBModels\BookTempDossier2;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateSecondBookTempDossierJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $dossier;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($dossier)
    {
        $this->dossier = $dossier;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $creator = $this->dossier->creator;
        $dossiers = BookTempDossier2::where('creator',$creator)->get();
        if ($dossiers != null)
        foreach ($dossiers as $dossier){
            if (strpos($dossier->book_names_original[0] , $this->dossier->book_names_original[0]) !== false and
                $dossier->book_counts < $this->dossier->book_counts)
            {
                $dossier->delete();
            };
        }
    }
}
