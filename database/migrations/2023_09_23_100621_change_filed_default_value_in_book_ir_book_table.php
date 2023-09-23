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
        Schema::table('bookir_book', function (Blueprint $table) {
            $table->string('xdoctype', 50)->nullable()->change();
            $table->string('xapearance', 100)->nullable()->change();
            $table->string('xminprice', 100)->nullable()->change();
            $table->string('xcongresscode', 80)->nullable()->change();
            $table->string('xcongresscode', 20)->nullable()->change();
            $table->string('xname2', 200)->nullable()->change();
            $table->text('xdescription')->nullable()->change();
            $table->integer('xcovernumber')->default(0)->nullable()->change();
            $table->integer('xcoverprice')->default(0)->nullable()->change();
            $table->smallInteger('xissubject')->tinyInteger('xissubject')->default(0)->nullable()->unsigned()->change();
            $table->smallInteger('xissubject')->tinyInteger('xiscreator')->default(0)->nullable()->unsigned()->change();
            $table->smallInteger('xissubject')->tinyInteger('xispublisher')->default(0)->nullable()->unsigned()->change();
            $table->smallInteger('xissubject')->tinyInteger('xislibrary')->default(0)->nullable()->unsigned()->change();
            $table->smallInteger('xissubject')->tinyInteger('xistag')->default(0)->nullable()->unsigned()->change();
            $table->smallInteger('xissubject')->tinyInteger('xisseller')->default(0)->nullable()->unsigned()->change();
            $table->smallInteger('xissubject')->tinyInteger('xisname')->default(0)->nullable()->unsigned()->change();
            $table->smallInteger('xissubject')->tinyInteger('xisdoc')->default(0)->nullable()->unsigned()->change();
            $table->smallInteger('xissubject')->tinyInteger('xisdoc2')->default(0)->nullable()->unsigned()->change();
            $table->smallInteger('xissubject')->tinyInteger('xiswater')->default(0)->nullable()->unsigned()->change();
            $table->smallInteger('xissubject')->tinyInteger('xwhite')->default(0)->nullable()->unsigned()->change();
            $table->smallInteger('xissubject')->tinyInteger('xblack')->default(0)->nullable()->unsigned()->change();
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
        Schema::table('bookir_book', function (Blueprint $table) {
            //
        });
    }
}
