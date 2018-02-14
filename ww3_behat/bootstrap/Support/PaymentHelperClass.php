<?php
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 05.02.16
 * Time: 14:49
 */

class Support_PaymentHelperClass {

    /**
     * @param $card_type
     * @param bool $mobile
     * @throws Support_ConfigsException
     */
    public static function fillPaymentData( $card_type , $mobile = false) {
        if ($mobile) {
            $pan = "pan";
            $expirymonth = "expirymonth";
            $expiryyear = "expiryyear";
            $securitycode = "securitycode";
        } else {
            $pan = "st-pan-textfield";
            $expirymonth = "st-expirymonth-dropdown";
            $expiryyear = "st-expiryyear-dropdown";
            $securitycode = "st-securitycode-textfield";
        }
        $session = Support_Registry::singleton();
        $driver = $session->driver;
        if ($card_type === "visa") {
            $driver->findElement(WebDriverBy::id($pan))->sendKeys(Support_Configs::get()->visa_card);
        } elseif ($card_type === "mastercard") {
            $driver->findElement(WebDriverBy::id($pan))->sendKeys(Support_Configs::get()->mastercard_card);
        }
        $driver->findElement(WebDriverBy::cssSelector('#'.$expirymonth.' option[value="12"]'))->click();
        $driver->findElement(WebDriverBy::cssSelector('#'.$expiryyear.' option[value="2030"]'))->click();
        $driver->findElement(WebDriverBy::id($securitycode))->sendKeys('123');
        $driver->findElement(WebDriverBy::id('submit'))->click();


    }

    /**
     * @throws Exception
     * @throws Support_ConfigsException
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function fillSkrillPayData()
    {
        $session = Support_Registry::singleton();
        $driver = $session->driver;

        for ($i = 0; $i < 120; $i++) {
            if ($session->elementPresent(WebDriverBy::id('already_has_account'), false)) {
                $driver->findElement(WebDriverBy::id('already_has_account'))->click();
            }
            sleep(1);
            if ($session->elementPresent(WebDriverBy::id('password'), false)) {
                break;
            }
        }

        // login under test account
        $driver->findElement(WebDriverBy::id('email'))->clear();
        $driver->findElement(WebDriverBy::id('email'))->sendKeys(Support_Configs::get()->skrill_email);
        $driver->findElement(WebDriverBy::id('password'))->sendKeys(Support_Configs::get()->skrill_password);
        $driver->findElement(WebDriverBy::id('login'))->click();

        // confirm payment
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('confirm_and_pay')));
        $driver->findElement(WebDriverBy::id('confirm_and_pay'))->click();
    }

    /**
     * @throws Exception
     * @throws Support_ConfigsException
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function fillPaypal() #todo won't fix because can't locate elements after page to load
    {
        $session = Support_Registry::singleton();
        $driver = $session->driver;

        sleep(10);
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('login_emaildiv')));
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('login_passworddiv')));
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('btnLogin')));

        // fill the form
        $driver->findElement(WebDriverBy::id('login_emaildiv'))->sendKeys(Support_Configs::get()->paypal_email);
        $driver->findElement(WebDriverBy::id('login_passworddiv'))->sendKeys(Support_Configs::get()->paypal_password);
        $driver->findElement(WebDriverBy::id('btnLogin'))->click();

        // confirm payment
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('confirmButtonTop')));
        $driver->findElement(WebDriverBy::id('confirmButtonTop'))->click();

    }

    /**
     * @throws Exception
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function fillPaysafe()
    {
        $session = Support_Registry::singleton();
        $driver = $session->driver;
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('classicPin-addPinField')));

        usleep(120000);
        $driver->findElement(WebDriverBy::id('classicPin-addPinField'))->clear();
        $driver->findElement(WebDriverBy::id('classicPin-addPinField'))->sendKeys('0000000009902556');
        $driver->findElement(WebDriverBy::id('acceptTerms'))->click();
        usleep(120000);

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.remove-added-pin')));
        usleep(120000);

        $driver->findElement(WebDriverBy::id('payBtn'))->click();
    }

    /**
     * @param $method
     * @throws Exception
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function selectPaymentMethod($method)
    {
        $driver = Support_Registry::singleton()->driver;

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('payment_amount')));

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('.payment-method-list a[data-method="'.$method.'"]')
        ));

        usleep(120000);

        $driver->findElement(WebDriverBy::cssSelector('.payment-method-list a[data-method="'.$method.'"]'))->click();

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector(
            '.payment-method-list a.active[data-method="'.$method.'"]'
        )));

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('payment_amount')));

        sleep(2);
        $driver->findElement(WebDriverBy::id('payment_amount'))->clear();
    }

    /**
     * @param $method
     * @throws Exception
     * @throws Support_ConfigsException
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function submitPayment($method)
    {
        $driver = Support_Registry::singleton()->driver;
        if ($method === "skrill") {
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('email')));

            $driver->findElement(WebDriverBy::id('email'))->sendKeys(Support_Configs::get()->skrill_email);
            $driver->findElement(WebDriverBy::cssSelector('#LB_Player_PaymentMethod_Form_Skrill button[type="submit"]'))->click();

            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('merchant')));
        } elseif ($method === "paypal") {
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('email')));
            $driver->findElement(WebDriverBy::id('email'))->sendKeys(Support_Registry::singleton()->account->email);

            $driver->findElement(WebDriverBy::cssSelector('#LB_Player_PaymentMethod_Form_Paypal button[type="submit"]'))->click();

            $driver->wait()->until(function(){
                $url = Support_Registry::singleton()->driver->getCurrentURL();
                while (strpos($url, 'sandbox') === false) {
                    sleep(1);
                    $url = $url = Support_Registry::singleton()->driver->getCurrentURL();
                }

                return true;
            });

            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('password')));

        } elseif ($method === "mastercard") {
            $driver->findElement(WebDriverBy::cssSelector('#LB_Player_PaymentMethod_Form_Mastercard button[type="submit"]'))->click();
            $driver->wait()->until(function() {
                if (Support_Registry::singleton()->elementPresent(WebDriverBy::id('pan'), false)) {
                    return true;
                }
                if (Support_Registry::singleton()->elementPresent(WebDriverBy::id('st-pan-textfield'), false)) {
                    return true;
                }
            });

        } elseif ($method === "visa") {
            $driver->findElement(WebDriverBy::cssSelector('#LB_Player_PaymentMethod_Form_Visa button[type="submit"]'))->click();
            $driver->wait()->until(function() {
                if (Support_Registry::singleton()->elementPresent(WebDriverBy::id('pan'), false)) {
                    return true;
                }
                if (Support_Registry::singleton()->elementPresent(WebDriverBy::id('st-pan-textfield'), false)) {
                    return true;
                }
            });

        } elseif ($method === "paysafe") {
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('card_number')));

            $driver->findElement(WebDriverBy::id('card_number'))->sendKeys(Support_Configs::get()->paysafe_card);
            $driver->findElement(WebDriverBy::cssSelector('#LB_Player_PaymentMethod_Form_Paysafe button[type="submit"]'))->click();

            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::id('pan')
            ));
        }
    }
}