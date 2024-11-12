<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUnallwedInFidiboTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookFidibo', function (Blueprint $table) {
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
        Schema::table('bookFidibo', function (Blueprint $table) {
            //
        });
    }
}
