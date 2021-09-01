<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IranketabTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookIranketab', function (Blueprint $table) {
            $table->id();
            $table->integer('recordNumber')->index();
            $table->longText('title')->nullable();
            $table->longText('enTitle')->nullable();
            $table->longText('nasher')->nullable();
            $table->longText('refCode')->nullable();
            $table->longText('tags')->nullable();
            $table->integer('saleNashr')->nullable();
            $table->integer('nobatChap')->nullable();
            $table->integer('tedadSafe')->nullable();
            $table->longText('ghatechap')->nullable();
            $table->longText('shabak')->nullable();
            $table->longText('jeld')->nullable();
            $table->boolean('traslate')->nullable()->default(false);
            $table->binary('desc')->nullable();
            $table->binary('features')->nullable();
            $table->binary('partsText')->nullable();
            $table->binary('notes')->nullable();
            $table->binary('prizes')->nullable();
            $table->longText('images')->nullable();
            $table->integer('price')->nullable()->default(0);
            $table->longText('partnerArray')->nullable();
            $table->float('rate', 8, 2)->nullable();
            $table->boolean('saveBook')->nullable()->default(false);
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
        Schema::dropIfExists('bookIranketab');
    }
}
