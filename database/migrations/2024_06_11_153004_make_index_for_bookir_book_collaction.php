<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Jenssegers\Mongodb\Schema\Blueprint;

class MakeIndexForBookirBookCollaction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//        Schema::connection('mongodb')->table('bookir_books', function (Blueprint $collection) {
//            $collection->index('xname', 'text');
//            $collection->index('publisher.xpublishername', 'publisher_text');
//            $collection->index('creators.xcreatorname', 'creator_text');
//            $collection->index('subjects.xsubject_name', 'subject_text');
//            $collection->index('publisher.xpublishername', );
//            $collection->index('xisbn');
//            $collection->index('xformat');
//            $collection->index('xdiocode');
//        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
//        Schema::connection('mongodb')->table('bookir_books', function (Blueprint $collection) {
//            $collection->dropIndex('xname_text');
//            $collection->dropIndex('xpublisher_name_text');
//            $collection->dropIndex('xisbn');
//            $collection->dropIndex('xformat');
//            $collection->dropIndex('xdescription_text');
//            $collection->dropIndex('xdiocode');
//        });
    }
}
