<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFunctionCalleToMajmaApiBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('majma_api_books', function (Blueprint $table) {
            $table->string('xfunction_caller', 255)->nullable()->collation('utf8_persian_ci');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('majma_api_books', function (Blueprint $table) {
            //
        });
    }
}
