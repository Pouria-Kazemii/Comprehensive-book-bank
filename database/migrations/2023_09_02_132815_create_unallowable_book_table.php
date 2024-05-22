<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnallowableBookTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unallowable_book', function (Blueprint $table) {
            $table->bigIncrements('xid')->unsigned();
            $table->string('xtitle',255)->collation('utf8_persian_ci')->nullable()->index();
            $table->string('xauthor',255)->collation('utf8_persian_ci')->nullable()->index();
            $table->string('xpublish_date',255)->collation('utf8_persian_ci')->nullable()->index();
            $table->string('xpublisher_name',255)->collation('utf8_persian_ci')->nullable()->index();
            $table->string('xtranslator',255)->collation('utf8_persian_ci')->nullable()->index();
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
        Schema::dropIfExists('unallowable_book');
    }
}
