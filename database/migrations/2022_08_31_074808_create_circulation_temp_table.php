<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCirculationTempTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('circulation_temp', function (Blueprint $table) {
            $table->bigIncrements('xid')->unsigned();
            $table->bigInteger('xyear')->unsigned()->index();
            $table->bigInteger('xbook_id')->unsigned()->nullable()->index();
            $table->bigInteger('xpublisher_id')->unsigned()->nullable()->index();
            $table->bigInteger('xauthor_id')->unsigned()->nullable()->index();
            $table->bigInteger('xbooks_count')->unsigned()->nullable()->index()->comment('تعداد کتاب ها');
            $table->bigInteger('xfirst_edition_books_count')->unsigned()->nullable()->index()->comment('تعداد کتب های چاپ اول');
            $table->bigInteger('xcirculations_count')->unsigned()->nullable()->index()->comment('تعداد تیراژ ها');
            $table->bigInteger('xfirst_edition_circulations_count')->unsigned()->nullable()->index()->comment('تعداد تیراژ های چاپ اول');
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
        Schema::dropIfExists('circulation_temp');
    }
}
