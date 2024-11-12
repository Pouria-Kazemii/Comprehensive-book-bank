<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteSingleWordDossiersJob implements ShouldQueue
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
        foreach ($this->dossier as $item)
        {
            if(strpos($item->book_names_original[0] , ' ') == false){
                $item->delete();
            }
        }
    }
}
