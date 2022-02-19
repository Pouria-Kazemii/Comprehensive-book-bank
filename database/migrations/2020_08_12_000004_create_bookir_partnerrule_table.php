<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookirPartnerruleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookir_partnerrule', function (Blueprint $table) {
            $table->bigIncrements('xid')->unsigned();
            $table->integer('xbookid')->unsigned()->index();
            $table->integer('xcreatorid')->unsigned()->index();
            $table->integer('xroleid')->unsigned()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bookir_partnerrule');
    }
}
