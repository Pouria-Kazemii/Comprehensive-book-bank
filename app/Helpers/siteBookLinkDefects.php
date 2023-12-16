<?php

if (!function_exists('digiRecordNumberFromBookLink')) {
    function digiRecordNumberFromBookLink($row)
    {

        if (isset($row['book_links'])) {
            $book_link_array = explode("/", $row['book_links']);
            return $book_link_array[4];
        } else {
            return NULL;
        }
    }
}

if (!function_exists('vaziiat_dar_khane_ketab')) {
    function vaziiat_dar_khane_ketab($row)
    {
        $vaziiat_dar_khane_ketab =  (isset($row['vaziiat_dar_khane_ketab']) and !empty($row['vaziiat_dar_khane_ketab'])) ? $row['vaziiat_dar_khane_ketab'] : NULL;
        return $vaziiat_dar_khane_ketab;
    }
}

if (!function_exists('vaziiat_dar_edare_ketab')) {
    function vaziiat_dar_edare_ketab($row)
    {
        $vaziiat_dar_edare_ketab = (isset($row['vaziiat_dar_edare_ketab']) and !empty($row['vaziiat_dar_edare_ketab'])) ? $row['vaziiat_dar_edare_ketab'] : NULL;
        return $vaziiat_dar_edare_ketab;
    }
}

if (!function_exists('siteBookLinkDefects')) {
    function siteBookLinkDefects($khaneKetabStatus, $edareKetabStatus)
    {
        if ($khaneKetabStatus == 'کتاب شابک ندارد' && $edareKetabStatus == 'کتاب شابک ندارد') {
            $bugId = 1;
        } elseif ($khaneKetabStatus == 'کتاب در خانه کتاب وجود ندارد' && $edareKetabStatus == 'کتاب در اداره کتاب وجود ندارد') {
            $bugId = 2;
        } elseif ($khaneKetabStatus == 'کتاب در خانه کتاب وجود ندارد' && $edareKetabStatus == 'کتاب در اداره کتاب وجود دارد') {
            $bugId = 3;
        } elseif ($khaneKetabStatus == 'کتاب در خانه کتاب وجود دارد' && $edareKetabStatus == 'کتاب در اداره کتاب وجود ندارد') {
            $bugId = 4;
        } elseif ($khaneKetabStatus == 'کتاب در خانه کتاب وجود دارد' && $edareKetabStatus == 'کتاب در اداره کتاب وجود دارد') { //ok
            $bugId = 5;
        } elseif ($khaneKetabStatus == NULL && $edareKetabStatus == NULL) { // فیلد وضعیت در خانه کتاب و اداره کتاب ندارد
            $bugId = 6;
        } elseif ($khaneKetabStatus == 'کتاب حذف شده است' && $edareKetabStatus == 'کتاب حذف شده است') { // 
            $bugId = 7;
        } elseif ($khaneKetabStatus == 'کتاب حذف نشده است' && $edareKetabStatus == 'کتاب حذف نشده است') { // 
            $bugId = 8;
        } elseif ($khaneKetabStatus == 'کتاب غیرفعال شده است' && $edareKetabStatus == 'کتاب غیرفعال شده است') { // 
            $bugId = 9;
        } elseif ($khaneKetabStatus == 'کتاب غیرفعال نشده است' && $edareKetabStatus == 'کتاب غیرفعال نشده است') { // 
            $bugId = 10;
        } else {
            $bugId = 11;
        }
        return $bugId;
    }
}

if (!function_exists('checkStatusTitle')) {
    function checkStatusTitle($checkStatus)
    {
        if ($checkStatus == 1) {
            $checkStatusText = 'کتاب در خانه کتاب وجود دارد';
        } elseif ($checkStatus == 2) {
            $checkStatusText = 'کتاب در خانه کتاب وجود ندارد';
        } elseif ($checkStatus == 3) {
            $checkStatusText = 'جستجو نشده به دلیل محدودیت سال انتشار';
        } elseif ($checkStatus == 4) {
            $checkStatusText = 'کتاب شابک ندارد';
        } elseif ($checkStatus == 5) {
            $checkStatusText = 'کتاب غیرفعال شده است';
        } elseif ($checkStatus == 6) {
            $checkStatusText = 'کتاب غیرفعال نشده است';
        } elseif ($checkStatus == 7) {
            $checkStatusText = 'کتاب حذف شده است';
        } elseif ($checkStatus == 8) {
            $checkStatusText = 'کتاب حذف نشده است';
        }
        return (isset($checkStatusText) and !empty($checkStatusText)) ? $checkStatusText : '';

        return $checkStatusText;
    }
}

if (!function_exists('checkStatusValue')) {
    function checkStatusValue($checkStatusText)
    {
        if ($checkStatusText == 'کتاب در خانه کتاب وجود دارد') {
            $checkStatus = 1;
        } elseif ($checkStatusText == 'کتاب در خانه کتاب وجود ندارد') {
            $checkStatus = 2;
        } elseif ($checkStatusText == 'جستجو نشده به دلیل محدودیت سال انتشار') {
            $checkStatus = 3;
        } elseif ($checkStatusText == 'کتاب شابک ندارد') {
            $checkStatus = 4;
        } elseif ($checkStatusText == 'کتاب غیرفعال شده است') {
            $checkStatus = 5;
        } elseif ($checkStatusText == 'کتاب غیرفعال نشده است') {
            $checkStatus = 6;
        } elseif ($checkStatusText == 'کتاب حذف شده است') {
            $checkStatus = 7;
        } elseif ($checkStatusText == 'کتاب حذف نشده است') {
            $checkStatus = 8;
        }
        return (isset($checkStatus) and !empty($checkStatus)) ? $checkStatus : 0;
    }
}
if (!function_exists('unallowedTitle')) {
    function unallowedTitle($unallowedTitle)
    {
        if ($unallowedTitle == 1) {
            $unallowedTitleText = 'کتاب در خانه کتاب وجود دارد';
        } elseif ($unallowedTitle == 2) {
            $unallowedTitleText = 'کتاب در خانه کتاب وجود ندارد';
        } elseif ($unallowedTitle == 3) {
            $unallowedTitleText = 'جستجو نشده به دلیل محدودیت سال انتشار';
        } elseif ($unallowedTitle == 4) {
            $unallowedTitleText = 'کتاب شابک ندارد';
        } elseif ($unallowedTitle == 5) {
            $unallowedTitleText = 'کتاب غیرفعال شده است';
        } elseif ($unallowedTitle == 6) {
            $unallowedTitleText = 'کتاب غیرفعال نشده است';
        } elseif ($unallowedTitle == 7) {
            $unallowedTitleText = 'کتاب حذف شده است';
        } elseif ($unallowedTitle == 8) {
            $unallowedTitleText = 'کتاب حذف نشده است';
        }

        return (isset($unallowedTitleText) and !empty($unallowedTitleText)) ? $unallowedTitleText : '';
    }
}

if (!function_exists('unallowedValue')) {
    function unallowedValue($unallowedText)
    {
        if ($unallowedText == 'کتاب در خانه کتاب وجود دارد') {
            $unallowed = 1;
        } elseif ($unallowedText == 'کتاب در خانه کتاب وجود ندارد') {
            $unallowed = 2;
        } elseif ($unallowedText == 'جستجو نشده به دلیل محدودیت سال انتشار') {
            $unallowed = 3;
        } elseif ($unallowedText == 'کتاب شابک ندارد') {
            $unallowed = 4;
        } elseif ($unallowedText == 'کتاب غیرفعال شده است') {
            $unallowed = 5;
        } elseif ($unallowedText == 'کتاب غیرفعال نشده است') {
            $unallowed = 6;
        } elseif ($unallowedText == 'کتاب حذف شده است') {
            $unallowed = 7;
        } elseif ($unallowedText == 'کتاب حذف نشده است') {
            $unallowed = 8;
        }
        return (isset($unallowed) and !empty($unallowed)) ? $unallowed : 0;
    }
}


if (!function_exists('hasPermitTitle')) {
    function hasPermitTitle($hasPermit)
    {
        if ($hasPermit == 1) {
            $hasPermitText = 'کتاب در اداره کتاب وجود دارد';
        } elseif ($hasPermit == 2) {
            $hasPermitText = 'کتاب در اداره کتاب وجود ندارد';
        } elseif ($hasPermit == 3) {
            $hasPermitText = 'جستجو نشده به دلیل محدودیت سال انتشار';
        } elseif ($hasPermit == 4) {
            $hasPermitText = 'کتاب شابک ندارد';
        } elseif ($hasPermit == 5) {
            $hasPermitText = 'کتاب غیرفعال شده است';
        } elseif ($hasPermit == 6) {
            $hasPermitText = 'کتاب غیرفعال نشده است';
        } elseif ($hasPermit == 7) {
            $hasPermitText = 'کتاب حذف شده است';
        } elseif ($hasPermit == 8) {
            $hasPermitText = 'کتاب حذف نشده است';
        }
        return (isset($hasPermitText) and !empty($hasPermitText)) ? $hasPermitText : '';
    }
}

if (!function_exists('hasPermitVlaue')) {
    function hasPermitVlaue($hasPermitText)
    {
        if ($hasPermitText == 'کتاب در اداره کتاب وجود دارد') {
            $hasPermit = 1;
        } elseif ($hasPermitText == 'کتاب در اداره کتاب وجود ندارد') {
            $hasPermit = 2;
        } elseif ($hasPermitText == 'جستجو نشده به دلیل محدودیت سال انتشار') {
            $hasPermit = 3;
        } elseif ($hasPermitText == 'کتاب شابک ندارد') {
            $hasPermit = 4;
        } elseif ($hasPermitText ==  'کتاب غیرفعال شده است') {
            $hasPermit = 5;
        } elseif ($hasPermitText ==  'کتاب غیرفعال نشده است') {
            $hasPermit = 6;
        } elseif ($hasPermitText == 'کتاب حذف شده است') {
            $hasPermit = 7;
        } elseif ($hasPermitText == 'کتاب حذف نشده است') {
            $hasPermit = 8;
        }
        return (isset($hasPermit) and !empty($hasPermit)) ? $hasPermit : 0;
    }
}
