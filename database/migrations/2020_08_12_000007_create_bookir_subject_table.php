<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookirSubjectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookir_subject', function (Blueprint $table) {
            $table->bigIncrements('xid')->unsigned();
            $table->integer('xparentid')->unsigned();
            $table->string('xsubject', 80)->collation('utf8_persian_ci')->index();
            $table->integer('xregdate')->unsigned();
            $table->tinyInteger('xhaschild')->unsigned();
            $table->string('xsubjectname2', 100)->collation('utf8_persian_ci');
            $table->tinyInteger('xisname')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bookir_subject');
    }
}
