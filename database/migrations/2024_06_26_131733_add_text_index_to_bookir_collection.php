<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddTextIndexToBookirCollection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Define the collection name
        $collection = 'bookir_books';

        // Drop the existing text index if it exists
        DB::connection('mongodb')->collection($collection)->raw(function($collection) {
            $indexes = $collection->listIndexes();
            foreach ($indexes as $index) {
                if (isset($index['key']['_fts']) && $index['key']['_fts'] == 'text') {
                    $collection->dropIndex($index['name']);
                }
            }
        });

        // Create a composite text index on xname, xdescription, and subjects.xsubject_name
        DB::connection('mongodb')->collection($collection)->raw(function($collection) {
            $collection->createIndex(
                [
                    'xname' => 'text',
                    'publisher.xpublishername'=> 'text',
                    'partners.xcreatorname'=> 'text',
                    'subjects.xsubject_name'=> 'text',
                ],
                [
                    'name' => 'books_text_index', // Optional: Name your index
                ]
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookir_collection', function (Blueprint $table) {
            //
        });
    }
}
