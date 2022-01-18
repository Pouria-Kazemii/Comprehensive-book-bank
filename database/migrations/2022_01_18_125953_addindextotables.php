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
        Schema::table('books', function(Blueprint $table)
        {
            $table->index('shabak');
            $table->index('Title');
            $table->index('Nasher');
        });
        Schema::table('bookK24', function(Blueprint $table)
        {
            $table->index('title');
            $table->index('nasher');
            $table->index('shabak');
        });
        Schema::table('book30book', function(Blueprint $table)
        {
            $table->index('title');
            $table->index('nasher');
            $table->index('shabak');
        });
        Schema::table('bookDigi', function(Blueprint $table)
        {
            $table->index('title');
            $table->index('nasher');
            $table->index('shabak');
        });
        Schema::table('bookgisoom', function(Blueprint $table)
        {
            $table->index('title');
            $table->index('nasher');
            $table->index('shabak10');
            $table->index('shabak13');
        });
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
