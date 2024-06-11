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
//        Schema::connection('mongodb')->table('bookir_creators', function (Blueprint $collection) {
//            $collection->index('xcreatorname', 'text');
//            $collection->index('xrole');
//        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
//        Schema::connection('mongodb')->table('bookir_creators', function (Blueprint $collection) {
//            $collection->dropIndex('xcreatorname_text');
//            $collection->dropIndex('xrole');
//        });
    }
}
