<?php
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 19.11.15
 * Time: 16:46
 */

class Support_Helper {

    /**
     * @param $url
     * @param string $ret
     * @param bool $head
     * @return bool|mixed
     */
    public static function doCurlRequest($url, $ret = 'output', $head = false)
    {
        $result = false;
        // create curl resource
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // turn off ssl verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($ch, CURLOPT_HEADER, $head);
        curl_setopt($ch, CURLOPT_TIMEOUT, 360);

        $output = curl_exec($ch);

        switch ($ret) {
            case 'output':
                $result = $output;
                break;
            case 'code':
                $result = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                break;
            case 'json':
                $result = json_decode($output);
                break;
        }

        curl_close($ch);
        return $result;
    }

    /**
     * @param $login
     * @param $password
     * @throws Exception
     * @throws NoSuchElementException
     * @throws Support_ConfigsException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function login($login, $password) //todo move to specified class for login and logout methods
    {
        $session = Support_Registry::singleton();
        $driver = $session->driver;
        try {
            $driver->findElement(WebDriverBy::cssSelector('div.aHead a[href*="admin/auth/logout"]'))->click();

            $driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('a.item-login')
                )
            );
        } catch (NoSuchElementException $e) {
        }

        sleep(3);
        $url_login = Support_Configs::get()->BASE_URL . "index.php?ac=user/login";
        $driver->get($url_login);

        $driver->wait()->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::id('userName')
            )
        );


        $driver->findElement(WebDriverBy::id('userName'))->sendKeys($login);
        $driver->findElement(WebDriverBy::id('userPass'))->sendKeys($password);
        $driver->findElement(WebDriverBy::cssSelector('.t-item-login-btn a[href]'))->click();

        sleep(1);

        $driver->wait()->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::cssSelector('#useraccount a.logout_link[href*="ac=user/logout"]')
            )
        );
    }

    public static function processTicketQueue() //todo move to system function class
    {
        PHPUnit_Framework_Assert::assertEquals(200, Support_Helper::doCurlRequest(Support_Configs::get()->MANAGE_URL . 'index.php?ac=selenium-test/process_tickets', 'code'));
        return true;
    }

    public static function openMainPage()   //todo move to GoPage class
    {
        $driver = Support_Registry::singleton()->driver;
        $driver->get(Support_Configs::get()->BASE_URL);

        // wait for page to load
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('lb-more')));
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('ticket_insert_button')));
    }

    public static function openAccountPage()//todo move to GoPage class
    {
        $driver = Support_Registry::singleton()->driver;

        if (Support_Helper::isMobile()) {
            if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.popup-is-opened .popup_close'), false)) {
                $driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .popup_close'))->click();
                usleep(250000);
            }
            $driver->findElement(WebDriverBy::cssSelector('#wwfooter .username .logged-in'))->click();
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/player/index"]')));
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .jt_account_settings div[onclick*="account_tickets"]')));
            sleep(1);
        } else {
            $driver->get(Support_Configs::get()->BASE_URL . '/index.php?ac=user/player/index');
            $driver->wait()->until(function () {
                if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.account-buttons a[href*="player_add_payment_method"]'), false)) {
                    return true;
                }
                if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.account-buttons a[href*="fund_account"]'), false)) {
                    return true;
                }
                if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.account-buttons a.disabled[href]'), false)) {
                    return true;
                }
            });
        }
    }

    public static function loginUnderAccount($account = false, $password = false) //todo move to specified login|logout class
    {
        if (!$account) {
            $account = Support_Registry::singleton()->account->email;
            $password = Support_Registry::singleton()->account->password;
        }
        // open main page
        $driver = Support_Registry::singleton()->driver;
        $driver->get(Support_Configs::get()->BASE_URL);

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('username')));
        // fill login form
        $driver->findElement(WebDriverBy::id('username'))->clear();
        $driver->findElement(WebDriverBy::id('username'))->sendKeys($account);

        $driver->findElement(WebDriverBy::id('password'))->clear();
        $driver->findElement(WebDriverBy::id('password'))->sendKeys($password);

        // submit form
        $driver->findElement(WebDriverBy::cssSelector('.sign-in-form button[type="submit"]'))->click();

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('.username a[href*="ac=user/player/index"]')
        ));

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector(' a.logout-link')
        ));

        sleep(2);
    }

    public static function clickButton($btn_control)
    {
        $driver = Support_Registry::singleton()->driver;
        // set ---
        // set ---
        if ($btn_control === "set") {
            $driver->findElement(WebDriverBy::id('ticket_insert_button'))->click();

            // wait for message
            for ($sec = 0; $sec <= 60; $sec++) {
                sleep(1);
                $isPresent = Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#custom_alert_btn'), false);
                if ( $isPresent )
                {
                    break;
                } else {
                    continue;
                }
            }
            // 10 set and print coupon ---
        } elseif ($btn_control === "10 set and print coupon") {
            // if button is disable then function return false
            $submit_buttons = $driver->findElements(WebDriverBy::cssSelector('.t-item-submit-final #submitgreen.btn_green'));
            if (strpos($submit_buttons[0]->getAttribute('class'), 'disabled') !== false) {
                return false;
            } else {
                $driver->findElement(WebDriverBy::cssSelector('#bet_place #submitgreen'))->click();

                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('.popup-success-msg.alert-msg')
                ));

                $driver->findElement(WebDriverBy::id('custom_alert_btn'))->click();

            }
        } elseif ($btn_control === "deposit") {
            if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.account-block a.form-link[href*="player_add_payment_method"]'), false)) {
                $driver->findElement(WebDriverBy::cssSelector('.account-block a.form-link[href*="player_add_payment_method"]'))->click();
                $driver->wait()->until(
                    WebDriverExpectedCondition::presenceOfElementLocated(
                        WebDriverBy::cssSelector('.player-form.settings-form.payment-form')
                    )
                );

            } elseif (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.account-block a.form-link[href*="fund_account"]'), false)) {
                $driver->findElement(WebDriverBy::cssSelector('.account-block a.form-link[href*="fund_account"]'))->click();
                $driver->wait()->until(
                    WebDriverExpectedCondition::presenceOfElementLocated(
                        WebDriverBy::cssSelector('#modal_content')
                    )
                );
            }

        } elseif ($btn_control === "deposit in account") {
            $driver->findElement(WebDriverBy::cssSelector('.account-block a.form-link[href*="fund_account"]'))->click();

            $driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#modal_content')
                )
            );
        } elseif ($btn_control === "pay") {
            $driver->findElement(WebDriverBy::cssSelector('.form-row-buttons button[type="submit"]'))->click();
        } elseif ($btn_control === "register") {
            $driver->findElement(WebDriverBy::cssSelector('.sign-in-form a[href*="register_player"]'))->click();
            // wait for form to load
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('f_terms')));
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('password2')));

        } elseif ($btn_control === "I forgot password") {
            if (Support_Helper::isMobile()) {
                // click to restore password button
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened .t-restore-pwd')));
                $driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .t-restore-pwd'))->click();

                // wait for restore password form opened
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/player/restore_password"]')));
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #email')));
            } else {
                $driver->findElement(WebDriverBy::cssSelector('.sign-in-form a.form-link[href*="restore_player_password"]'))->click();
                // wait for form to load
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('.restore-password-form #email')
                ));
            }
        } elseif ($btn_control === "save") {
            if (Support_Helper::isMobile()) {
                $driver->findElement(WebDriverBy::cssSelector('.active_page #btn_place_bet'))->click();
            } else {
                
                $driver->findElement(WebDriverBy::cssSelector('#LB_Player_Settings_Form button[type="submit"]'))->click();

                // wait for success message
                $driver->wait()->until(function() {
                        if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.popup-success-msg.alert-msg'), false))
                            return true;
                        if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#modal_content #mobile_confirm_key'), false))
                            return true;
                });
            }
        } elseif ($btn_control === "save phone") {
            if (Support_Helper::isMobile()) {
                $driver->findElement(WebDriverBy::cssSelector('.active_page #btn_place_bet'))->click();

                // wait for success message
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened .msg_success')));
                $driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .popup_btn_wrap .popup_btn'))->click();
                usleep(120000);
            } else {
                $driver->findElement(WebDriverBy::cssSelector('#LB_Player_Settings_Form button[type="submit"]'))->click();

                // wait for success message
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::id('mobile_confirm_key')
                ));
            }
        } elseif ($btn_control === "close account") {
            if (Support_Helper::isMobile()) {
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .linked[onclick*="close_account"]')));
                $driver->findElement(WebDriverBy::cssSelector('.active_page .linked[onclick*="close_account"]'))->click();

                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened .msg_success')));
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened .popup_btn.btn')));
                $driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .popup_btn.btn'))->click();
                usleep(150000);

                sleep(2);
                if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.popup-is-opened .msg_success'), false)) {
                    $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened .msg_success')));
                    $driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .popup_btn.btn'))->click();
                    usleep(150000);
                }
            } else {
                $driver->findElement(WebDriverBy::cssSelector('.account-settings a[href*="close_account"]'))->click();

                // wait for confirm message
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content #custom_confirm_btn')));
                $driver->findElement(WebDriverBy::cssSelector('#modal_content #custom_confirm_btn'))->click();
            }
        } elseif ($btn_control === "Temporarily block account") {
            if (Support_Helper::isMobile()) {
                if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.popup-is-opened .popup_close'), false)) {
                    $driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .popup_close'))->click();
                    usleep(150000);
                }
                $driver->findElement(WebDriverBy::cssSelector('.active_page .jt_account_settings div[onclick*="account_block_account"]'))->click();
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/player/block_account/form"]')));
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #period')));
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #tmstmp_end')));
            } else {
                $driver->findElement(WebDriverBy::cssSelector('.account-settings a[href*="lock_account"]'))->click();

                // wait for form open
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content #period')));
            }
        } elseif ($btn_control === "block") {
            if (Support_Helper::isMobile()) {
                $driver->findElement(WebDriverBy::cssSelector('.active_page #btn_place_bet'))->click();
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened .msg_success')));
                usleep(150000);
                $msg_text = strtolower($driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .msg_success'))->getText());
                $driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .popup_btn_wrap .popup_btn'))->click();
                usleep(150000);
            } else {
                //Account was successfully blocked
                $driver->findElement(WebDriverBy::cssSelector('#LB_Player_Blocking_Form button[type="submit"]'))->click();

                // wait for successful message
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content #custom_alert_btn')));
                $msg_text = strtolower($driver->findElement(WebDriverBy::cssSelector('#modal_content .popup-success-msg'))->getText());
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.current-blockings .block-remaining-time')));
                $driver->findElement(WebDriverBy::cssSelector('#modal_content #custom_alert_btn'))->click();
            }
            PHPUnit_Framework_Assert::assertContains('account', $msg_text);
            PHPUnit_Framework_Assert::assertContains('was', $msg_text);
            PHPUnit_Framework_Assert::assertContains('successfully', $msg_text);
            PHPUnit_Framework_Assert::assertContains('blocked', $msg_text);

            sleep(1);
        } elseif ($btn_control === "Generate new Player Vouchers") {
            $driver->findElement(WebDriverBy::id('t_voucher_gen_btn'))->click();

            // wait for popUp loaded
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('agg_voucher_amount')));
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('agg_voucher_nominal')));
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('agg_voucher_currency')));
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('submit')));

            usleep(120000);
        } elseif ($btn_control === "Bank transfer") {
            if (Support_Helper::isMobile()) {
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page button[onclick*="ac=mobile/player/withdraw/bank_transfer"]')));
                $driver->findElement(WebDriverBy::cssSelector('.active_page button[onclick*="ac=mobile/player/withdraw/bank_transfer"]'))->click();
            } else {
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content .personal-account.withdraw a[href*="player_add_bank_transfer"]')));
                $driver->findElement(WebDriverBy::cssSelector('#modal_content .personal-account.withdraw a[href*="player_add_bank_transfer"]'))->click();
            }
        } elseif ($btn_control === 'main events') {
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('button.t-add-main-event')));
            usleep(150000);
            $driver->findElement(WebDriverBy::cssSelector('button.t-add-main-event'))->click();
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#LB_MainEvent_Form')));
        }
    }

    /**
     * @throws Exception
     * @throws NoSuchElementException
     * @throws Support_ConfigsException
     * @throws Support_WWTimeoutException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function loginUnderAccountOnMobileSite()  //todo move to specified login|logout class
    {
        $driver = Support_Registry::singleton()->driver;

        // check and logout if i in backoffice
        if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('a.t-flush-cache'), false)) {
            Support_AdminHelper::logoutFromBackoffice();
            $driver->get(Support_Configs::get()->MOBILE_BASE_URL);
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="'.Support_Configs::get()->MOBILE_BASE_URL.'"]')));
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('li.t-about')));
            sleep(1);
        }

        // check that im on mobile page
        $cur_url = $driver->getCurrentURL();
        str_replace('https', 'http', $cur_url);
        if (strpos($cur_url, Support_Configs::get()->MOBILE_BASE_URL) === false) {
            $driver->get(Support_Configs::get()->MOBILE_BASE_URL);
        }
//        PHPUnit_Framework_Assert::assertContains(Support_Configs::get()->MOBILE_BASE_URL, $cur_url);

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#wwfooter .footer-link[onclick*=".mobile_app.show_login"]')));
        //usleep(250000);
        sleep(1);
        $driver->findElement(WebDriverBy::cssSelector('#wwfooter .footer-link[onclick*=".mobile_app.show_login"]'))->click();

        // wait for login form opened
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup.popup-is-opened #username')));
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup.popup-is-opened #password')));
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup.popup-is-opened .popup_btn_area button[type="submit"]')));

        // fill login form
        $driver->findElement(WebDriverBy::cssSelector('.popup.popup-is-opened #username'))->clear();
        $driver->findElement(WebDriverBy::cssSelector('.popup.popup-is-opened #username'))->sendKeys(Support_Registry::singleton()->account->email);
        $driver->findElement(WebDriverBy::cssSelector('.popup.popup-is-opened #password'))->clear();
        $driver->findElement(WebDriverBy::cssSelector('.popup.popup-is-opened #password'))->sendKeys(Support_Registry::singleton()->account->password);
        $driver->findElement(WebDriverBy::cssSelector('.popup.popup-is-opened .popup_btn_area button[type="submit"]'))->click();

        // wait for login
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#wwfooter .username .logged-in')));
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#wwfooter .balance .logged-in')));

        Support_Wait::forTextInElement(WebDriverBy::cssSelector('#wwfooter .username .logged-in'), Support_Registry::singleton()->account->email);

        sleep(1); //todo for any cases
    }

    // move to check class
    public static function isMobile()
    {
        if (strpos(str_replace('https', 'http', Support_Registry::singleton()->driver->getCurrentURL()), Support_Configs::get()->MOBILE_BASE_URL) !== false) {
            return true;
        } else {
            return false;
        }
    }

    public static function redeemVoucher($code)
    {
        $driver = Support_Registry::singleton()->driver;

        $driver->findElement(WebDriverBy::cssSelector('.account-settings a[href*="redeem_voucher"]'))->click();
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('LB_Player_Voucher_RedeemForm')));
        $driver->findElement(WebDriverBy::id('code'))->click();
        usleep(150000);
        $driver->findElement(WebDriverBy::id('code'))->sendKeys($code);

        $driver->findElement(WebDriverBy::cssSelector('#modal_content button[type="submit"]'))->click();
        usleep(150000);
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-success-msg')));
        $driver->findElement(WebDriverBy::id('custom_alert_btn'))->click();
        usleep(150000);
    }

    public static function logout() //todo move to specified login logout class
    {
        $driver = Support_Registry::singleton()->driver;

        if (Support_Helper::isMobile()) {
            // go to account page
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#wwfooter a.logged-in[onclick*="ac=mobile/player/index"]')));
            $driver->findElement(WebDriverBy::cssSelector('#wwfooter a.logged-in[onclick*="ac=mobile/player/index"]'))->click();

            // click to logout
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/player/index"]')));
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .li.linked[onclick*="mobile_logout"]')));
            $driver->findElement(WebDriverBy::cssSelector('.active_page .li.linked[onclick*="mobile_logout"]'))->click();

            // confirm logout
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened .popup_btn.btn.large')));
            usleep(150000);
            $driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .popup_btn.btn.large'))->click();

            // wait for logout
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/index/index"]')));
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .t-sport')));
            
            Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('#wwfooter a.logged-in[onclick*="ac=mobile/player/index"]'));
        } else {
            if (Support_Registry::singleton()->elementNotPresent(WebDriverBy::id('username'), false)) {
                $driver->findElement(WebDriverBy::cssSelector('.account-block .logout-link'))->click();
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('username')));
            }
        }
    }

    public static function iGetOneMoreAccount($false, $amount, $currency)
    {
        $driver = Support_Registry::singleton()->driver;

        if (Support_Helper::isMobile()) {
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#wwfooter .username a.logged-in')));
            $driver->findElement(WebDriverBy::cssSelector('#wwfooter .username a.logged-in'))->click();

            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/player/index"]')));
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .linked[onclick*="mobile_logou"]')));
            $driver->findElement(WebDriverBy::cssSelector('.active_page .linked[onclick*="mobile_logou"]'))->click();

            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup.popup-is-opened .popup_btn_wrap .popup_btn.btn')));
            $driver->findElement(WebDriverBy::cssSelector('.popup.popup-is-opened .popup_btn_wrap .popup_btn.btn'))->click();

            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#wwfooter .footer-link[onclick*=".mobile_app.show_login"]')));
            usleep(150000);
        } else {
            if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.account-block a.logout-link'))) {
                $driver->findElement(WebDriverBy::cssSelector('.account-block a.logout-link'))->click();
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('username')));
            }
        }
        Support_Registry::singleton()->account = new Support_AccountClass();
        Support_Registry::singleton()->account->createNewAccount(false, $amount, $currency);
    }

    public static function setPhoneOnPersonalAccountPage($phone, $country = '')
    {
        $driver = Support_Registry::singleton()->driver;
//.phone-plugin-wrapper input.phone-plugin-input
        $driver->executeScript("$('.phone-plugin-wrapper input.phone-plugin-input').focus()");
        usleep(150000);
        $driver->findElement(WebDriverBy::cssSelector('.phone-plugin-wrapper input.phone-plugin-input'))->clear();
        $driver->executeScript("$('.phone-plugin-wrapper input.phone-plugin-input').focus()");
        usleep(150000);
//        sleep(3);
        $driver->findElement(WebDriverBy::cssSelector('.phone-plugin-wrapper input.phone-plugin-input'))->sendKeys($phone);
//        $driver->executeScript("$('#mobile_phone_number').focus()");
//        usleep(150000);
//        $driver->findElement(WebDriverBy::id('mobile_phone_number'))->sendKeys(time());
    }

    /**
     * @param array $arKeyWords
     * @param \Facebook\WebDriver\Remote\RemoteWebElement[] $arElements
     * @return bool
     */
    public static function checkArrayEntryInTableRows($arKeyWords, $arElements)
    {
        $result = false;
        
        foreach ($arElements as $element) {
            $row_text = $element->getText();
            foreach ($arKeyWords as $keyword) {
                if (strpos($row_text, $keyword) === false) {
                    $result = false;
                    continue;
                } else {
                    $result = true;
                }
            }

            if ($result) return $result;
        }

        return $result;
    }

    public static function generateBonusShopItems()
    {
        PHPUnit_Framework_Assert::assertEquals(200, Support_Helper::doCurlRequest(Support_Configs::get()->MANAGE_URL . 'index.php?ac=selenium-test/generate_bonus_shop_items', 'code'));
        return true;
    }
}