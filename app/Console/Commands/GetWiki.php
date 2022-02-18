<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Wikipedia;

class GetWiki extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:wiki {phrase}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will get data from wiki';

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
        //$page = (new Wikipedia)->page($this->argument('phrase'));
        $page = (new Wikipedia('fa'))->page('رضا امیرخانی');
        if($page->isSuccess()){
            $sections = $page->getSections();
            foreach($sections as $sec){
                echo $sec->getTitle();
                echo "\n";
                echo $sec->getBody();
                echo "\n";
                if($sec->hasImages()){
                    echo "Image : ";
                    $images = $sec->getImages();
                    foreach($images as $img){
                        echo $img->getUrl();
                    }
                }
                echo "\n";
                echo "---------------------------";
                echo "\n";

            }
            // if($page->getMainImage()){
            //     echo "\n";
            //     echo $sec->getMainImage();
            //     echo "\n";
            // }
        }else{
            echo "\n";
            echo "---------------------------";
            echo "\n";
            echo "         FAILED";
            echo "\n";
            echo "---------------------------";
            echo "\n";
        }
    }
}
