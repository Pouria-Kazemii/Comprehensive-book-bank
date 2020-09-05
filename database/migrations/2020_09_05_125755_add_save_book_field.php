<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSaveBookField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookgisoom', function($table) {
            $table->boolean('saveBook')->nullable()->default(false);
        });
        Schema::table('books', function($table) {
            $table->boolean('saveBook')->nullable()->default(false);
        });
        Schema::table('book30book', function($table) {
            $table->boolean('saveBook')->nullable()->default(false);
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
