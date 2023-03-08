<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddXpageurl2ToBookirPublisherTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookir_publisher', function (Blueprint $table) {
            $table->string('xpageurl2',100)->collation('utf8_persian_ci')->nullable()->index()->after('xpageurl');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookir_publisher', function (Blueprint $table) {
            $table->dropColumn('xpageurl2');
        });
    }
}
