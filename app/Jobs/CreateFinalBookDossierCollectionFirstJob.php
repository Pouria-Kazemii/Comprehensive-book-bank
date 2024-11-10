<?php

namespace App\Jobs;

use App\Models\MongoDBModels\BookDossier;
use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookTempDossier1;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MongoDB\BSON\ObjectId;

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
        $dossier = BookDossier::create();
        $original_temp = BookTempDossier1::findOrFail($this->dossier->dossier_temp_one_id_original);
        $isbns [] = $original_temp->isbn;
        $names [] = $original_temp->book_names;
        foreach ($original_temp->book_ids as $book_id){
            BookIrBook2::where('_id' , new ObjectId($book_id))->first()->update([
                'xmongo_parent' => (string) $dossier->_id
            ]);
        }
        $original_temp->update([
            'is_checked' => true
        ]);
        foreach ($this->dossier->dossier_temp_one_id as $tempOneId){
            $temp = BookTempDossier1::findOrFail($tempOneId);
            $isbns [] = $temp->isbn;
            $names [] = $temp->book_names;
            foreach ($temp->book_ids as $book_id){
                BookIrBook2::where('_id' , new ObjectId($book_id))->first()->update([
                    'xmongo_parent' => (string) $dossier->_id
                ]);
            }
            $temp->update([
                'is_checked' => true
            ]);
        }
        $dossier->update([
            'xmain_name' => $this->dossier->book_names_original,
            'xmain_creator' => $this->dossier->creator,
            'xnames' => $names,
            'xisbns' => $isbns
        ]);
    }
}
