<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblBookMasterPersonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_book_master_person', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_master_id');
            $table->unsignedBigInteger('person_id');
            $table->string('role', 20)->comment('author or translator or editor');

            $table->foreign('book_master_id')->references('id')->on('tbl_book_master')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('person_id')->references('id')->on('tbl_person')->onUpdate('cascade')->onDelete('cascade');

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
        Schema::dropIfExists('tbl_book_master_person');
    }
}
