<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPartnerMasterIdToBookiranketabPartnerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookiranketab_partner', function (Blueprint $table) {
            $table->integer('partner_master_id')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookiranketab_partner', function (Blueprint $table) {
            $table->dropColumn('partner_master_id');
        });
    }
}
