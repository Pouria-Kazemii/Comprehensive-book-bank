<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookTaaghcheTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('BookTaaghche', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->integer('recordNumber')->index();
            $table->integer('parentId')->index();
            $table->string('title',200)->collation('utf8_persian_ci')->nullable();
            $table->string('nasher',150)->collation('utf8_persian_ci')->nullable()->index();
            $table->string('saleNashr',20)->collation('utf8_persian_ci')->nullable();
            $table->integer('tedadSafe')->default(0);
            $table->string('shabak',50)->collation('utf8_persian_ci')->nullable()->index();
            $table->tinyInteger('translate')->default(0)->index();
            $table->text('content')->collation('utf8_persian_ci')->nullable();
            $table->string('images',150)->collation('utf8_persian_ci')->nullable();
            $table->integer('price')->default(0);
            $table->string('lang',255)->collation('utf8_persian_ci')->nullable();
            $table->string('fileSize',255)->collation('utf8_persian_ci')->nullable();
            $table->longText('partnerArray')->collation('utf8_persian_ci')->nullable();
            $table->string('tags',255)->collation('utf8_persian_ci')->nullable();
            $table->integer('book_master_id')->default(0)->index();
            $table->string('commentsCount',3)->collation('utf8_persian_ci')->nullable();
            $table->string('ghatechap',50)->collation('utf8_persian_ci')->nullable();
            $table->string('jeld',50)->collation('utf8_persian_ci')->nullable();
            $table->string('authorsname',255)->collation('utf8_persian_ci')->nullable();
            $table->text('authorbio')->collation('utf8_persian_ci')->nullable();
            $table->string('authorimg',50)->collation('utf8_persian_ci')->nullable();
            $table->string('rating',10)->collation('utf8_persian_ci')->nullable();
            $table->integer('check_status')->default(0)->index();
            $table->integer('commentcrawl')->unsigned()->default(0)->index();
            $table->integer('has_permit')->default(0)->index();
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
        Schema::dropIfExists('BookTaaghche');
    }
}
