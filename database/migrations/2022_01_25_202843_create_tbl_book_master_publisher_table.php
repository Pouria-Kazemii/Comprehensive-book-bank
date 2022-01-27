<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblBookMasterPublisherTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_book_master_publisher', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_master_id');
            $table->unsignedBigInteger('publisher_id');

            $table->foreign('book_master_id')->references('id')->on('tbl_book_master')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('publisher_id')->references('id')->on('tbl_publisher')->onUpdate('cascade')->onDelete('cascade');

            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_book_master_publisher');
    }
}
