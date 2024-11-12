<?php

namespace App\Models\MongoDBModels;

use Jenssegers\Mongodb\Eloquent\Model;

class BTB_Yearly extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'cached_book_total_paragraph';

    protected $primaryKey = '_id';

    protected $fillable = [
        'year',
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
