<?php
    $specialFaArChar = array(
                        'أ' => 'ا',
                        'إ' => 'ا',
                        'ك' => 'ک',
                        'ؤ' => 'و',
                        'ة' => 'ه',
                        'ۀ' => 'ه',
                        'ي' => 'ی',
                        '۰' => '۰',
                        '۰' => '٠',
                        '۱' => '۱',
                        '۱' => '١',
                        '۲' => '۲',
                        '۲' => '٢',
                        '۳' => '۳',
                        '۳' => '٣',
                        '۴' => '۴',
                        '۴' => '٤',
                        '۵' => '۵',
                        '۵' => '٥',
                        '۶' => '۶',
                        '۶' => '٦',
                        '۷' => '۷',
                        '۷' => '٧',
                        '۸' => '۸',
                        '۸' => '٨',
                        '۹' => '۹',
                        '۹' => '٩',
                        ';' => '؛',
                        '?' => '؟',
                        ',' => '،'
                );
    $specialEnFaNumChar = array(
                        '0' => '۰',
                        '1' => '۱',
                        '2' => '۲',
                        '3' => '۳',
                        '4' => '۴',
                        '5' => '۵',
                        '6' => '۶',
                        '7' => '۷',
                        '8' => '۸',
                        '9' => '۹',
                        ';' => '؛',
                        '?' => '؟',
                        ',' => '،'
                );


if (!function_exists('arCharToFA')) {

    /**
     * Change Ar  String to FA
     *
     * @param string $arStr
     * @return string $faStr
     */
    function arCharToFA($arStr)
    {
        global $specialFaArChar;
        foreach($specialFaArChar as $fa=>$ar){
            $arStr = str_replace($ar, $fa, $arStr);
        }
        return $arStr;
    }
}
if (!function_exists('faCharToEN')) {

    /**
     * Change Fa String to EN
     *
     * @param string $faStr
     * @return string $enStr
     */
    function faCharToEN($faStr)
    {
        global $specialEnFaNumChar;
        $faStr = arCharToFA($faStr);
        foreach($specialEnFaNumChar as $en=>$fa){
            $faStr = str_replace($fa, $en, $faStr);
        }
        return $faStr;
    }
}
