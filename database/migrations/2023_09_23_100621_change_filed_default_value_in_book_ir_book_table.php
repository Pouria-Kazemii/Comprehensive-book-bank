<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFiledDefaultValueInBookIrBookTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('book_ir_book', function (Blueprint $table) {
            $table->string('xdoctype', 50)->nullable()->change();
            $table->string('xapearance', 100)->nullable()->change();
            $table->string('xminprice', 100)->nullable()->change();
            $table->string('xcongresscode', 80)->nullable()->change();
            $table->string('xcongresscode', 20)->nullable()->change();
            $table->string('xname2', 200)->nullable()->change();
            $table->text('xdescription')->nullable()->change();
            $table->integer('xcovernumber')->default(0)->nullable()->change();
            $table->integer('xcoverprice')->default(0)->nullable()->change();
            $table->tinyInteger('xissubject')->default(0)->nullable()->change();
            $table->tinyInteger('xiscreator')->default(0)->nullable()->change();
            $table->tinyInteger('xispublisher')->default(0)->nullable()->change();
            $table->tinyInteger('xislibrary')->default(0)->nullable()->change();
            $table->tinyInteger('xistag')->default(0)->nullable()->change();
            $table->tinyInteger('xisseller')->default(0)->nullable()->change();
            $table->tinyInteger('xisname')->default(0)->nullable()->change();
            $table->tinyInteger('xisdoc')->default(0)->nullable()->change();
            $table->tinyInteger('xisdoc2')->default(0)->nullable()->change();
            $table->tinyInteger('xiswater')->default(0)->nullable()->change();
            $table->tinyInteger('xwhite')->default(0)->nullable()->change();
            $table->tinyInteger('xblack')->default(0)->nullable()->change();
            $table->bigInteger('xreg_userid')->default(0)->nullable()->change();
            $table->integer('xcovercount')->default(0)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('book_ir_book', function (Blueprint $table) {
            //
        });
    }
}
