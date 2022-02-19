<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddK24bookTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookK24', function (Blueprint $table) {
            $table->id();
            $table->integer('recordNumber')->index();
            $table->longText('title')->nullable();
            $table->longText('nasher')->nullable();
            $table->longText('lang')->nullable();
            $table->longText('cats')->nullable();
            $table->integer('saleNashr')->nullable();
            $table->integer('nobatChap')->nullable();
            $table->integer('tedadSafe')->nullable();
            $table->longText('ghatechap')->nullable();
            $table->longText('shabak')->nullable();
            $table->boolean('tarjome')->nullable()->default(false);
            $table->binary('desc')->nullable();
            $table->longText('image')->nullable();
            $table->integer('price')->nullable()->default(0);
            $table->longText('DioCode')->nullable();
            $table->integer('printCount')->nullable()->default(0);
            $table->longText('printLocation')->nullable();
            $table->longText('partnerArray')->nullable();
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
        Schema::dropIfExists('bookk24');
    }
}

