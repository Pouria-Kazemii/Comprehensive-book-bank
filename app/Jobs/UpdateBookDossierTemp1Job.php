<?php

namespace App\Jobs;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookTempDossier1;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MongoDB\BSON\ObjectId;

class UpdateBookDossierTemp1Job implements ShouldQueue
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
        $objects = BookTempDossier1::raw(function ($collection) {
            return $collection->aggregate([
                [
                    '$match' => [
                    'creator' => $this->doc->creator,
                    '_id' => [
                        '$ne' => new ObjectId($this->doc->_id)
                        ],
                    ]
                ],
            ]);
        });
        if (count($objects) != 0) {
            $flag = false;
            $baseArray = [
                'isbn' => [$this->doc->isbn],
                'book_ids' => [$this->doc->book_ids],
                'book_names' => [$this->doc->book_names],
            ];

            foreach ($objects as $object) {
                $objectBookName = $object->book_names instanceof \MongoDB\Model\BSONArray
                    ? $object->book_names->getArrayCopy()
                    : [];

                if (count(array_unique($objectBookName)) == 1 && $objectBookName[0] == $this->doc->book_names[0]) {
                    $baseArray['isbn'][] = $object->isbn;
                    $baseArray['book_ids'][] = $object->book_ids->getArrayCopy();

                    // Perform batch delete or soft delete
                    $object->delete();
                    $flag = true;
                }
            }

            if ($flag) {
                // Flatten book_ids array
                $bookIds = array_merge(...array_map('array_values', $baseArray['book_ids']));
                // Update the main document
                $this->doc->update([
                    'isbn' => $baseArray['isbn'],
                    'book_ids' => $bookIds,
                    'book_names' => [$baseArray['book_names'][0][0]],  // First book name
                    'is_delete' => false
                ]);

                // Update related books in one query if possible
                BookIrBook2::whereIn('_id', $bookIds)->update([
                    'xmongo_parent' => $this->doc->_id
                ]);
            }
        }
    }
}
