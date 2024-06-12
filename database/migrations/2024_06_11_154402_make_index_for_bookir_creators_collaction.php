<?php

use Illuminate\Database\Migrations\Migration;
use Jenssegers\Mongodb\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeIndexForBookirCreatorsCollaction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->table('bookir_creators', function (Blueprint $collection) {
            $collection->index('xcreatorname', 'creator_name');
            $collection->index(['xcreatorname' => 'text'] , 'creator_name_text');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')->table('bookir_creators', function (Blueprint $collection) {
            $collection->dropIndex('creator_name_text');
            $collection->dropIndex('creator_name');
        });
    }
}
