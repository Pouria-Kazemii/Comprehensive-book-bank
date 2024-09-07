<?php

namespace App\Models\MongoDBModels;

use Jenssegers\Mongodb\Eloquent\Model;


class CreatorCacheData extends Model
{
    protected $connection = 'mongodb';

   protected $collection = 'cached_creators';

   protected $primaryKey = '_id';

   protected $fillable = [
       'creator_id',
       'year' ,
       'total_circulation',
       'first_cover_total_circulation',
       'total_price',
       'first_cover_total_price',
       'average',
       'first_cover_average',
       'total_pages',
       'first_cover_total_pages',
       'count',
       'first_cover_count',
       'paragraph',
       'roghi_paragraph',
       'vaziri_paragraph',
       'jibi_paragraph',
       'paltoe_paragraph',
       'kheshti_paragraph',
       'rahli_paragraph',
       'bayazi_paragraph',
       'janamzi_paragraph',
       'soltani_paragraph',
       'robi_paragraph',
       'jibi_paltoe_paragraph',
       'rahli_kochak_paragraph',
       'albumi_paragraph',
       'jibi_yek_dovom_paragraph',
       'jibi_yek_chaharom_paragraph',
       'roghi_paltoe_paragraph',
       'baghali_paragraph',
       'jabe_paragraph'
   ];
}
