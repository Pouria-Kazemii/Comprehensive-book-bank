<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookirRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookir_rules', function (Blueprint $table) {
            $table->bigIncrements('xid');
            $table->string('xrole', 100)->collation('utf8_persian_ci');
            $table->integer('xregdate')->unsigned();
            $table->tinyInteger('xisauthors');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bookir_rules');
    }
}
