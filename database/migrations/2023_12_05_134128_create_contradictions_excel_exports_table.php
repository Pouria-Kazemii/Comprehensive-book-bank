<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContradictionsExcelExportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_contradictions_excel_exports', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('title',255)->collation('utf8_persian_ci')->nullable();
            $table->text('path')->collation('utf8_persian_ci')->nullable();
            $table->string('ReferenceDate',255)->collation('utf8_persian_ci')->nullable();
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
        Schema::dropIfExists('tbl_contradictions_excel_exports');
    }
}
