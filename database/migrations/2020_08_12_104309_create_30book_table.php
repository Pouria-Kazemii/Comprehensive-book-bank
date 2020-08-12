<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Create30bookTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('book30book', function (Blueprint $table) {
            $table->id();
            $table->integer('recordNumber')->index();
            $table->longText('title')->nullable();
            $table->longText('lang')->nullable();
            $table->longText('shabak')->nullable();
            $table->longText('cats')->nullable();
            $table->longText('nasher')->nullable();
            $table->integer('saleNashr')->nullable();
            $table->integer('nobatChap')->nullable();
            $table->integer('tedadSafe')->nullable();
            $table->longText('ghatechap')->nullable();
            $table->boolean('tarjome')->nullable()->default(false);
            $table->binary('desc')->nullable();
            $table->longText('jeld')->nullable();
            $table->integer('price')->nullable()->default(0);
            $table->integer('vazn')->nullable()->default(0);
            $table->longText('image')->nullable();
            $table->longText('catPath')->nullable();
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
        Schema::dropIfExists('book30book');
    }
}
