<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUnallwedInKetabejamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_book_ketabejam', function (Blueprint $table) {
            $table->integer('unallowed')->default(0)->index()->after('has_permit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_book_ketabejam', function (Blueprint $table) {
            //
        });
    }
}
