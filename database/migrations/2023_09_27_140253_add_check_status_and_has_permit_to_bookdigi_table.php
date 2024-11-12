<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCheckStatusAndHasPermitToBookdigiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookDigi', function (Blueprint $table) {
            $table->integer('check_status')->default(0)->index();
            $table->integer('has_permit')->default(0)->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookDigi', function (Blueprint $table) {
            //
        });
    }
}
