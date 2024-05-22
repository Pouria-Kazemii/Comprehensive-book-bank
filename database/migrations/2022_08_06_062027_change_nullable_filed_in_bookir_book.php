<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeNullableFiledInBookirBook extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookir_book', function (Blueprint $table) {
            $table->dropIndex('bookir_book_xdiocode_index');
            $table->string('xdiocode', 30)->collation('utf8_persian_ci')->index()->nullable()->change();

            $table->string('xformat', 30)->collation('utf8_persian_ci')->nullable()->change();
            $table->string('xcover', 30)->collation('utf8_persian_ci')->nullable()->change();
            $table->string('xpublishplace', 50)->collation('utf8_persian_ci')->nullable()->change();
            $table->string('xisbn', 30)->collation('utf8_persian_ci')->nullable()->change();

            $table->dropIndex('bookir_book_xisbn2_index');
            $table->string('xisbn2', 20)->collation('utf8_persian_ci')->index()->nullable()->change();

            $table->integer('xcirculation')->nullable()->change();
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
            $table->string('xdiocode', 30)->collation('utf8_persian_ci')->index()->nullable(false)->change();
            $table->string('xformat', 30)->collation('utf8_persian_ci')->nullable(false)->change();
            $table->string('xcover', 30)->collation('utf8_persian_ci')->nullable(false)->change();
            $table->string('xpublishplace', 50)->collation('utf8_persian_ci')->nullable(false)->change();
            $table->string('xisbn', 30)->collation('utf8_persian_ci')->nullable(false)->change();
            $table->string('xisbn2', 20)->collation('utf8_persian_ci')->index()->nullable(false)->change();
            $table->integer('xcirculation')->nullable(false)->change();
        });
    }
}
