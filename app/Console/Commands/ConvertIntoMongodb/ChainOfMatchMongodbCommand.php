<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use Illuminate\Console\Command;

class ChainOfMatchMongodbCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'match:mongodb_chain';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create all new sql data into mongo in one command ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return Bool
     */
    public function handle()
    {
        $i = 0;
        $j = 0;
        $z = 0;
        $x = 0;
        $c = 0;

        while($i < 10){
            $matchSubjects = $this->call('match:mongodb_subjects');
            if ($matchSubjects){
                logCommandResult('match:mongodb_subjects' , true);
                break;
            }else{
                $this->error('mongodb_subject failed');
                logCommandResult('match:mongodb_subjects' , false);
                $i++;
            }
        }


        while($j < 10){
            $matchPartners = $this->call('match:mongodb_creators');
            if ($matchPartners){
                logCommandResult('match:mongodb_creators' , true);
                break;
            }else{
                $this->error('mongodb_creators failed');
                logCommandResult('match:mongodb_creators' , false);
                $j++;
            }
        }


        while($z < 10){
            $matchPublishers= $this->call('match:mongodb_publishers');
            if ($matchPublishers){
                logCommandResult('match:mongodb_publishers' , true);
                break;
            }else{
                $this->error('mongo_publishers failed');
                logCommandResult('match:mongodb_publishers' , false);
                $z++;
            }
        }


        while($x < 10){
            $matchBooks = $this->call('match:mongodb_books');
            if ($matchBooks){
                logCommandResult('match:mongodb_books' , true);
                break;
            }else{
                $this->error('mongodb_books failed');
                logCommandResult('match:mongodb_books' , false);
                $x++;
            }
        }

        while($c < 10){
            $matchBooksCount = $this->call("insert:daily_books_count",['date' => getDateNow()]);
            if ($matchBooksCount){
                logCommandResult('insert:daily_books_count' , true);
                break;
            }else{
                $this->error('mongodb_daily_books failed');
                logCommandResult('insert:daily_books_count' , false);
                $c++;
            }
        }

    return true;
    }
}
