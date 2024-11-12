<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTextIndexToBookirSubjectCollation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->table('bookir_subjects', function (Blueprint $collection) {
            $collection->index(['xsubject_name' => 'text'] , 'subject_text_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')->table('bookir_subjects', function (Blueprint $collection) {
            $collection->dropIndex('subject_text_index');
        });
    }
}
