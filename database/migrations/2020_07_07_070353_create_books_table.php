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
            $table->id();
            $table->integer('recordNumber');
            $table->longText('Creator')->nullable();
            $table->longText('MahalNashr')->nullable();
            $table->longText('Title')->nullable();
            $table->longText('mozoe')->nullable();
            $table->longText('Yaddasht')->nullable();
            $table->longText('TedadSafhe')->nullable();
            $table->longText('saleNashr')->nullable();
            $table->boolean('EjazeReserv')->nullable();
            $table->boolean('EjazeAmanat')->nullable();
            $table->longText('shabak')->nullable();
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
