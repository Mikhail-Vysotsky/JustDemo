<?php
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 07.12.15
 * Time: 17:39
 */

class Support_Close {
    public static function closeOverlayContainer()
    {
        $session = Support_Registry::singleton();
        $driver = $session->driver;
        try {
            if ($driver->findElement(WebDriverBy::id('overlay_content'))->isDisplayed()) {
                $driver->findElement(WebDriverBy::id('overlay_close_btn'))->click();
                $driver->wait()->until(
                    WebDriverExpectedCondition::invisibilityOfElementLocated(
                        WebDriverBy::id('overlay_content')
                    )
                );
            }
        } catch (WebDriverException $e) {
            // nothing to do if element not present
        }
    }
}