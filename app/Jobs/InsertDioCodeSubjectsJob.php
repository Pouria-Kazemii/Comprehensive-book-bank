<?php

namespace App\Jobs;

use App\Models\MongoDBModels\DioSubject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\String_;

class InsertDioCodeSubjectsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $book;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($book)
    {
        $this->book = $book;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $arrayOfSubjects = [];
        if ($this->book->xdiocode != null) {
            $dioSubjects = DioSubject::raw(function ($collection) {
                return $collection->aggregate([
                    [
                        '$match' => [
                            'has_child' => 0,
                            'dio_type' => [
                                '$ne' => 'child'
                            ]
                        ]
                    ],
                    [
                        '$unwind' => '$range'
                    ],
                    [
                        '$match' => [
                            'range.start' => ['$lte' => $this->book->xdiocode],
                            'range.end' => ['$gte' => $this->book->xdiocode]
                        ]
                    ]
                ]);
            });
            $total = 0;
            if (count($dioSubjects) > 1) {
                foreach ($dioSubjects as $dioSubject) {
                    if (!$dioSubject->except) {
                        $arrayOfSubjects [] = [$dioSubject->id_by_law => $dioSubject->title];
                        $condition = $dioSubject->level;
                        $parentId = $dioSubject->parent_id;
                        while ($condition != 0) {
                            $newSubject = DioSubject::where('id_by_law', $parentId)->first();
                            $arrayOfSubjects [] = [$newSubject->id_by_law => $newSubject->title];
                            $parentId = $newSubject->parent_id;
                            $condition--;
                        }
                    } else {
                        $total++;
                    }
                }
            } else {
                foreach ($dioSubjects as $dioSubject) {
                    $arrayOfSubjects [] = [$dioSubject->id_by_law => $dioSubject->title];
                    $condition = $dioSubject->level;
                    $parentId = $dioSubject->parent_id;
                    while ($condition != 0) {
                        $newSubject = DioSubject::where('id_by_law', $parentId)->first();
                        $arrayOfSubjects [] = [$newSubject->id_by_law => $newSubject->title];
                        $parentId = $newSubject->parent_id;
                        $condition--;
                    }
                }
            }

            if ($total == count($dioSubjects)) {
                foreach ($dioSubjects as $dioSubject) {
                    $count = count($dioSubject->except_range);
                    $except = 0;
                    foreach ($dioSubject->except_range as $value){
                        if ($this->book->xdiocode < $value['start'] || $this->book->xdiocode > $value['end']){
                            $except++;
                        }
                    }
                    if ($except == $count){
                        $arrayOfSubjects [] = [$dioSubject->id_by_law => $dioSubject->title];
                        $condition = $dioSubject->level;
                        $parentId = $dioSubject->parent_id;
                        while ($condition != 0) {
                            $newSubject = DioSubject::where('id_by_law', $parentId)->first();
                            $arrayOfSubjects [] = [$newSubject->id_by_law => $newSubject->title];
                            $parentId = $newSubject->parent_id;
                            $condition--;
                        }
                    }
                }
            }
        }
        $this->book->update([
            'diocode_subject' => $arrayOfSubjects
        ]);
    }
}
