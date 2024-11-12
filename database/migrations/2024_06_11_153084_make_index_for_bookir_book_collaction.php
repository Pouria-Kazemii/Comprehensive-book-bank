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
        Schema::connection('mongodb')->table('bookir_books', function (Blueprint $collection) {
            $collection->index('xname', 'text');
            $collection->index('publisher.xpublishername', 'publisher_text');
            $collection->index('creators.xcreatorname', 'creator_text');
            $collection->index('subjects.xsubject_name', 'subject_text');
            $collection->index(['xname' => 'text'] , 'xname_text');
            $collection->index('publisher.xpublisher_id' , 'publisher_id');
            $collection->index('creators.xcreator_id' , 'creator_id');
            $collection->index('creators.xrule' , 'creator_role');
            $collection->index('subjects.xsubject_id' , 'subject_id');
            $collection->index('xforamt' , 'format');
            $collection->index('xcover','cover');
            $collection->index('xprintnymber' , 'print_number');
            $collection->index('xcirculation' , 'circulation');
            $collection->index('xisbn' , 'xisbn');
            $collection->index('xisbn2' , 'xisbn2');
            $collection->index('xisbn3' , 'xisbn3');
            $collection->index('xpublishdate_shamsi' , 'publisjdate_shamsi');
            $collection->index('xcoverprice' , 'cover_price');
            $collection->index('xdiocode' , 'diocode');
            $collection->index('is_translate' , 'is_translate');
            $collection->index('xparent' , 'parent');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')->table('bookir_books', function (Blueprint $collection) {
            $collection->dropIndex('text');
            $collection->dropIndex('publisher_text');
            $collection->dropIndex('creator_text');
            $collection->dropIndex('subject_text');
            $collection->dropIndex('xname_text');
            $collection->dropIndex('publisher_id');
            $collection->dropIndex('creator_id');
            $collection->dropIndex('creator_role');
            $collection->dropIndex('subject_id');
            $collection->dropIndex('format');
            $collection->dropIndex('cover');
            $collection->dropIndex('print_number');
            $collection->dropIndex('circulation');
            $collection->dropIndex('xisbn');
            $collection->dropIndex('xisbn2');
            $collection->dropIndex('xisbn3');
            $collection->dropIndex('xpublishdate_shamsi');
            $collection->dropIndex('xcoverprice');
            $collection->dropIndex('xdiocode');
            $collection->dropIndex('is_translate');
            $collection->dropIndex('xparent');
        });
    }
}
