<?php

namespace App\Jobs;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookTempDossier1;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TakeCreatorOfTempDossierCollectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private BookTempDossier1 $doc;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($doc)
    {
        $this->doc = $doc;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $creatorId = '';
        $first = true;
        $result = true;
        foreach ($this->doc->book_ids as $book_id) {
            if ($result) {
                $book = BookIrBook2::find($book_id);
                if ($book->partners != null) {
                    foreach ($book->partners as $partner) {
                        if (in_array($partner['xrule'], ['نويسنده', 'نویسنده', 'شاعر'])) {
                            if ($first) {
                                $creatorId = $partner['xcreator_id'];
                                $first = false;
                            } else {
                                if ($partner['xcreator_id'] == $creatorId) {
                                    continue;
                                } else {
                                    $result = false;
                                    break;
                                }
                            }
                        }
                    }
                }
            }else {
                break;
            }
        }
        if (!$result){
          $this->doc->update([
              'creator' => 'multi'
          ]);
        }else{
            $this->doc->update([
               'creator' => $creatorId
            ]);
        }
    }
}
