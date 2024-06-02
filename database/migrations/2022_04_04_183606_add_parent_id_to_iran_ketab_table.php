<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentIdToIranKetabTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//        Schema::table('bookIranketab', function (Blueprint $table) {
//            $table->integer('parentId')->nullable()->after('recordNumber')->index()->default(0);
//        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookIranketab', function (Blueprint $table) {
            $table->dropColumn('parentId');
        });
    }
}
