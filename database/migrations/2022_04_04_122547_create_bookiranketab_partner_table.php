<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookiranketabPartnerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookiranketab_partner', function (Blueprint $table) {
            $table->id();
            $table->integer('partnerId')->index();
            $table->integer('roleId')->index();
            $table->string('partnerEnName',255)->collation('utf8_persian_ci')->nullable();
            $table->string('partnerName',255)->collation('utf8_persian_ci')->nullable();
            $table->longText('partnerDesc')->collation('utf8_persian_ci')->nullable();
            $table->string('partnerImage',255)->collation('utf8_persian_ci')->nullable();
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
        Schema::dropIfExists('bookiranketab_partner');
    }
}
