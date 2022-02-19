<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBiBookBiPublisherTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bi_book_bi_publisher', function (Blueprint $table) {
            $table->bigIncrements('xid')->unsigned();
            $table->integer('bi_book_xid')->unsigned()->index();
            $table->integer('bi_publisher_xid')->unsigned()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bi_book_bi_publisher');
    }
}
