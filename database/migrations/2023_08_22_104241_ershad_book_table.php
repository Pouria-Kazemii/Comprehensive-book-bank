<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ErshadBookTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ershad_book', function (Blueprint $table) {
            $table->bigIncrements('xid')->unsigned();
            $table->string('xtitle_fa',255)->collation('utf8_persian_ci')->nullable()->index();
            $table->string('xtitle_en',255)->collation('utf8_persian_ci')->nullable()->index();
            $table->string('xtype',255)->collation('utf8_persian_ci')->nullable()->index();
            $table->string('xrade',255)->collation('utf8_persian_ci')->nullable()->index();
            $table->string('xpublisher_name',255)->collation('utf8_persian_ci')->nullable()->index();
            $table->string('xlang',255)->collation('utf8_persian_ci')->nullable()->index();
            $table->string('xisbn',255)->collation('utf8_persian_ci')->nullable()->index();
            $table->string('xpage_number',255)->collation('utf8_persian_ci')->nullable();
            $table->text('xmoalefin')->collation('utf8_persian_ci')->nullable();
            $table->text('xmotarjemin')->collation('utf8_persian_ci')->nullable();
            $table->text('xdesc')->collation('utf8_persian_ci')->nullable();
            $table->string('xformat',255)->collation('utf8_persian_ci')->nullable();
            $table->text('xgerdavarande')->collation('utf8_persian_ci')->nullable();
            $table->text('xpadidavarande')->collation('utf8_persian_ci')->nullable();
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
        Schema::dropIfExists('ershad_book');
    }
}
