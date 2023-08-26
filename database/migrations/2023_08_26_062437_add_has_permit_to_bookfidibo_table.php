<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHasPermitToBookfidiboTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookfidibo', function (Blueprint $table) {
            $table->tinyInteger('has_permit')->index()->after('check_status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookfidibo', function (Blueprint $table) {
            $table->dropColumn('has_permit');
        });
    }
}
