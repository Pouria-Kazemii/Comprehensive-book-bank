<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToCachedBookTotalPriceCollection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->table('cached_book_total_price', function (Blueprint $collection) {
        $collection->index('year' , 'xyear');
        $collection->index('price' , 'xprice');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')->table('cached_book_total_price', function (Blueprint $collection) {
            $collection->dropIndex('xyear');
            $collection->dropIndex('xprice');
        });
    }
}
