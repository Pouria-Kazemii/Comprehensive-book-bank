<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblBookMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_book_master', function (Blueprint $table)
        {
            $table->id();
            $table->integer('record_number')->index();
            $table->longText('shabak')->nullable();
            $table->longText('title')->nullable();
            $table->longText('title_en')->nullable();
            $table->longText('publisher')->nullable();
            $table->longText('author')->nullable();
            $table->longText('translator')->nullable();
            $table->longText('language')->nullable();
            $table->longText('category')->nullable();
            $table->integer('weight')->nullable()->default(0);
            $table->longText('book_cover_type')->nullable();
            $table->longText('paper_type')->nullable();
            $table->longText('type_printing')->nullable();
            $table->longText('editor')->nullable();
            $table->integer('first_year_publication')->nullable()->default(0);
            $table->integer('last_year_publication')->nullable()->default(0);
            $table->integer('count_pages')->nullable()->default(0);
            $table->longText('book_size')->nullable();
            $table->integer('print_period_count')->nullable()->default(0);
            $table->integer('print_count')->nullable()->default(0);
            $table->longText('print_location')->nullable();
            $table->tinyInteger('translation')->nullable()->default(0);
            $table->binary('desc')->nullable();
            $table->longText('image')->nullable();
            $table->integer('price')->nullable()->default(0);
            $table->longText('dio_code')->nullable();
            $table->timestamps();

            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_book_master');
    }
}
