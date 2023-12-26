<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCheckStatusAndHasPermitToGisoomTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookgisoom', function (Blueprint $table) {
            $table->integer('check_status')->default(0)->index()->after('book_master_id');
            $table->integer('has_permit')->default(0)->index()->after('check_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookgisoom', function (Blueprint $table) {
            //
        });
    }
}
