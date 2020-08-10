<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterGisoomBook extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookgisoom', function($table) {
            $table->integer('price')->default(0);
            $table->longText('bigTitle')->nullable();
            $table->longText('nnumber')->nullable();
            $table->longText('sarshenase')->nullable();
            $table->longText('nasherDesc')->nullable();
            $table->longText('zaherDesc')->nullable();
            $table->longText('descriptions')->nullable();
            $table->longText('catText')->nullable();
            $table->longText('radeKongere')->nullable();
            $table->longText('radeDText')->nullable();
            $table->longText('relatedN')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
