<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookirPartnerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookir_partner', function (Blueprint $table) {
            $table->bigIncrements('xid')->unsigned();
            $table->string('xcreatorname', 200)->collation('utf8_persian_ci')->index();
            $table->tinyInteger('xiswiki')->unsigned();
            $table->string('xname2', 200)->collation('utf8_persian_ci');
            $table->tinyInteger('xisname')->unsigned();
            $table->integer('xregdate')->unsigned();
            $table->tinyInteger('xwhite')->unsigned()->index();
            $table->tinyInteger('xblack')->unsigned()->index();
            $table->integer('xketabir_id')->unsigned()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bookir_partner');
    }
}
