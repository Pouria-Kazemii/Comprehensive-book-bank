<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToBookirDailyCollection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->table('bookir_daily', function (Blueprint $collection) {
            $collection->index('year','xyear');
            $collection->index('month','xmonth');
            $collection->index('day' , 'xday');
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
        Schema::connection('mongodb')->table('bookir_daily', function (Blueprint $collection) {
            $collection->dropIndex('xyear');
            $collection->dropIndex('xmonth');
            $collection->dropIndex('xday');
            $collection->dropIndex('xcount');
        });
    }
}
