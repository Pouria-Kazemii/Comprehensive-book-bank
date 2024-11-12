<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddEducationalHelpToDioSubjectsCollection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('mongodb')->collection('dio_subjects')->insert([
            [
                'id_by_law' => 4,
                'title' => 'کمک آموزشی',
                'dio_typr' => 'educational_help',
                'parent_id' => 0 ,
                'has_child' => 0,
                'level' => 0,
                'range' => null,
                'except' => false ,
                'except_range' => null,
                'create_at' => now(),
                'update_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dio_subjects_collection', function (Blueprint $table) {
            //
        });
    }
}
