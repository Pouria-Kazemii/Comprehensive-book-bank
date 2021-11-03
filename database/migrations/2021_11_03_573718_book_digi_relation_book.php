<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BookDigiRelationBook extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // bookDigiRelation
        Schema::create('book_digi_book_digi_related', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('book_digi_id');
            $table->unsignedInteger('book_digi_related_id');
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
        Schema::dropIfExists('book_digi_book_digi_related');
    }
}
