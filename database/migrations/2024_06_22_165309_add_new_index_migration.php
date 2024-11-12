<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewIndexMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->table('bookir_books' , function (Blueprint $collection) {
            $collection->index('partners.xcreatorname','partner_name');
            $collection->index('partners.xcreator_id', 'partner_id');
            $collection->index('partners.xrule' , 'partner_rule');
            $collection->index('xprintnumber' , 'printnumber');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')->table('bookir_books' , function (Blueprint $collection){
            $collection->dropIndex('partner_name');
            $collection->dropIndex('partner_id');
            $collection->dropIndex('partner_rule');
            $collection->dropIndex('printnumber');
        });
    }
}
