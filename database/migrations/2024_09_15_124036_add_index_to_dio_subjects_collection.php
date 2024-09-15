<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToDioSubjectsCollection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->table('dio_subjects', function (Blueprint $collection) {
            $collection->index('id_by_law','xidbylaw');
            $collection->index('title' , 'xtitle');
            $collection->index('parent_id','xparentid');
            $collection->index('range.start','xrangestart');
            $collection->index('range.end','xrangeend');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')->table('dio_subjects', function (Blueprint $collection) {
            $collection->dropIndex('xidbylaw');
            $collection->dropIndex('xtitle');
            $collection->dropIndex('xparentid');
            $collection->dropIndex('xrangestart');
            $collection->dropIndex('xrangeend');
        });
    }
}
