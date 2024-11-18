<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSomePartOfBookColumnToBookTaaghcheTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booktaaghche', function (Blueprint $table) {
            $table->longText('some_part_of_book');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('booktaaghche', function (Blueprint $table) {
            $table->dropColumn('some_part_of_book');
        });
    }
}
