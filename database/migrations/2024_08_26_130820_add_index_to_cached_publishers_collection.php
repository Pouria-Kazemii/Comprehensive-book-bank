<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToCachedPublishersCollection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->table('cached_publishers', function (Blueprint $collection) {
            $collection->index('year' , 'xyear');
            $collection->index('publisher_id' , 'xpublisher_id');
            $collection->index('total_circulation','xtotal_circulation');
            $collection->index('total_price' , 'xtotal_price');
            $collection->index('average' , 'xaverage');
            $collection->index('total_pages' ,'xtotal_pages');
            $collection->index('count' , 'xcount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')->table('cached_publishers', function (Blueprint $collection) {
            $collection->dropIndex('xyear');
            $collection->dropIndex('xpublisher_id');
            $collection->dropIndex('xtotal_circulation');
            $collection->dropIndex('xtotal_price');
            $collection->dropIndex('xaverage');
            $collection->dropIndex('xtotal_pages');
            $collection->dropIndex('xcount');
        });
    }
}
