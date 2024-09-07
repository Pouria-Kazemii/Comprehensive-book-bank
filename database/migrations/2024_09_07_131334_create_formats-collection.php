<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateFormatsCollection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('mongodb')->collection('formats')->insert([
            [
                'en_title' => 'vaziri_paragraph',
                'fa_title' => 'وزیری'
            ],
            [
                'en_title' => 'roghi_paragraph',
                'fa_title' => 'رقعی'
            ],
            [
                'en_title' => 'jibi_paragraph',
                'fa_title' => 'جیبی'
            ],
            [
                'en_title' => 'paltoe_paragraph',
                'fa_title' => 'پالتویی'
            ],
            [
                'en_title' => 'kheshti_paragraph',
                'fa_title' => 'خشتی'
            ],
            [
                'en_title' => 'rahli_paragraph',
                'fa_title' => 'رحلی'
            ],
            [
                'en_title' => 'bayazi_paragraph',
                'fa_title' => 'بیاضی'
            ],
            [
                'en_title' => 'janamzi_paragraph',
                'fa_title' => 'جانمازی'
            ],
            [
                'en_title' => 'soltani_paragraph',
                'fa_title' => 'سلطانی'
            ],
            [
                'en_title' => 'robi_paragraph',
                'fa_title' => 'ربعی'
            ],
            [
                'en_title' => 'jibi_paltoe_paragraph',
                'fa_title' => 'جیبی پالتویی'
            ],
            [
                'en_title' => 'rahli_kochak_paragraph',
                'fa_title' => 'رحلی کوچک'
            ],
            [
                'en_title' => 'jibi_yek_dovom_paragraph',
                'fa_title' => '1/2 جیبی'
            ],
            [
                'en_title' => 'jibi_yek_chaharom_paragraph',
                'fa_title' => '1/4 جیبی'
            ],
            [
                'en_title' => 'roghi_paltoe_paragraph',
                'fa_title' => 'رقعی پالتویی'
            ],
            [
                'en_title' => 'albumi_paragraph',
                'fa_title' => 'آلبومی'
            ],
            [
                'en_title' => 'baghali_paragraph',
                'fa_title' => 'بغلی'
            ],
            [
                'en_title' => 'jabe_paragraph',
                'fa_title' => 'جعبه ای'
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
