<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToCachedPublishersCollectionPart2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->table('cached_publishers', function (Blueprint $collection) {
            $collection->index('first_cover_total_circulation','fctc');
            $collection->index('first_cover_total_price','fctpr');
            $collection->index('first_cover_total_pages','fctpa');
            $collection->index('first_cover_average','fca');
            $collection->index('first_cover_count' , 'fcc');
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
            $collection->dropIndex('fctc');
            $collection->dropIndex('fctpr');
            $collection->dropIndex('fctpa');
            $collection->dropIndex('fca');
            $collection->dropIndex('fcc');
        });
    }
}
