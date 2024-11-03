<?php

namespace App\Jobs;

use App\Models\MongoDBModels\BookTempDossier1;
use App\Models\MongoDBModels\BookTempDossier2;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateDossierTemp2Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $dossier ;
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
        $data = [];
        $newDossiers = BookTempDossier1::where('creator' , $this->dossier->creator)
            ->where('book_names' , 'all',[new \MongoDB\BSON\Regex($this->dossier->book_names[0], 'i')])
            ->where('_id','!=',$this->dossier->_id)
            ->get();
        if (count($newDossiers) != 0) {
            $data['creator'] = $this->dossier->creator;
            $data['dossier_temp_one_id_original'] = $this->dossier->_id;
            $data['book_names_original'] = $this->dossier->book_names;
            foreach ($newDossiers as $dossier){
                $data ['dossier_temp_one_id'][] = $dossier->_id;
                $data['book_names'][] = $dossier->book_names;
            }
            $count = count($data['book_names']);
            $data['book_counts'] = $count;
            BookTempDossier2::create($data);
        }

    }
}
