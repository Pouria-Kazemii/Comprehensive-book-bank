<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateErshadBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ershad_books', function (Blueprint $table) {
            $table->bigIncrements('nid')->unsigned();
            $table->string('id', 255)->nullable()->collation('latin1_swedish_ci');
            $table->string('title', 255)->nullable()->collation('utf8_persian_ci');
            $table->string('author1', 255)->nullable()->collation('utf8_persian_ci');
            $table->string('author2', 255)->nullable()->collation('utf8_persian_ci');
            $table->string('author3', 255)->nullable()->collation('utf8_persian_ci');
            $table->string('author4', 255)->nullable()->collation('utf8_persian_ci');
            $table->string('booksize', 255)->nullable()->collation('utf8_persian_ci');
            $table->string('isbn', 255)->nullable()->collation('utf8_persian_ci');
            $table->string('isbn2', 20)->collation('utf8_persian_ci')->index();
            $table->string('publisher', 255)->nullable()->collation('utf8_persian_ci');
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
        Schema::dropIfExists('ershad_books');
    }
}
