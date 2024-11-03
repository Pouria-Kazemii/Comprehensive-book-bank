<?php

namespace App\Jobs;

use App\Models\MongoDBModels\BookDossier;
use App\Models\MongoDBModels\BookTempDossier1;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateFinalBookDossierCollectionFirstJob implements ShouldQueue
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
        $isbns = [];
        $names = [];
        foreach ($this->dossier->dossier_temp_one_id as $tempOneId){
            $temp = BookTempDossier1::findOrFail($tempOneId);

        }
        BookDossier::create([
            'xmain_name' => $this->dossier->book_names_original
        ]);
    }
}
