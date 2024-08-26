<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToCachedPublishersTotalCountCollection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->table('cached_publishers_total_count', function (Blueprint $collection) {
            $collection->index('publisher_id', 'xpublisher_id');
            $collection->index('count','xcount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')->table('cached_publishers_total_count', function (Blueprint $collection) {
            $collection->dropIndex('xpublisher_id');
            $collection->dropIndex('xcount');
        });
    }
}
