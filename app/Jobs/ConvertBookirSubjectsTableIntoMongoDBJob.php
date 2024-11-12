<?php

namespace App\Jobs;

use App\Models\BiBookBiSubject;
use App\Models\MongoDBModels\BookIrSubject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ConvertBookirSubjectsTableIntoMongoDBJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $subject;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($subject)
    {
        $this->subject = $subject;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $exist = BiBookBiSubject::where('bi_subject_xid' , $this->subject->xid)->exists();
        if($exist){
            $id = $this->subject->xid;
            $subject = trim($this->subject->xsubject);
            BookIrSubject::create([
                '_id' => $id,
                'xsubject_name' => $subject
            ]);
        }
    }
}
