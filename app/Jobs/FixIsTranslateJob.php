<?php

namespace App\Jobs;

use App\Models\MongoDBModels\BookIrBook2;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MongoDB\BSON\ObjectId;

class FixIsTranslateJob implements ShouldQueue
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
        BookIrBook2::where('_id' , new ObjectId($this->book->_id))->first();
        $is_translate = 1;
        if (count($this->book->partners) == 0) {
            if (count($this->book->languages) == 1 and $this->book->languages[0]['name'] == 'فارسی'){
                $is_translate = 1;
            }else {
                $is_translate = 3;
            }
        }

        foreach ($this->book->partners as $partner) {
            if ($partner['xrule'] == 'مترجم'
                or $partner['xrule'] == 'ترجمه مقدمه'
                or $partner['xrule'] == 'ترجمه به شعر'
                or $partner['xrule'] == 'ترجمه انگليسي'
                or $partner['xrule'] == 'ترجمه انگلیسی') {
                $is_translate = 2;
            }

        }

        $this->book->update([
            'is_translate' => $is_translate
        ]);
    }
}
