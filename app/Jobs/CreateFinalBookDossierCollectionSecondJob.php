<?php

namespace App\Jobs;

use App\Models\MongoDBModels\BookDossier;
use App\Models\MongoDBModels\BookIrBook2;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MongoDB\BSON\ObjectId;

class CreateFinalBookDossierCollectionSecondJob implements ShouldQueue
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
        $dossier = BookDossier::create();
        foreach ($this->dossier->book_ids as $book_id){
            BookIrBook2::where('_id' , new ObjectId($book_id))->first()->update([
                'xmongo_parent' => (string) $dossier->_id
            ]);
        }

        $shortestString = array_reduce($this->dossier->book_names, function ($carry, $item) {
            // If carry is null, initialize it with the first item
            if ($carry === null || str_word_count($item, 0, "آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهی") < str_word_count($carry, 0, "آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهی")) {
                return $item;
            }
            return $carry;
        }, null);

        $dossier->update([
            'xmain_name' =>  $shortestString,
            'xmain_creator' => $this->dossier->creator,
            'xnames' => $this->dossier->book_names,
            'xisbns' => $this->dossier->isbn
        ]);
    }
}
