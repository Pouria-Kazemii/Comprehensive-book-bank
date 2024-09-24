<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateEducationalHelpBooksJob implements ShouldQueue
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
        if ($this->book->subjects != null) {
            foreach ($this->book->subjects as $subject) {
                if ($subject['xsubject_id'] == 260442) {
                    $this->book->update([
                        'diocode_subject' => [
                            [
                                4 => "کمک آموزشی"
                            ]
                        ]
                    ]);
                }
            }
        }
    }
}
