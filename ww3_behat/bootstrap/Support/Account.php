<?php

/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 09.06.16
 * Time: 9:30
 */

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver;

class Support_Account
{

    public static function generatePhoneByTemplate($phone_template = '912 345-67-89')
    {
        $arString = str_split($phone_template, 1);
        $isPrefix = true;
        $result = '';

        foreach ($arString as $item) {
            if ($item === ' ') $isPrefix = false;

            if ($isPrefix) {
                $result .= $item;
            } else {
                if (is_numeric($item)) {
                    $result .= rand(0, 9);
                } else {
                    continue;
//                    $result .= $item;
                }

            }
        }
        return $result;
    }
}