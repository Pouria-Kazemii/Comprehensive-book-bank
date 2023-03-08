<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddXisbn3AndXpageurl2AndXregUseridToBookirBookTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookir_book', function (Blueprint $table) {
            $table->string('xisbn3',20)->nullable()->index()->after('xisbn2');
            $table->string('xpageurl2',100)->collation('utf8_persian_ci')->nullable()->index()->after('xpageurl');
            $table->bigInteger('xreg_userid')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookir_book', function (Blueprint $table) {
            $table->dropColumn('xisbn3');
            $table->dropColumn('xpageurl2');
            $table->dropColumn('xreg_userid');
        });
    }
}
