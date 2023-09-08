<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgeGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('age_group', function (Blueprint $table) {
            $table->bigIncrements('xid')->unsigned();
            $table->integer('xbook_id')->index();
            $table->tinyInteger('xa')->default(0)->index();
            $table->tinyInteger('xb')->default(0)->index();
            $table->tinyInteger('xg')->default(0)->index();
            $table->tinyInteger('xd')->default(0)->index();
            $table->tinyInteger('xh')->default(0)->index();
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
        Schema::dropIfExists('age_group');
    }
}
