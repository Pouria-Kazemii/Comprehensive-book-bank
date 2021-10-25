<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Adddigibook extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookDigi', function (Blueprint $table) {
            $table->id();
            $table->longText('recordNumber')->index();
            $table->longText('title')->nullable();
            $table->longText('nasher')->nullable();
            $table->longText('ghatechap')->nullable();
            $table->longText('shabak')->nullable();
            $table->longText('cat')->nullable();
            $table->longText('noekaghaz')->nullable();
            $table->longText('noechap')->nullable();
            $table->longText('jeld')->nullable();
            $table->longText('vazn')->nullable();
            $table->binary('desc')->nullable();
            $table->binary('sellers')->nullable();
            $table->binary('features')->nullable();
            $table->longText('images')->nullable();
            $table->integer('count')->nullable()->default(0);
            $table->integer('tedadSafe')->nullable()->default(0);
            $table->integer('price')->nullable()->default(0);
            $table->longText('partnerArray')->nullable();
            $table->float('rate', 2, 2)->nullable();
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
        Schema::dropIfExists('bookDigi');
    }
}
