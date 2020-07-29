<?php
if (!function_exists('arCharToFA')) {

    /**
     * Change Ar  String to FA
     *
     * @param string $arStr
     * @return string $faStr
     */
    function arCharToFA($arStr)
    {
        $specialFaArChar = array(
                'ا' => 'أ',
                'ا' => 'إ',
                'ک' => 'ك',
                'و' => 'ؤ',
                'ه' => 'ة',
                'ه' => 'ۀ',
                'ی' => 'ي',
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
        foreach($specialFaArChar as $fa => $ar){
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
        $faStr = arCharToFA($faStr);
        foreach($specialEnFaNumChar as $en => $fa){
            $faStr = str_replace($fa, $en, $faStr);
        }
        return $faStr;
    }
}
if (!function_exists('cleanFaAlphabet')) {

    /**
     * Clean Fa Alphabet and character
     *
     * @param string $faStr
     * @return string $cleanedStr
     */
    function cleanFaAlphabet($faStr)
    {
        $faAlphabet = explode(' ', 'ا ب پ ت ث ج چ ح خ د ذ ر ز ژ س ش ص ض ط ظ ع غ ف ق ک گ ل م ن و ه ی ئ ء [ ] { } . , ; . ،  : |');
        $faStr = arCharToFA($faStr);
        foreach($faAlphabet as $fa){
            $faStr = str_replace($fa, "", $faStr);
        }
        $faStr=preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u','',trim($faStr));
        return $faStr;
    }
}
if (!function_exists('faAlphabetKeep')) {

    /**
     * Keep fa alphabet only
     *
     * @param string $faStr
     * @return string $cleanedStr
     */
    function faAlphabetKeep($faStr)
    {
        $faAlphabet = explode(' ', 'ا ب پ ت ث ج چ ح خ د ذ ر ز ژ س ش ص ض ط ظ ع غ ف ق ک گ ل م ن و ه ی ئ ء');
        $faStr = arCharToFA($faStr);
        $pointer = 0;
        while(isset($faStr[$pointer])){
            $char=mb_substr($faStr, $pointer++, 1, 'UTF-8');
            if(in_array($char, $faAlphabet) === FALSE){
                $faStr = str_replace($char, " ", $faStr);
            }
        }

        return trim($faStr);
    }
}
if (!function_exists('enNumberKeepOnly')) {

    /**
     * Keep fa alphabet only
     *
     * @param string $numallStr
     * @return string $cleanedStr
     */
    function enNumberKeepOnly($numallStr)
    {
        return trim(preg_replace("/[^0-9]/", "",$numallStr));
    }
}
