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
use Illuminate\Support\Facades\Log;
use MongoDB\Driver\Exception\CommandException;

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
        if ($this->dossier->book_names[0] != (null or '')) {
            $data = [];

            try {
                // Escape special characters in book name for regex
                $escapedBookName = preg_quote($this->dossier->book_names[0], '/');

                $newDossiers = BookTempDossier1::where('creator', $this->dossier->creator)
                    ->where('book_names', 'all', [new \MongoDB\BSON\Regex($escapedBookName, 'i')])
                    ->where('_id', '!=', $this->dossier->_id)
                    ->get();

            } catch (CommandException $e) {
                Log::error('Regex error in document search.', [
                    'dossier_id' => $this->dossier->_id,
                    'book_names' => $this->dossier->book_names,
                    'error_message' => $e->getMessage(),
                ]);
                throw $e;
            }

            if (count($newDossiers) != 0) {
                $data['creator'] = $this->dossier->creator;
                $data['dossier_temp_one_id_original'] = $this->dossier->_id;
                $data['book_names_original'] = $this->dossier->book_names;

                foreach ($newDossiers as $dossier) {
                    $data['dossier_temp_one_id'][] = $dossier->_id;
                    $data['book_names'][] = $dossier->book_names;
                }

                $data['book_counts'] = count($data['book_names']);
                BookTempDossier2::create($data);
            }
        }
    }
}
