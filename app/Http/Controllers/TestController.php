<?php

namespace App\Http\Controllers;

class TestController extends Controller
{
    public function test_majma_api(){
        $timeout = 120;
        $url = 'https://core.ketab.ir/api/Majma/get-books/?MaxResultCount=200&SkipCount=0&From=2023-08-18&To=2023-08-20';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        $content = curl_exec($ch);
        var_dump($content);

    }
    public function test_get_books_majma($from_date,$to_date,$from,$result_count){
        
        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );  

        // $url = 'https://core.ketab.ir/api/Majma/get-books/?MaxResultCount=200&SkipCount=0&From=2023-02-13&To=2023-02-15';
        $url = 'https://core.ketab.ir/api/Majma/get-books/?MaxResultCount='.$result_count.'&SkipCount='.$from.'&From='.$from_date.'&To='.$to_date;
        $response = file_get_contents($url, false, stream_context_create($arrContextOptions));
        die($response);
    }

    public function test_get_book_id_majma($book_id){
        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );  


        $url = 'https://core.ketab.ir/api/Majma/get-book/'.$book_id;
        $response = file_get_contents($url, false, stream_context_create($arrContextOptions));
        die($response);
    }

 

    public function test_get_publishers_majma($from,$result_count){
        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );  


        $url = 'https://core.ketab.ir/api/Majma/get-publishers/?MaxResultCount='.$result_count.'&SkipCount='.$from;
        $response = file_get_contents($url, false, stream_context_create($arrContextOptions));
        die($response);
    }

       public function test_get_publisher_id_majma($publisher_id){
        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );  


        $url = 'https://core.ketab.ir/api/Majma/get-publisher/'.$publisher_id;
        $response = file_get_contents($url, false, stream_context_create($arrContextOptions));
        die($response);
    }

     public function test_get_authors_majma($from,$result_count){
        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );  


        $url = 'https://core.ketab.ir/api/Majma/get-authors/?MaxResultCount='.$result_count.'&SkipCount='.$from;
        $response = file_get_contents($url, false, stream_context_create($arrContextOptions));
        die($response);
    }
}