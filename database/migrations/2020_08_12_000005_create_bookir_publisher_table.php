<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookirPublisherTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookir_publisher', function (Blueprint $table) {
            $table->bigIncrements('xid')->unsigned();
            $table->tinyInteger('xtabletype')->unsigned();
            $table->integer('xsiteid')->unsigned();
            $table->integer('xparentid')->unsigned();
            $table->string('xpageurl', 80)->collation('utf8_persian_ci')->index();
            $table->string('xpublishername', 100)->collation('utf8_persian_ci')->index();
            $table->string('xmanager', 50)->collation('utf8_persian_ci');
            $table->string('xactivity', 60)->collation('utf8_persian_ci');
            $table->string('xplace', 60)->collation('utf8_persian_ci');
            $table->string('xaddress', 100)->collation('utf8_persian_ci');
            $table->string('xpobox', 40)->collation('utf8_persian_ci');
            $table->string('xzipcode', 40)->collation('utf8_persian_ci');
            $table->string('xphone', 50)->collation('utf8_persian_ci');
            $table->string('xcellphone', 40)->collation('utf8_persian_ci');
            $table->string('xfax', 40)->collation('utf8_persian_ci');
            $table->string('xlastupdate', 30)->collation('utf8_persian_ci');
            $table->string('xtype', 40)->collation('utf8_persian_ci');
            $table->string('xpermitno', 40)->collation('utf8_persian_ci');
            $table->string('xemail', 40)->collation('utf8_persian_ci');
            $table->string('xsite', 40)->collation('utf8_persian_ci');
            $table->string('xisbnid', 50)->collation('utf8_persian_ci');
            $table->string('xfoundingdate', 40)->collation('utf8_persian_ci');
            $table->string('xispos', 20)->collation('utf8_persian_ci');
            $table->string('ximageurl', 50)->collation('utf8_persian_ci');
            $table->integer('xregdate')->unsigned();
            $table->string('xpublishername2', 100)->collation('utf8_persian_ci');
            $table->tinyInteger('xiswiki')->unsigned();
            $table->tinyInteger('xismajma')->unsigned();
            $table->tinyInteger('xisname')->unsigned();
            $table->mediumText('xsave')->collation('utf8_persian_ci');
            $table->tinyInteger('xwhite')->unsigned()->index();
            $table->tinyInteger('xblack')->unsigned()->index();
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
        Schema::dropIfExists('bookir_publisher');
    }
}
