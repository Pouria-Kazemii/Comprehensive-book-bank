<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('recordNumber')->index();
            $table->longText('shabak')->nullable();
            $table->longText('Title')->nullable();
            $table->longText('mozoe')->nullable();
            $table->longText('Nasher')->nullable();
            $table->longText('barcode')->nullable();
            $table->longText('matName')->nullable();
            $table->longText('langName')->nullable();
            $table->longText('RadeAsliD')->nullable();
            $table->longText('RadeFareiD')->nullable();
            $table->longText('ShomareKaterD')->nullable();
            $table->longText('PishRade')->nullable();
            $table->longText('MahalNashr')->nullable();
            $table->longText('Yaddasht')->nullable();
            $table->longText('TedadSafhe')->nullable();
            $table->longText('saleNashr')->nullable();
            $table->boolean('EjazeReserv')->nullable();
            $table->boolean('EjazeAmanat')->nullable();
            $table->longText('Creator')->nullable();
            $table->longText('Image_Address')->nullable();
            $table->json('all')->nullable();
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
        Schema::dropIfExists('books');
    }
}
