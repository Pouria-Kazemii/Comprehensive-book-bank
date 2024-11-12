<?php

namespace App\Jobs\CachedData;

use App\Models\MongoDBModels\PublisherCacheData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PublishersTotalParagraphJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $year;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($year)
    {
        $this->year = $year;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $rows = PublisherCacheData::where('year' , (int) $this->year)->get();
        foreach ($rows as $row){
            $total = 0;
            if ($row->vaziri_paragraph != null){
                $total += $row->vaziri_paragraph;
            }

            if ($row->roghi_paragraph != null){
                $total += $row->roghi_paragraph;
            }

            if ($row->jibi_paragraph != null){
                $total += $row->jibi_paragraph;
            }

            if ($row->paltoe_paragraph != null){
                $total += $row->paltoe_paragraph;
            }

            if ($row->kheshti_paragraph != null){
                $total += $row->kheshti_paragraph;
            }

            if ($row->rahli_paragraph != null){
                $total += $row->rahli_paragraph;
            }

            if ($row->bayazi_paragraph != null){
                $total += $row->bayazi_paragraph;
            }

            if ($row->janamzi_paragraph != null){
                $total += $row->janamzi_paragraph;
            }

            if ($row->soltani_paragraph != null){
                $total += $row->soltani_paragraph;
            }

            if ($row->robi_paragraph != null){
                $total += $row->robi_paragraph;
            }

            if ($row->jibi_paltoe_paragraph != null){
                $total += $row->jibi_paltoe_paragraph;
            }

            if ($row->rahli_kochak_paragraph != null){
                $total += $row->rahli_kochak_paragraph;
            }

            if ($row->albumi_paragraph != null){
                $total += $row->albumi_paragraph;
            }

            if ($row->jibi_yek_dovom_paragraph != null){
                $total += $row->jibi_yek_dovom_paragraph;
            }

            if ($row->jibi_yek_chaharom_paragraph != null){
                $total += $row->jibi_yek_chaharom_paragraph;
            }

            if ($row->roghi_paltoe_paragraph != null){
                $total += $row->roghi_paltoe_paragraph;
            }

            if ($row->baghali_paragraph != null){
                $total += $row->baghali_paragraph;
            }

            if ($row->roghi_paltoe_paragraph != null){
                $total += $row->jabe_paragraph;
            }

            $row->update([
                'paragraph' => $total
            ]);
        }
    }
}
