<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BookController extends Controller
{
    // list books
    public function find()
    {
        $currentPageNumber = $_GET["currentPageNumber"];


        $data =
            [
                ["title" => "البهجه المرضیه فی شرح الالفیه", "nasher" => "وفا", "lang" => "فارسی", "cats" => "نمایشنامه فارسی - قرن 14 ", "saleNashr" => "1391", "nobatChap" => "1", "tedadSafe" => "32", "ghatechap" => "وزیری", "shabak" => "999292593492750", "price" => "260000"],
                ["title" => "`اباذر` نمایشنامه", "nasher" => "نگاه", "lang" => "فارسی", "cats" => "آب - تجزیه و آزمایش", "saleNashr" => "1390", "nobatChap" => "2", "tedadSafe" => "252", "ghatechap" => "رقعی", "shabak" => "99928968282620", "price" => "12000"],
                ["title" => "آب چرا خیس می کند؟", "nasher" => "بی نا", "lang" => "فارسی", "cats" => "لوله کشی آب - راهنمای آموزشی", "saleNashr" => "1393", "nobatChap" => "3", "tedadSafe" => "120", "ghatechap" => "پالتویی", "shabak" => "99988546220", "price" => "354000"],
                ["title" => "آب حیات: جهاد و شهادت", "nasher" => "خوارزمی", "lang" => "فارسی", "cats" => "آب - تجزیه و آزمایش", "saleNashr" => "1392", "nobatChap" => "5", "tedadSafe" => "50", "ghatechap" => "وزیری", "shabak" => "99941662970", "price" => "23000"],
                ["title" => "آبرسانی شهری", "nasher" => "بی نا", "lang" => "فارسی", "cats" => "آب - تجزیه و آزمایش", "saleNashr" => "1394", "nobatChap" => "2", "tedadSafe" => "75", "ghatechap" => "جیبی", "shabak" => "99910646270", "price" => "75000"],
                ["title" => "آبرسانی و تاسیسات بهداشتی روستایی", "nasher" => "پیام", "lang" => "فارسی", "cats" => "آب - تجزیه و آزمایش", "saleNashr" => "1395", "nobatChap" => "2", "tedadSafe" => "150", "ghatechap" => "رقعی", "shabak" => "9992880526961070", "price" => "65000"],
                ["title" => "آبروی از دست رفته کاتریف بلوم", "nasher" => "پیام", "lang" => "فارسی", "cats" => "آب - تجزیه و آزمایش", "saleNashr" => "1399", "nobatChap" => "4", "tedadSafe" => "60", "ghatechap" => "پالتویی", "shabak" => "99910218339140", "price" => "12000"],
                ["title" => "آبستنی و زایمان", "nasher" => "موسسه چاپ و انتشارات دانشگاه تهران", "lang" => "فارسی", "cats" => "بارداری", "saleNashr" => "1398", "nobatChap" => "4", "tedadSafe" => "100", "ghatechap" => "پالتویی", "shabak" => "99918061820", "price" => "72000"],
                ["title" => "آبشار", "nasher" => "مولف", "lang" => "فارسی", "cats" => "آب شناسی", "saleNashr" => "1398", "nobatChap" => "1", "tedadSafe" => "120", "ghatechap" => "رقعی", "shabak" => "99918061820", "price" => "563000"],
                ["title" => "آب شناسی", "nasher" => "نیما", "lang" => "فارسی", "cats" => "آب شناسی", "saleNashr" => "1392", "nobatChap" => "1", "tedadSafe" => "350", "ghatechap" => "وزیری", "shabak" => "9992898982970", "price" => "13000"],
                ["title" => "آبشوران", "nasher" => "دانشگاه تبریز", "lang" => "فارسی", "cats" => "آب شناسی", "saleNashr" => "1396", "nobatChap" => "2", "tedadSafe" => "376", "ghatechap" => "رقعی", "shabak" => "9991064551480", "price" => "53000"],
                ["title" => "المهذب البارع فی شرح المختصر النافع", "nasher" => "عمیدی", "lang" => "فارسی", "cats" => "زبان عربی - معانی و بیان", "saleNashr" => "1391", "nobatChap" => "3", "tedadSafe" => "200", "ghatechap" => "رقعی", "shabak" => "99995061892390", "price" => "635000"],
                ["title" => "علم برای نوجوانان، شاخت طبیعت: آب، موادمعدنی، مینرالها، خاک", "nasher" => "عمیدی", "lang" => "فارسی", "cats" => "زبان عربی - معانی و بیان", "saleNashr" => "1394", "nobatChap" => "2", "tedadSafe" => "120", "ghatechap" => "وزیری", "shabak" => "99910646314320", "price" => "42000"],
                ["title" => "آب و الکترولیتها", "nasher" => "مرکز نشر دانشگاهی", "lang" => "فارسی", "cats" => "لوله کشی", "saleNashr" => "1396", "nobatChap" => "1", "tedadSafe" => "110", "ghatechap" => "جیبی", "shabak" => "9994438663140", "price" => "75000"],
                ["title" => "آب و خاک", "nasher" => "موسسه چاپ و انتشارات دانشگاه تهران", "lang" => "فارسی", "cats" => "آب شناسی", "saleNashr" => "1392", "nobatChap" => "1", "tedadSafe" => "80", "ghatechap" => "وزیری", "shabak" => "99981269610", "price" => "235000"],
            ];

        return response()->json
        (
            [
                "status" => 200,
                "message" => "ok",
                "data" => ["list" => $data, "currentPageNumber" => $currentPageNumber, "totalPages" => 20, "pageRows" => 15, "totalRows" => 300]
            ],
            200
        );

//        $resultCount = count($resultArray);
//        if($resultCount == 0){
//            return response()->json(['error'=>'NOT FOUND','error_code'=>'2001','result_count'=>0 , 'result'=>''], 404);
//        }else{
//            return response()->json(['error'=>'','result_count'=>$resultCount ,'results'=>$resultArray]);
//        }
    }
}
