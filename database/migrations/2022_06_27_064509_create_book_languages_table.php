<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('book_languages', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('name', 45)->collation('utf8_persian_ci');
            $table->timestamps();
        });
        $covers = array(
            array(
                'name' => 'فارسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'ترکی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'عربی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'انگلیسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'لاتین',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'لری',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'کرمانجی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'لکی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'گیلکی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'پهلوی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'تالشی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'مازندرانی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'اوستایی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/انگلیسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'انگلیسی/فارسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/ترکی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'ترکی/فارسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/عربی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'عربی/فارسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'ترکمنی/فارسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/گیلکی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/عربی/روسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'فارسی/انگلیسی/روسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ), array(
                'name' => 'فارسی/انگلیسی/عربی/فرانسه/اسپانیایی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/ترکی/انگلیسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'انگلیسی/کردی/فارسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'انگلیسی/فارسی/ترکی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'انگلیسی/روسی/فارسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/آشوری',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/اردو',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'کردی/ترکی/فارسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'ژاپنی/انگلیسی/فارسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'سیستانی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/ویتنامی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'کرمانجی/فارسی/ترکی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'بلوچی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/عربی/کردی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'فارسی/انگلیسی/عربی/اردو',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'فارسی/انگلیسی/آلمانی/فرانسه/عربی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'کردی/فارسی/انگلیسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'فارسی/سمنانی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/قزاقی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/تاتی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/انگلیسی/اسپانیایی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/انگلیسی/گرجی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/لکی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'انگلیسی/فارسی/گیلکی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فنلاندی/فارسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/انگلیسی/چینی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/چکسلواکی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/یونانی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/سومری',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'عربی/فارسی/انگلیسی/فرانسوی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/انگلیسی/فرانسوی/آلمانی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/انگلیسی/اسپانیایی/استانبولی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/اوکراینی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'فرانسوی/عربی/فارسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'پرتغالی/فارسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'چینی/فارسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'انگلیسی/عربی/فارسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/انگلیسی/فرانسوی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/کردی/ترکی/انگلیسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسي/انگليسي/ايتاليايي',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'انگلیسی/ترکی/عربی/فارسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'انگلیسی/ترکی/فارسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'دشتی/فارسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'فارسی/انگلیسی/آلمانی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'پالی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'رومانیایی/فارسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی / پهلوی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'هلندی/فارسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/تایلندی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/کره‌ای',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/هندی/اردو',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'فارسی/ژاپنی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'عربی/ترکمنی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/هندی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'فارسی/انگلیسی/ایتالیایی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'عربی/انگلیسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/عربی/انگلیسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'کردی/فارسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'کردی/ترکی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'عربی/فرانسوی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'کردی/انگلیسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/ایتالیایی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/نروژی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/کردی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/اسپانیایی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/زرتشتی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/آلمانی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/ارمنی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'فارسی/انگلیسی/عربی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'آلمانی/فارسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فرانسوی/فارسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'انگلیسی/ترکی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'ارمنی/فارسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/روسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/تاجیکی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'روسی/فارسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'ژاپنی/فارسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'عربی/ترکی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'عربی/کردی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'اردو',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فرانسوی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'ترکمنی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'بنگالی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'ایتالیایی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'روسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'آلمانی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'پرتغالی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'هلندی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'ژاپنی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'استانبولی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'سواحلی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'لهستانی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'چینی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'دانمارکی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'سوئدی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'چکی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'هندی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'اسلواکی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'ایرلندی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'نروژی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'بوسنیایی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'تبتی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'عبری',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'سوئیسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'کره ای',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'مجارستانی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'آلبانیایی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فنلاندی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'آذری',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'تاجیکی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'گرجستانی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'روسی/انگلیسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'رومانیایی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'یونانی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'کریل',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'ویتنامی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'اوگاندایی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'اندونزیایی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'اردو/عربی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'بلغاری',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'سورانی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'گرجستانی/انگلیسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'شیلیایی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'عربی/اردو',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'عربی/روسی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),


            array(
                'name' => 'تایلندی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),


            array(
                'name' => 'عربی/ارمنی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'روسی/عربی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'اسپانیایی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),


            array(
                'name' => 'انگلیسی/کردی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'ارمنی/عربی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),



            array(
                'name' => 'عربی/سواحلی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),


            array(
                'name' => 'فرانسوی/عربی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'پشتو',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'انگلیسی/عربی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'ترکی/عربی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'مالزیایی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'کردی/عربی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'فارسی/فرانسوی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'افغانی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'الجزایری',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'آفریقایی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'ماداگاسکاری',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'تاجیکی/عربی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),


            array(
                'name' => 'ترکی/قزاقستانی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'کردی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'انگلیسی/فرانسوی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'انگلیسی/ایتالیایی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'هوسایی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'صربی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'تامیلی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'انگلیسی/اسپانیایی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'ازبکی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),



            array(
                'name' => 'ترکمنی/فرانسوی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'کره‌ای',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),


            array(
                'name' => 'فولانی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'انگلیسی/فرانسه/ایتالیایی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'مغولی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'آشوری',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'کرمانجی/عربی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'برزیلی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'آلمانی/عربی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'تایوانی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'برمه‌ای',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'عربی/اسپانیایی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),


            array(
                'name' => 'عربی/چینی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'چیچاوا',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'کاتالان',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'لتونیایی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'عربی/سریانی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'عربی/ژاپنی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'مندایی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'سریانی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'کروات',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'اوکراینی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'پرتغالی/عربی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'سانسکریت',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'فیلیپینی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'عربی/آلمانی/انگلیسی/ترکی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'انگلیسی/اردو/عربی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),
            array(
                'name' => 'سندی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

            array(
                'name' => 'استونیایی',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ),

        );
        DB::table('book_languages')->insert($covers);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('book_languages');
    }
}
