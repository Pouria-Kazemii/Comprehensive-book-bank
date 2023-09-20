<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubjectTagAgeGroupToBookDigiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookDigi', function (Blueprint $table) {
            $table->longText('subject')->nullable()->collation('utf8_persian_ci')->after('tedadSafe');
            $table->longText('tag')->nullable()->collation('utf8_persian_ci')->after('subject');
            $table->longText('ageGroup')->nullable()->collation('utf8_persian_ci')->after('tag');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookDigi', function (Blueprint $table) {
            $table->dropColumn('subject');
            $table->dropColumn('tag');
            $table->dropColumn('ageGroup');
        });
    }
}
