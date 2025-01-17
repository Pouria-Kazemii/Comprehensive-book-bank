<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKetabrahTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookKetabrah', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->integer('recordNumber')->index();
            $table->integer('parentId')->default(0)->index();
            $table->string('title',255)->collation('utf8_persian_ci')->nullable();
            $table->string('nasher',255)->collation('utf8_persian_ci')->nullable()->index();
            $table->string('nasherSouti',255)->collation('utf8_persian_ci')->nullable()->index();
            $table->string('saleNashr',20)->collation('utf8_persian_ci')->nullable();
            $table->integer('tedadSafe')->default(0)->index();
            $table->string('shabak',255)->collation('utf8_persian_ci')->nullable()->index();
            $table->tinyInteger('translate')->default(0)->index();
            $table->binary('desc')->nullable();
            $table->string('images',255)->collation('utf8_persian_ci')->nullable();
            $table->integer('price')->default(0);
            $table->string('lang',255)->collation('utf8_persian_ci')->nullable();
            $table->longText('partnerArray')->collation('utf8_persian_ci')->nullable();
            $table->string('tags',255)->collation('utf8_persian_ci')->nullable();
            $table->string('cat',255)->collation('utf8_persian_ci')->nullable();
            $table->string('format',255)->collation('utf8_persian_ci')->nullable();
            $table->string('title2',255)->collation('utf8_persian_ci')->nullable();
            $table->integer('book_master_id')->default(0)->index();
            $table->integer('check_status')->default(0)->index();
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
        Schema::dropIfExists('bookKetabrah');
    }
}
