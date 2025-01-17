<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Author extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('author', function (Blueprint $table) {
            $table->increments('id');
            $table->longText("f_name")->nullable();  // First Name
            $table->longText("l_name")->nullable();  // Last Name
            $table->longText("d_name");  // Display Name
            $table->longText("country")->nullable(); // From
            $table->unsignedInteger("book_id")->nullable();
            $table->foreign("book_id")->references('id')->on('books');
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
        Schema::dropIfExists('author');
    }
}
