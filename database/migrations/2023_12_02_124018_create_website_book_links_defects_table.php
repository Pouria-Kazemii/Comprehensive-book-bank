<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebSiteBookLinksDefectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_website_book_links_defects', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('siteName',255)->collation('utf8_persian_ci')->nullable();
            $table->text('book_links')->collation('utf8_persian_ci')->nullable();
            $table->string('recordNumber',255)->collation('utf8_persian_ci')->nullable();
            $table->integer('bookId')->nullable()->index();
            $table->integer('bugId')->nullable()->index();
            $table->longText('crawlerInfo')->collation('utf8_persian_ci')->nullable();
            $table->string('crawlerTime',255)->collation('utf8_persian_ci')->nullable();
            $table->integer('crawlerStatus')->nullable()->index();
            $table->integer('result')->nullable()->index();
            $table->integer('excelId')->nullable()->index();
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
        Schema::dropIfExists('website_book_links_defects');
    }
}
