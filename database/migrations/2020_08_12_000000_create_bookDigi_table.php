<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatebookDigiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookDigi', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->integer('recordNumber')->index();
            $table->longText('title')->nullable()->index();
            $table->longText('nasher')->nullable()->index();
            $table->longText('ghatechap')->nullable()->index();
            $table->longText('shabak')->nullable()->index();
            $table->longText('cat')->nullable();
            $table->longText('noekaghaz')->nullable();
            $table->longText('saleNashr')->nullable();
            $table->longText('noechap')->nullable();
            $table->longText('jeld')->nullable();
            $table->longText('vazn')->nullable()->default(0);
            $table->binary('desc')->nullable();
            $table->binary('sellers')->nullable();
            $table->binary('features')->nullable();
            $table->longText('image')->nullable();
            $table->integer('count')->nullable()->default(0);
            $table->integer('price')->nullable()->default(0);
            $table->longText('partnerArray')->nullable();
            $table->double('rate', 2, 2)->nullable();
            $table->integer('tedadSafe')->default(0);
            $table->tinyInteger('saveBook')->nullable()->default(0);
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
        Schema::dropIfExists('bookDigi');
    }
}
