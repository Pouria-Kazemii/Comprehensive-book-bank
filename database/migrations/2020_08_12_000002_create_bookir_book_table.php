<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookirBookTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookir_book', function (Blueprint $table) {
            $table->bigIncrements('xid')->unsigned();
            $table->integer('xdocid')->unsigned();
            $table->integer('xsiteid')->unsigned();
            $table->string('xpageurl', 100)->collation('utf8_persian_ci')->index();
            $table->string('xname', 200)->collation('utf8_persian_ci')->index();
            $table->string('xdoctype', 50)->collation('utf8_persian_ci');
            $table->integer('xpagecount');
            $table->string('xformat', 30)->collation('utf8_persian_ci');
            $table->string('xcover', 30)->collation('utf8_persian_ci');
            $table->integer('xprintnumber')->unsigned();
            $table->integer('xcirculation');
            $table->integer('xcovernumber')->unsigned();
            $table->integer('xcovercount')->unsigned();
            $table->string('xapearance', 100)->collation('utf8_persian_ci');
            $table->string('xisbn', 30)->collation('utf8_persian_ci');
            $table->string('xisbn2', 20)->collation('utf8_persian_ci')->index();
            $table->date('xpublishdate');
            $table->string('xcoverprice', 30)->collation('utf8_persian_ci');
            $table->string('xminprice', 30)->collation('utf8_persian_ci');
            $table->string('xcongresscode', 80)->collation('utf8_persian_ci');
            $table->string('xdiocode', 30)->collation('utf8_persian_ci')->index();
            $table->string('xlang', 30)->collation('utf8_persian_ci');
            $table->string('xpublishplace', 50)->collation('utf8_persian_ci');
            $table->text('xdescription')->collation('utf8_persian_ci');
            $table->string('xweight', 20)->collation('utf8_persian_ci');
            $table->string('ximgeurl', 100)->collation('utf8_persian_ci');
            $table->string('xpdfurl', 100)->collation('utf8_persian_ci');
            $table->integer('xregdate');
            $table->tinyInteger('xissubject')->unsigned();
            $table->tinyInteger('xiscreator')->unsigned();
            $table->tinyInteger('xispublisher')->unsigned();
            $table->tinyInteger('xislibrary')->unsigned();
            $table->tinyInteger('xistag')->unsigned();
            $table->tinyInteger('xisseller')->unsigned();
            $table->string('xname2', 200)->collation('utf8_persian_ci')->index();
            $table->tinyInteger('xisname')->unsigned();
            $table->tinyInteger('xisdoc')->unsigned();
            $table->tinyInteger('xisdoc2')->unsigned();
            $table->tinyInteger('xiswater')->unsigned();
            $table->tinyInteger('xwhite')->unsigned()->index();
            $table->tinyInteger('xblack')->unsigned()->index();
            $table->integer('xparent')->default(0)->index();
            $table->string('mongo_id', 255)->nullable()->index()->collation('utf8_persian_ci');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bookir_book');
    }
}
