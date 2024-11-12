<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiteCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('site_categories', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('domain',255)->collation('utf8_persian_ci')->nullable();
            $table->string('cat_link',255)->collation('utf8_persian_ci')->nullable();
            $table->string('cat_name',255)->collation('utf8_persian_ci')->nullable();
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
        Schema::dropIfExists('site_categories');
    }
}
