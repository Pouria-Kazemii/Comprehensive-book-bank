<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToCachedTopPriceCreatorsCollection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->table('cached_top_price_creators', function (Blueprint $collection) {
            $collection->index('year' , 'xyear');
            $collection->index('creators.creator_id' , 'xcreator_id');
            $collection->index('creators.creator_name' , 'xcreator_name');
            $collection->index('total_price' , 'xtotal_price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')->table('cached_top_price_creators', function (Blueprint $collection) {
            $collection->dropIndex('xyear');
            $collection->dropIndex('xcreator_id');
            $collection->dropIndex('xcreator_name');
            $collection->dropIndex('xtotal_price');
        });
    }
}
