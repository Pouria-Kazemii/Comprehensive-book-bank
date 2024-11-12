<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMajmaApiPublishersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('majma_api_publishers', function (Blueprint $table) {
            $table->bigIncrements('xid')->unsigned();
            $table->integer('xpublisher_id')->index();
            $table->integer('xstatus')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('majma_api_publishers');
    }
}
