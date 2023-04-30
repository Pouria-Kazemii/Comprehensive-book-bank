<?php

if(!function_exists('priceFormat'))
{
    /**
     * Change number to price format
     *
     * @param string $priceNumber
     * @return string $price
     */
    function priceFormat($priceNumber)
    {
        $priceNumber = (int) filter_var($priceNumber, FILTER_SANITIZE_NUMBER_INT);

        return $priceNumber > 0 ? number_format($priceNumber, 0) : 0;
    }
}
