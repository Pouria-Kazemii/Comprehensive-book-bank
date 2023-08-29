<?php

namespace App\Http\Controllers;

class TestController extends Controller
{
    public function test_majma_api(){
        $timeout = 120;
        $url = 'https://core.ketab.ir/api/Majma/get-books/?MaxResultCount=200&SkipCount=0&From=2023-08-18&To=2023-08-20';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'X-XSRF-TOKEN:CfDJ8EBsdGBOL-pNnWd01gxikhJ6Wv4aqwU6KelpdK1yI3JPZMM1XvMLmCOHSQrsxJa3qHgRQrxuMkqxADW6n9_-JsDSoF5oFPb8vD8sbW7Hw1b5hWq-Lbv_IXjhb3838RYQmZSWq56CGOCnoy4N4oufGNg',
        ));
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.google.com/');
        $content = curl_exec($ch);
        var_dump($content);
    }
}