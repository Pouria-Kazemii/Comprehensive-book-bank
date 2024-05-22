<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCheckStatusAndHasPermitInWebsiteBookLinksDefectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_website_book_links_defects', function (Blueprint $table) {
            $table->integer('old_check_status')->default(0)->index()->after('bugId');
            $table->integer('old_has_permit')->default(0)->index()->after('old_check_status');
            $table->integer('new_check_status')->default(0)->index()->after('crawlerStatus');
            $table->integer('new_has_permit')->default(0)->index()->after('new_check_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(' tbl_website_book_links_defects', function (Blueprint $table) {
            //
        });
    }
}
