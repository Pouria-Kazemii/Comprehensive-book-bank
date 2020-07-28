<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FindBookNlai extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
                $host= "z3950.nlai.ir:210";

                $z = yaz_connect($host);

                $isbn = $_GET['isbn'];
                yaz_search($z, 'rpn', '@attr 1=7 "' .$isbn. '"');

                yaz_wait();
                $error = yaz_error($z);
                if (!empty($error)) {
                    echo "Error: $error";
                } else {
                    $hits = yaz_hits($z);

                    if ($hits == 0){
                        $export = NULL;
                        header('Content-Type: application/json');
                        die(json_encode($export));
                    }


                    for ($p = 1; $p <= 1; $p++) {
                        $rec = yaz_record($z, $p, "string");
                        if (empty($rec)) continue;


                        $list = explode("\n", $rec);
                    }
                }

                print_r($list);exit;

    }
}
