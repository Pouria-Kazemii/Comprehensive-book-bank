<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiteBookLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('site_book_links', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('domain',255)->collation('utf8_persian_ci')->nullable();
            $table->text('book_links')->collation('utf8_persian_ci')->nullable();
            $table->integer('status')->default('0')->index();
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
        Schema::dropIfExists('site_book_links');
    }
}
