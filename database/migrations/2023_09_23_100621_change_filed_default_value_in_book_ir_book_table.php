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
            $table->string('xname', 200)->nullable()->change();
            $table->string('xname2', 200)->nullable()->change();
            $table->string('xlang', 30)->collation('utf8_persian_ci')->nullable()->change();
            $table->string('xweight', 20)->collation('utf8_persian_ci')->nullable()->default(0)->change();
            $table->string('ximgeurl', 255)->collation('utf8_persian_ci')->nullable()->change();
            $table->string('xpdfurl', 255)->collation('utf8_persian_ci')->nullable()->change();
            $table->date('xpublishdate')->nullable()->change();
            $table->text('xdescription')->nullable()->change();
            $table->integer('xcovernumber')->default(0)->nullable()->change();
            $table->integer('xcoverprice')->default(0)->nullable()->change();
            $table->integer('xpagecount')->default(0)->nullable()->unsigned()->change();
            $table->integer('xprintnumber')->default(0)->nullable()->unsigned()->change();
            $table->integer('xcirculation')->default(0)->nullable()->unsigned()->change();
            $table->integer('xregdate')->default(0)->nullable()->unsigned()->change();
            $table->smallInteger('xissubject')->tinyInteger('xissubject')->default(0)->nullable()->unsigned()->change();
            $table->smallInteger('xiscreator')->tinyInteger('xiscreator')->default(0)->nullable()->unsigned()->change();
            $table->smallInteger('xispublisher')->tinyInteger('xispublisher')->default(0)->nullable()->unsigned()->change();
            $table->smallInteger('xislibrary')->tinyInteger('xislibrary')->default(0)->nullable()->unsigned()->change();
            $table->smallInteger('xistag')->tinyInteger('xistag')->default(0)->nullable()->unsigned()->change();
            $table->smallInteger('xisseller')->tinyInteger('xisseller')->default(0)->nullable()->unsigned()->change();
            $table->smallInteger('xisname')->tinyInteger('xisname')->default(0)->nullable()->unsigned()->change();
            $table->smallInteger('xisdoc')->tinyInteger('xisdoc')->default(0)->nullable()->unsigned()->change();
            $table->smallInteger('xisdoc2')->tinyInteger('xisdoc2')->default(0)->nullable()->unsigned()->change();
            $table->smallInteger('xwhite')->tinyInteger('xiswater')->default(0)->nullable()->unsigned()->change();
            $table->smallInteger('xwhite')->tinyInteger('xwhite')->default(0)->nullable()->unsigned()->change();
            $table->smallInteger('xblack')->tinyInteger('xblack')->default(0)->nullable()->unsigned()->change();
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
