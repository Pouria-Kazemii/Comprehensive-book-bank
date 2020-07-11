<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLibrariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('libraries', function (Blueprint $table) {
            $table->id();
            $table->json('all')->nullable();
            $table->integer('stateCode')->nullable();
            $table->integer('libraryCode');
            $table->string('libraryName')->nullable();
            $table->integer('townshipCode')->nullable();
            $table->integer('partCode')->nullable();
            $table->integer('cityCode')->nullable();
            $table->integer('villageCode')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->integer('libTypeCode')->nullable();
            $table->string('postCode')->nullable();
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
        Schema::dropIfExists('libraries');
    }
}
