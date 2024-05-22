<?php
if (!function_exists('convertToSolarHijriYear')) {
    function convertToSolarHijriYear($dateString)
    {
        return \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($dateString))->getYear();
    }
}
