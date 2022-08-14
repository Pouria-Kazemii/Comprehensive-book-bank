<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateBookFormatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('book_formats', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('name',30)->collation('utf8_persian_ci');
            $table->timestamps();
        });
        $formats = array(
            array(
                'name' => 'وزیری',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'رقعی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'جیبی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'پالتویی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'خشتی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'رحلی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'بیاضی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'جعبه ای',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'بغلی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'جانمازی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'سلطانی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'ربعی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'جیبی پالتویی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'رحلی کوچک',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'آلبومی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => '۱/۲ جیبی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => '۱/۴ جیبی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'رقعی پالتویی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
        );
        DB::table('book_formats')->insert($formats);
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('book_formats');
    }
}
