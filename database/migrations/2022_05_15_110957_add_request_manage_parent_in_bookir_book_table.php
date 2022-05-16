<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRequestManageParentInBookirBookTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookir_book', function (Blueprint $table) {
            $table->integer('xrequest_manage_parent')->nullable()->default(0)->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookir_book', function (Blueprint $table) {
            $table->dropColumn('xrequest_manage_parent');
        });
    }
}
