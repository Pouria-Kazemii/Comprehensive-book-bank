<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToCachedCreatorsCollection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->table('cached_creators', function (Blueprint $collection) {
            $collection->index('creator_id' , 'xcreator_id');
            $collection->index('year','xyear');
            $collection->index('total_circulation' ,'xtotal_circulation');
            $collection->index('total_price' , 'xtotal_price');
            $collection->index('average' , 'xaverage');
            $collection->index('total_pages' , 'xtotal_pages');
            $collection->index('count' , 'xcount');
            $collection->index('first_cover_total_circulation' , 'fctc');
            $collection->index('first_cover_total_price' , 'fctpr');
            $collection->index('first_cover_total_pages' , 'fctpa');
            $collection->index('first_cover_average' , 'fca');
            $collection->index('first_cover_count' ,'fcc');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')->table('cached_creators', function (Blueprint $collection) {
            $collection->dropIndex('xcreator_id');
            $collection->dropIndex('xyear');
            $collection->dropIndex('xtotal_circulation');
            $collection->dropIndex('xtotal_pages');
            $collection->dropIndex('xtotal_price');
            $collection->dropIndex('xaverage');
            $collection->dropIndex('xcount');
            $collection->dropIndex('fctc');
            $collection->dropIndex('fctpr');
            $collection->dropIndex('fctpa');
            $collection->dropIndex('fca');
            $collection->dropIndex('fcc');

        });
    }
}
