<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHasPermitToBooktaaghcheTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booktaaghche', function (Blueprint $table) {
            $table->tinyInteger('has_permit')->index()->after('commentcrawl')->default(0);
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
            $table->dropColumn('has_permit');
        });
    }
}
