<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToCachedTopPricePublishersCollection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->table('cached_top_price_publishers', function (Blueprint $collection) {
            $collection->index('year' , 'xyear');
            $collection->index('publishers.publisher_id' , 'xpublisher_id');
            $collection->index('publishers.publisher_name' , 'xpublisher_name');
            $collection->index('publishers.total_price' , 'xtotal_price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')->table('cached_top_price_publishers', function (Blueprint $collection) {
            $collection->dropIndex('xyear');
            $collection->dropIndex('xpublisher_id');
            $collection->dropIndex('xpublisher_name');
            $collection->dropIndex('xtotal_price');
        });
    }
}
