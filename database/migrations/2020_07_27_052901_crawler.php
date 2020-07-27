<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Crawler extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crawler', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('start')->unique();
            $table->unsignedInteger('end')->unique();
            $table->integer('status')->index()->default(0);
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
        Schema::dropIfExists('_crawler_');
    }
}
