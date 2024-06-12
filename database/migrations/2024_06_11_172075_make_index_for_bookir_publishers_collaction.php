<?php

use Illuminate\Database\Migrations\Migration;
use Jenssegers\Mongodb\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeIndexForBookirPublishersCollaction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->table('bookir_publishers' , function (Blueprint $collection) {
            $collection->index(['xpublishername' =>'text'], 'name_text_index');
            $collection->index(['xpublishername'], 'name_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')->table('bookir_publishers', function (Blueprint $collection) {
            $collection->dropIndex('name_text_index');
            $collection->dropIndex('name_index');
        });
    }
}
