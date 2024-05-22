<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTitleEnToBookFidiboTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookFidibo', function (Blueprint $table) {
            $table->string('title_en',255)->collation('utf8_persian_ci')->nullable()->index()->after('title');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookFidibo', function (Blueprint $table) {
            $table->dropColumn('title_en');
        });
    }
}
