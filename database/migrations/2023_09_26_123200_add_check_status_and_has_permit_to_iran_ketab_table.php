<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCheckStatusAndHasPermitToIranKetabTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookIranketab', function (Blueprint $table) {
//            $table->integer('check_status')->default(0)->index();
//            $table->integer('has_permit')->default(0)->index();
//            $table->string('mongo_id', 255)->nullable()->index()->collation('utf8_persian_ci')->after('has_permit');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookIranketab', function (Blueprint $table) {
            //
        });
    }
}
