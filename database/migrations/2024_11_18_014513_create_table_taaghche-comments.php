<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableTaaghcheComments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('taaghche_comments',function (Blueprint $table){
           $table->string('name');
           $table->longText('comment');
           $table->integer('rate')->nullable();
           $table->integer('taaghche_book_id');
           $table->foreign('taaghche_book_id')
               ->on('bookTaaghche')
               ->references('recordNumber')
               ->onDelete('cascade');
       });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
