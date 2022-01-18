<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Addindextotables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        DB::statement('ALTER TABLE `books` ADD FULLTEXT INDEX book_shabak_index (shabak)');
        DB::statement('ALTER TABLE `books` ADD FULLTEXT INDEX book_title_index (Title)');
        DB::statement('ALTER TABLE `books` ADD FULLTEXT INDEX book_nasher_index (Nasher)');
        
        DB::statement('ALTER TABLE `bookK24` ADD FULLTEXT INDEX book_shabak_index (shabak)');
        DB::statement('ALTER TABLE `bookK24` ADD FULLTEXT INDEX book_title_index (title)');
        DB::statement('ALTER TABLE `bookK24` ADD FULLTEXT INDEX book_nasher_index (nasher)');

        DB::statement('ALTER TABLE `book30book` ADD FULLTEXT INDEX book_shabak_index (shabak)');
        DB::statement('ALTER TABLE `book30book` ADD FULLTEXT INDEX book_title_index (title)');
        DB::statement('ALTER TABLE `book30book` ADD FULLTEXT INDEX book_nasher_index (nasher)');

        DB::statement('ALTER TABLE `bookDigi` ADD FULLTEXT INDEX book_shabak_index (shabak)');
        DB::statement('ALTER TABLE `bookDigi` ADD FULLTEXT INDEX book_title_index (title)');
        DB::statement('ALTER TABLE `bookDigi` ADD FULLTEXT INDEX book_nasher_index (nasher)');

        DB::statement('ALTER TABLE `bookgisoom` ADD FULLTEXT INDEX book_shabak_index (shabak10)');
        DB::statement('ALTER TABLE `bookgisoom` ADD FULLTEXT INDEX book_shabak13_index (shabak13)');
        DB::statement('ALTER TABLE `bookgisoom` ADD FULLTEXT INDEX book_title_index (title)');
        DB::statement('ALTER TABLE `bookgisoom` ADD FULLTEXT INDEX book_nasher_index (nasher)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('books', function(Blueprint $table)
        {
            $table->dropIndex('shabak');
            $table->dropIndex('Title');
            $table->dropIndex('Nasher');
        });
        Schema::table('bookK24', function(Blueprint $table)
        {
            $table->dropIndex('title');
            $table->dropIndex('nasher');
            $table->dropIndex('shabak');
        });
        Schema::table('book30book', function(Blueprint $table)
        {
            $table->dropIndex('title');
            $table->dropIndex('nasher');
            $table->dropIndex('shabak');
        });
        Schema::table('bookDigi', function(Blueprint $table)
        {
            $table->dropIndex('title');
            $table->dropIndex('nasher');
            $table->dropIndex('shabak');
        });
        Schema::table('bookgisoom', function(Blueprint $table)
        {
            $table->dropIndex('title');
            $table->dropIndex('nasher');
            $table->dropIndex('shabak10');
            $table->dropIndex('shabak13');
        });
    }
}
