<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShahreKetabOnlineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_book_shahre_ketab_online', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->integer('recordNumber')->index();
            $table->integer('parentId')->default(0)->index();
            $table->string('title',255)->collation('utf8_persian_ci')->nullable();
            $table->string('nasher',255)->collation('utf8_persian_ci')->nullable()->index();
            $table->string('saleNashr',20)->collation('utf8_persian_ci')->nullable();
            $table->integer('tedadSafe')->default(0)->index();
            $table->string('shabak',255)->collation('utf8_persian_ci')->nullable()->index();
            $table->tinyInteger('translate')->default(0)->index();
            $table->binary('desc')->nullable();
            $table->string('images',255)->collation('utf8_persian_ci')->nullable();
            $table->integer('price')->default(0);
            $table->string('lang',255)->collation('utf8_persian_ci')->nullable();
            $table->string('jeld',255)->collation('utf8_persian_ci')->nullable();
            $table->string('ghateChap',255)->collation('utf8_persian_ci')->nullable();
            $table->string('nobatChap',255)->collation('utf8_persian_ci')->nullable();
            $table->string('length',255)->collation('utf8_persian_ci')->nullable();
            $table->string('height',255)->collation('utf8_persian_ci')->nullable();
            $table->string('width',255)->collation('utf8_persian_ci')->nullable();
            $table->string('vazn',255)->collation('utf8_persian_ci')->nullable();
            $table->longText('partnerArray')->collation('utf8_persian_ci')->nullable();
            $table->longText('cats')->collation('utf8_persian_ci')->nullable();
            $table->text('tags')->collation('utf8_persian_ci')->nullable();
            $table->string('subject',255)->collation('utf8_persian_ci')->nullable();
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
        Schema::dropIfExists('tbl_book_shahre_ketab_online');
    }
}
