<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateIndexInBookirCreatorsCollection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->table('bookir_creators', function (Blueprint $collection) {
            $collection->dropIndex('creator_name_text');
            $collection->index(
                [
                    'xcreatorname' => 'text',
                    'xcreatorname2' => 'text'
                ]
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookir_creators_collection', function (Blueprint $table) {
            //
        });
    }
}
