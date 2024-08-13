<?php

use Morilog\Jalali\Jalalian;

if (!function_exists('convertToSolarHijriYear')) {
    function convertToSolarHijriYear($dateString)
    {
        return Jalalian::fromCarbon(\Carbon\Carbon::parse($dateString))->getYear();
    }
}

if (!function_exists('getDateNow')){
    function getDateNow()
    {
        return Jalalian::now()->format('Y-m-d');
    }
}
