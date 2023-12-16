<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUnallowedInWebsiteBookLinkDefectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_website_book_links_defects', function (Blueprint $table) {
            $table->integer('old_unallowed')->default(0)->index()->after('old_has_permit');
            $table->integer('new_unallowed')->default(0)->index()->after('new_has_permit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('website_book_link_defects', function (Blueprint $table) {
            //
        });
    }
}
