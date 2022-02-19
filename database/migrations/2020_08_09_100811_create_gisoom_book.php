<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGisoomBook extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //'title','lang','editor','radeD','saleNashr','nobatChap','tiraj', 'tedadSafe', 'ghateChap', 'shabak10', 'shabak13','recordNumber','tarjome','desc'];
        Schema::create('bookgisoom', function (Blueprint $table) {
            $table->id();
            $table->integer('recordNumber')->index();
            $table->longText('title')->nullable();
            $table->longText('lang')->nullable();
            $table->longText('editor')->nullable();
            $table->longText('radeD')->nullable();
            $table->longText('nasher')->nullable();
            $table->integer('saleNashr')->nullable();
            $table->integer('nobatChap')->nullable();
            $table->integer('tiraj')->nullable();
            $table->integer('tedadSafe')->nullable();
            $table->longText('ghatechap')->nullable();
            $table->boolean('tarjome')->nullable()->default(false);
            $table->longText('desc')->nullable();
            $table->longText('shabak10')->nullable();
            $table->longText('shabak13')->nullable();
            $table->longText('image')->nullable();
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
        Schema::dropIfExists('bookgisoom');
    }
}
