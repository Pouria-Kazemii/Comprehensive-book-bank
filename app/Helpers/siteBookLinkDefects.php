<?php

if(!function_exists('digiRecordNumberFromBookLink'))
{
    /**
     * Change number to price format
     *
     * @param string $priceNumber
     * @return string $price
     */
    function digiRecordNumberFromBookLink($row)
    {

        if(isset($row['book_links'])){
            $book_link_array = explode("/",$row['book_links']);
            return $book_link_array[4];
        }else{
            return NULL;
        }
        
    }
}

if(!function_exists('vaziiat_dar_khane_ketab'))
{
    /**
     * Change number to price format
     *
     * @param string $priceNumber
     * @return string $price
     */
    function vaziiat_dar_khane_ketab($row)
    {
        $vaziiat_dar_khane_ketab = (isset($row['vaziiat_dar_khane_ketab']) AND !empty($row['vaziiat_dar_khane_ketab']))? : NULL;
        return $vaziiat_dar_khane_ketab;
    }
}

if(!function_exists('vaziiat_dar_edare_ketab'))
{
    /**
     * Change number to price format
     *
     * @param string $priceNumber
     * @return string $price
     */
    function vaziiat_dar_edare_ketab($row)
    {
        $vaziiat_dar_edare_ketab = (isset($row['vaziiat_dar_edare_ketab']) AND !empty($row['vaziiat_dar_edare_ketab']))? : NULL;
        return $vaziiat_dar_edare_ketab;
    }
}

if(!function_exists('siteBookLinkDefects'))
{
    /**
     * Change number to price format
     *
     * @param string $priceNumber
     * @return string $price
     */
    function siteBookLinkDefects($khaneKetabStatus,$edareKetabStatus)
    {
        if($khaneKetabStatus== 'کتاب شابک ندارد' && $edareKetabStatus== 'کتاب شابک ندارد'){
            $bugId = 1;
        }elseif($khaneKetabStatus== 'کتاب در خانه کتاب وجود ندارد' && $edareKetabStatus== 'کتاب در اداره کتاب وجود ندارد'){
            $bugId = 2;
        }elseif($khaneKetabStatus== 'کتاب در خانه کتاب وجود ندارد' && $edareKetabStatus== 'کتاب در اداره کتاب وجود دارد'){
            $bugId = 3;
        }elseif($khaneKetabStatus== 'کتاب در خانه کتاب وجود دارد' && $edareKetabStatus== 'کتاب در اداره کتاب وجود ندارد'){
            $bugId = 4;
        }elseif($khaneKetabStatus== 'کتاب در خانه کتاب وجود دارد' && $edareKetabStatus== 'کتاب در اداره کتاب وجود دارد'){
            $bugId = 5;
        }else{
            $bugId = 6;
        }
        return $bugId;
    }
}