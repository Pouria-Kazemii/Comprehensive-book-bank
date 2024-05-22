<?php

namespace App\Models\MongoDBModels;

use Jenssegers\Mongodb\Eloquent\Model;

class BookDossier extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'book_dossier';

    protected $primaryKey = '_id';

    protected $fillable = [
        'xmain_name',
        'xtotal_page' ,
        'xtotal_price' ,
        'xwhite',
        'xblack',
        'xis_translate' ,
        'xnames',
        'xmain_creator',
        'xpage_counts',
        'xformats',
        'xcovers',
        'xprint_numbers',
        'xcirculations',
        'xisbns',
        'xisbns2',
        'xisbns3',
        'xpublishdates_shamsi',
        'cover_prices',
        'xdiocodes',
        'xpublish_places',
        'xdescriptions',
        'xweights',
        'ximageurls',
        'xpdfurls',
        'xlanguages',
        'xpartners',
        'xpublishers',
        'xsubjects',
        'xage_groups'
    ];

    public $timestamps = false ;
}
