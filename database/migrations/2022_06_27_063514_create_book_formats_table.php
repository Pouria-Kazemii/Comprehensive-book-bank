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
        $covers = array(
            array(
                'name' => 'شومیز',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'گالینگور',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'سلفون',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'کارتی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'کاغذی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'مقوایی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'گلاسه',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'زرکوب',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'شومیز خارجی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'پلاستیکی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'سلفون خارجی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'چوبی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'پارچه ای',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            )
        );
        DB::table('book_formats')->insert($covers);
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
