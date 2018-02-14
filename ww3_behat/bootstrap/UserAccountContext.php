<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 20.11.15
 * Time: 12:34
 */
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class UserAccountContext implements Context, SnippetAcceptingContext {

    private $first_name;
    private $last_name;
    private $mobile_phone;
    private $new_mobile_phone = '';
    private $email_confirmation_url;
    private $date_of_birth = '11.12.1977';
    private $birth_place = 'test server';
    private $password_restore_url;
    private $account;
    private $driver;

    function __construct()
    {
        $time = time();
        $pref = substr($time, 0, strlen($time)/2);
        $suff = substr($time, strlen($time)/2);

        Support_Registry::singleton()->account = new Support_AccountClass();
        Support_Registry::singleton()->account->email = "behat_$time@vysotsky.rssystems.ru";
        $this->account = Support_Registry::singleton()->account;
        $this->account->password = '123';

        $this->first_name = "behat_$pref";
        $this->last_name = "test_$suff";
//        $this->mobile_phone = $time;
        $this->mobile_phone = '007'.'912'.rand(1000000, 9999999);

        $this->driver = Support_Registry::singleton()->driver;
    }

    function __destruct() {
        $this->account = null;
    }


    /**
     * @Given /^I fill account registration form$/
     */
    public function iFillAccountRegistrationForm()
    {
        if (Support_Helper::isMobile()) {
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .row__f_terms input#f_terms')));
            sleep(1);
            $this->driver->executeScript("$('.active_page #f_terms').click()");

            $this->driver->executeScript("$('.active_page #f_confirm').click()");

            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #currency option[value="EUR"]'))->click();
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #country option[value="DE"]'))->click();
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #first_name'))->sendKeys($this->first_name);
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #last_name'))->sendKeys($this->last_name);
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #birth_tmstmp'))->click();
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #birth_tmstmp'))->sendKeys($this->date_of_birth);
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #birth_place'))->sendKeys($this->birth_place);
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #email'))->sendKeys($this->account->email);

            // set phone country
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page .phone-plugin-wrapper .selected-dial-code'))->click();
            usleep(250000);
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page .phone-plugin-wrapper .country-list li[data-country-code="ru"]'))->getLocationOnScreenOnceScrolledIntoView();
            usleep(250000);
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page .phone-plugin-wrapper .country-list li[data-country-code="ru"]'))->click();
            usleep(250000);

            //enter phone
            $phone_template = $this->driver->findElement(WebDriverBy::cssSelector('.active_page .phone-plugin-wrapper .phone-plugin-input'))->getAttribute('placeholder');
            $this->mobile_phone = Support_Account::generatePhoneByTemplate($phone_template);
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page .phone-plugin-wrapper .phone-plugin-input'))->sendKeys($this->mobile_phone);


            usleep(150000);
//            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #mobile_phone'))->sendKeys($this->mobile_phone );
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #password'))->sendKeys($this->account->password);
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #password2'))->sendKeys($this->account->password);
        } else {
            // fill registration form
            $this->driver->findElement(WebDriverBy::id('email'))->sendKeys($this->account->email);
            $this->driver->findElement(WebDriverBy::cssSelector('#currencySelectBoxItArrow'))->click();
            usleep(250000);
            $this->driver->findElement(WebDriverBy::cssSelector('#currencySelectBoxItOptions .selectboxit-option[data-val="EUR"]'))->click();

            $this->driver->findElement(WebDriverBy::cssSelector('#countrySelectBoxItArrow'))->click();
            $this->driver->findElement(WebDriverBy::cssSelector('#countrySelectBoxItOptions .selectboxit-option[data-val="DE"]'))->click();
            usleep(250000);
            $this->driver->findElement(WebDriverBy::id('password'))->sendKeys($this->account->password);
            $this->driver->findElement(WebDriverBy::id('password2'))->sendKeys($this->account->password);
            $this->driver->findElement(WebDriverBy::id('first_name'))->sendKeys($this->first_name);
            $this->driver->findElement(WebDriverBy::id('last_name'))->sendKeys($this->last_name);

            $this->driver->findElement(WebDriverBy::id('birth_tmstmp'))->click();
            $this->driver->findElement(WebDriverBy::id('birth_tmstmp'))->sendKeys($this->date_of_birth);
            $this->driver->findElement(WebDriverBy::id('email'))->click();
            usleep(120000);
            $this->driver->findElement(WebDriverBy::id('birth_place'))->sendKeys($this->birth_place);
            usleep(150000);


            // set phone country
            $this->driver->findElement(WebDriverBy::cssSelector('.phone-plugin-wrapper .selected-dial-code'))->click();
            usleep(250000);
            $this->driver->findElement(WebDriverBy::cssSelector('.phone-plugin-wrapper .country-list li[data-country-code="ru"]'))->getLocationOnScreenOnceScrolledIntoView();
            usleep(250000);
            $this->driver->findElement(WebDriverBy::cssSelector('.phone-plugin-wrapper .country-list li[data-country-code="ru"]'))->click();
            usleep(250000);

            $phone_template = $this->driver->findElement(WebDriverBy::cssSelector('.phone-plugin-wrapper .phone-plugin-input'))->getAttribute('placeholder');
            $this->mobile_phone = Support_Account::generatePhoneByTemplate($phone_template);
            $this->driver->findElement(WebDriverBy::cssSelector('.phone-plugin-wrapper .phone-plugin-input'))->sendKeys($this->mobile_phone);

            usleep(120000);
            $this->driver->findElement(WebDriverBy::id('f_confirm'))->click();
            $this->driver->findElement(WebDriverBy::id('f_terms'))->click();
        }
    }

    /**
     * @Given /^I submit registration$/
     */
    public function iSubmitRegistration()
    {
        if (Support_Helper::isMobile()) {
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #btn_place_bet'))->click();
        } else {
            sleep(1);
            $this->driver->findElement(WebDriverBy::cssSelector('#LB_Player_Registration_Form .form-row-buttons button[type="submit"]'))->click();
        }
    }



    /**
     * @Then /^I get "([^"]*)" email$/
     */
    public function iGetEmail($mail_type)
    {
        $mail = str_replace('vysotsky.', '', Support_Registry::singleton()->account->email);
//        $mail = Support_Registry::singleton()->account->email;

        $obMail = new Support_TestMailClass();
        $content = $obMail->waitForNewMessage($mail);


        if ($mail_type === "account registration") {
            PHPUnit_Framework_Assert::assertTrue(strlen($content) > 10);
            PHPUnit_Framework_Assert::assertContains($this->account->email, $content);
            PHPUnit_Framework_Assert::assertContains($this->account->password, $content);

            $this->email_confirmation_url = $obMail::getLinkFromString($content);
        } elseif ($mail_type === "Wir-Wetten account restore password") {
            PHPUnit_Framework_Assert::assertTrue(strlen($content) > 10);

            $this->password_restore_url = $obMail::getLinkFromString($content);
        } elseif ($mail_type === "new password") {
            PHPUnit_Framework_Assert::assertTrue(strlen($content) > 10);
            PHPUnit_Framework_Assert::assertContains(Support_Registry::singleton()->account->email, $content);

            $this->account->old_password = Support_Registry::singleton()->account->password;
            Support_Registry::singleton()->account->password = $this->account->password = Support_TestMailClass::getPasswordFromEmailContent($content);
        } elseif ($mail_type === "Wir-Wetten account change email address") {
            PHPUnit_Framework_Assert::assertTrue(strlen($content) > 10);

            $this->email_confirmation_url = $obMail::getLinkFromString($content);
        }
    }

    /**
     * @When /^I open confirm email url$/
     */
    public function iOpenConfirmEmailUrl()
    {
        $this->driver->get($this->email_confirmation_url);
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('mobile_confirm_key')));
    }

    /**
     * @Given /^I enter confirm phone key$/
     */
    public function iEnterConfirmPhoneKey()
    {
        Support_Registry::singleton()->account->getConfirmPhoneKey();
        $popUp = false;
        if (Support_Helper::isMobile()) {
            if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.popup-is-opened #mobile_confirm_key'), false)) {
                $field_selector  = '.popup-is-opened #mobile_confirm_key';
                $submit_selector = '.popup-is-opened #btn_place_bet';
                $popUp = true;
            } else {
                $field_selector  = '.active_page #mobile_confirm_key';
                $submit_selector = '.active_page #btn_place_bet';
            }

            usleep(150000);
            $this->driver->findElement(WebDriverBy::cssSelector($field_selector))->sendKeys(Support_Registry::singleton()->account->phone_confirm_key);
            usleep(150000);

            $this->driver->findElement(WebDriverBy::cssSelector($submit_selector))->click();

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#wwfooter .username .footer-link.logged-in')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .li.linked[onclick*="urls.account_personal_data"]')));

            if ($popUp) {
                sleep(1);
                $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .popup_btn.btn'))->click();
                sleep(1);
            }
        } else {
            $this->driver->findElement(WebDriverBy::id('mobile_confirm_key'))->sendKeys(Support_Registry::singleton()->account->phone_confirm_key);

//            $this->driver->findElement(WebDriverBy::cssSelector('.form-row-buttons button[type="submit"]'))->click();
            sleep(1);
//            sleep(99999);
            $this->driver->findElement(WebDriverBy::cssSelector('.player-form .phone-confirm-send-sms button[type="submit"]'))->click();

            $this->driver->wait()->until( function(){
                try {
                    if (Support_Registry::singleton()->elementNotPresent(WebDriverBy::id('mobile_confirm_key'))) {
                        return true;
                    } else {
                        return false;
                    }
                } catch (Exception $e) {}
            });
        }
    }

    /**
     * @Then /^Account created$/
     */
    public function accountCreated()
    {
        if (!Support_Helper::isMobile()) {
            $this->driver->findElement(WebDriverBy::cssSelector('.account-block .username'));
            $this->driver->findElement(WebDriverBy::cssSelector('.account-block .logout-link'));
            $this->driver->findElement(WebDriverBy::cssSelector('.account-block a[href*="player_add_payment_method"]'));
        } else {
            $curr_email = $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter .username a.footer-link.logged-in'))->getText();
            PHPUnit_Framework_Assert::assertEquals($this->account->email, $curr_email);
        }
    }

    /**
     * @Given /^If i do logout$/
     */
    public function ifIDoLogout()
    {
        if (!Support_Helper::isMobile()) {
            $this->driver->findElement(WebDriverBy::cssSelector('.account-block .logout-link'))->click();

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('username')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('password')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.sign-in-form a[href*="register_player"]')));
        } else {
            // go to userpage
            $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter .username a[onclick*="ac=mobile/player/index"]'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="mobile/player/index"]')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page div.linked[onclick*="mobile_logout"]')));

            // click logout
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page div.linked[onclick*="mobile_logout"]'))->click();

            // confirm logout
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened .msg_success')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened .popup_btn_wrap .popup_btn.btn')));
            $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .popup_btn_wrap .popup_btn.btn'))->click();
            usleep(150000);
        }
    }

    /**
     * @Then /^I authorized$/
     */
    public function iAuthorized()
    {
        if (Support_Helper::isMobile()) {
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#wwfooter .username a.footer-link.logged-in')));
            $curr_email = $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter .username a.footer-link.logged-in'))->getText();
            PHPUnit_Framework_Assert::assertEquals($this->account->email, $curr_email);
        } else {
            $this->driver->findElement(WebDriverBy::cssSelector('.account-block .username'));
            $this->driver->findElement(WebDriverBy::cssSelector('.account-block .logout-link'));
            $this->driver->findElement(WebDriverBy::cssSelector('.account-block a[href*="player_add_payment_method"]'));
        }
    }

    /**
     * @Given /^User personal data is correct$/
     */
    public function userPersonalDataIsCorrect()
    {
        // go to account info tab
        if (Support_Helper::isMobile()) {
            $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter .username a.footer-link.logged-in'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/player/index"]')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page div[onclick*="account_personal_data"]')));

            $this->driver->findElement(WebDriverBy::cssSelector('.active_page div[onclick*="account_personal_data"]'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/player/settings"]')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #first_name')));
        } else {
            $this->driver->findElement(WebDriverBy::cssSelector('.account-block .username a[href]'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.account-menu-item a[href*="ac=user/player/settings"]')
            ));

            $this->driver->findElement(WebDriverBy::cssSelector('.account-menu-item a[href*="ac=user/player/settings"]'))->click();

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('old_password')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('password')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('password2')));
        }

        // check user personal data
        if (Support_Helper::isMobile()) {
            PHPUnit_Framework_Assert::assertEquals($this->driver->findElement(WebDriverBy::cssSelector('.active_page #first_name'))->getAttribute('value'), $this->first_name);
            PHPUnit_Framework_Assert::assertEquals($this->driver->findElement(WebDriverBy::cssSelector('.active_page #last_name'))->getAttribute('value'), $this->last_name);
            PHPUnit_Framework_Assert::assertEquals($this->driver->findElement(WebDriverBy::cssSelector('.active_page #birth_tmstmp'))->getAttribute('value'), $this->date_of_birth);
            PHPUnit_Framework_Assert::assertEquals($this->driver->findElement(WebDriverBy::cssSelector('.active_page #birth_place'))->getAttribute('value'), $this->birth_place);
            PHPUnit_Framework_Assert::assertEquals($this->driver->findElement(WebDriverBy::cssSelector('.active_page #email'))->getAttribute('value'), $this->account->email);
            PHPUnit_Framework_Assert::assertContains($this->mobile_phone, $this->driver->findElement(WebDriverBy::cssSelector('.active_page #phone'))->getAttribute('value'));
        } else {
            PHPUnit_Framework_Assert::assertEquals($this->driver->findElement(WebDriverBy::id('first_name'))->getAttribute('value'), $this->first_name);
            PHPUnit_Framework_Assert::assertEquals($this->driver->findElement(WebDriverBy::id('last_name'))->getAttribute('value'), $this->last_name);
            PHPUnit_Framework_Assert::assertEquals($this->driver->findElement(WebDriverBy::id('birth_tmstmp'))->getAttribute('value'), $this->date_of_birth);
            PHPUnit_Framework_Assert::assertEquals($this->driver->findElement(WebDriverBy::id('birth_place'))->getAttribute('value'), $this->birth_place);
            PHPUnit_Framework_Assert::assertEquals($this->driver->findElement(WebDriverBy::id('email'))->getAttribute('value'), $this->account->email);

            PHPUnit_Framework_Assert::assertContains($this->mobile_phone, $this->driver->findElement(WebDriverBy::id('phone'))->getAttribute('value'));
        }
    }

    /**
     * @Given /^I no need any confirmation again$/
     */
    public function iNoNeedAnyConfirmationAgain()   //todo research for that i do it
    {
        return true; // its ok;
    }

    /**
     * @Given /^I go to personal data tab$/
     */
    public function iGoToPersonalDataTab()
    {
        if (Support_Helper::isMobile()) {
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page div.linked[onclick*="account_personal_data"]')));
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page div.linked[onclick*="account_personal_data"]'))->click();

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #first_name')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #street')));
        } else {
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.account-block a[href*="ac=user/player/settings"]')));

            $this->driver->findElement(WebDriverBy::cssSelector('.account-block a[href*="ac=user/player/settings"]'))->click();

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('old_password')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('password')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('password2')));
        }
    }

    /**
     * @When /^I fill and submit change password form$/
     */
    public function iFillAndSubmitChangePasswordForm()
    {
        if (!Support_Helper::isMobile()) {
            $this->driver->findElement(WebDriverBy::id('old_password'))->sendKeys($this->account->password);
            $this->driver->findElement(WebDriverBy::id('password'))->sendKeys('321');
            $this->driver->findElement(WebDriverBy::id('password2'))->sendKeys('321');

            $this->driver->findElement(WebDriverBy::cssSelector('#LB_Player_Settings_PasswordChangeForm button[type="submit"]'))->click();
        } else {
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #old_password'))->sendKeys($this->account->password);
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #password'))->sendKeys('321');
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #password2'))->sendKeys('321');
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #btn_place_bet'))->click();
        }
        $this->account->old_password = $this->account->password;
        $this->account->password = '321';
        Support_Registry::singleton()->account->password = '321';
    }

    /**
     * @Given /^I can not login via old password$/
     */
    public function iCanNotLoginViaOldPassword()
    {
        try {
            if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.account-block .logout-link'))
                && !Support_Helper::isMobile()) {
                $this->ifIDoLogout();
            }
        } catch (Exception $e) {}


        if (Support_Helper::isMobile()) {
            if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#wwfooter .username a[onclick*="ac=mobile/player/index"]'), false)) {
                $this->ifIDoLogout();
            }
            // click to login icon
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#wwfooter .footer-link[onclick*=".mobile_app.show_login"]')));
            $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter .footer-link[onclick*=".mobile_app.show_login"]'))->click();

            // wait for login form
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened #username')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened #password')));

            // fill login data
            $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened #username'))->sendKeys($this->account->email);
            $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened #password'))->sendKeys($this->account->old_password);

            // submit form
            $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .popup_btn_area button[type="submit"]'))->click();

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened .form_errors div')));
            $error_text = $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .form_errors div'))->getText();

            sleep(1); //for any cases
            Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('#wwfooter .username'));
            Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#wwfooter .footer-link[onclick*=".mobile_app.show_login"]'));
        } else {
            $this->driver->findElement(WebDriverBy::id('username'))->sendKeys($this->account->email);
            $this->driver->findElement(WebDriverBy::id('password'))->sendKeys($this->account->old_password);

            $this->driver->findElement(WebDriverBy::cssSelector('.sign-in-form button[type="submit"]'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content .popup-success-msg')));
            $error_text = $this->driver->findElement(WebDriverBy::cssSelector('#modal_content .popup-success-msg'))->getText();

            $this->driver->findElement(WebDriverBy::id('custom_alert_btn'))->click();
            PHPUnit_Framework_Assert::assertTrue(Support_Registry::singleton()->elementPresent(WebDriverBy::id('username')));
            PHPUnit_Framework_Assert::assertTrue(Support_Registry::singleton()->elementPresent(WebDriverBy::id('password')));
            PHPUnit_Framework_Assert::assertTrue(Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('.account-block .logout-link')));

            $username_value = $this->driver->findElement(WebDriverBy::id('username'))->getAttribute('value');

            PHPUnit_Framework_Assert::assertEquals($this->account->email, $username_value);
        }
        PHPUnit_Framework_Assert::assertContains('Invalid username or password', $error_text);

    }

    /**
     * @Given /^I can login under new password$/
     */
    public function iCanLoginUnderNewPassword()
    {
        if (Support_Helper::isMobile()) {
            $this->driver->get(Support_Configs::get()->MOBILE_BASE_URL);
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.active_page[pageurl*="'.Support_Configs::get()->MOBILE_BASE_URL.'"]')
            ));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .t-about')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#wwfooter .footer-link[onclick*=".mobile_app.show_login"]')));

            Support_Helper::loginUnderAccountOnMobileSite();
        } else {
            // open main page
            $driver = $this->driver;

            // fill login form
            $driver->findElement(WebDriverBy::id('username'))->clear();
            $driver->findElement(WebDriverBy::id('username'))->sendKeys($this->account->email);

            $driver->findElement(WebDriverBy::id('password'))->clear();
            $driver->findElement(WebDriverBy::id('password'))->sendKeys($this->account->password);

            // submit form
            $driver->findElement(WebDriverBy::cssSelector('.sign-in-form button[type="submit"]'))->click();


            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::id('account-row')
            ));

            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('#account-row .logout-link')
            ));
        }
        sleep(2);
    }

    /**
     * @Given /^I go to account page$/
     */
    public function iGoToAccountPage()
    {
        if (!Support_Helper::isMobile()) {
            $this->driver->get(Support_Configs::get()->BASE_URL.'/index.php?ac=user/player/index');
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.account-buttons a[href*="player_add_payment_method"]')
            ));
        } else {
            $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter .username a.footer-link.logged-in'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/player/index"]')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page div[onclick*="account_personal_data"]')));
        }
    }

    /**
     * @When /^I fill email and click restore button$/
     */
    public function iFillEmailAndClickRestoreButton()
    {
        if (Support_Helper::isMobile()) {
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #email'))->sendKeys(Support_Registry::singleton()->account->email);
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #btn_place_bet'))->click();
        } else {
            $this->driver->findElement(WebDriverBy::cssSelector('.restore-password-form #email'))->sendKeys(Support_Registry::singleton()->account->email);
            $this->driver->findElement(WebDriverBy::cssSelector('#modal_content button[type="submit"]'))->click();
        }
    }

    /**
     * @When /^I open link for restore password$/
     */
    public function iOpenLinkForRestorePassword()
    {
        $this->driver->get($this->password_restore_url);
    }

    /**
     * @When /^I change all personal data$/
     */
    public function iChangeAllPersonalData()
    {
        $this->account->first_name = 'Behat';
        $this->account->last_name = 'Test';
        $this->account->birth_tmstmp = '07.07.1977';
        $this->account->birth_place = 'Autotest Server';
        $this->account->country = 'Germany';
        $this->account->city = 'New City';
        $this->account->street = 'Something street';
        $this->account->zip = '123456';


        if (Support_Helper::isMobile()) {
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #first_name'))->clear();
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #first_name'))->sendKeys($this->account->first_name);

            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #last_name'))->clear();
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #last_name'))->sendKeys($this->account->last_name);

            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #birth_tmstmp'))->clear();
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #birth_tmstmp'))->sendKeys($this->account->birth_tmstmp);

            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #birth_place'))->clear();
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #birth_place'))->sendKeys($this->account->birth_place);

            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #country option[value="DE"]'))->click();

            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #city'))->clear();
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #city'))->sendKeys($this->account->city);

            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #street'))->clear();
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #street'))->sendKeys($this->account->street);

            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #zip'))->clear();
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #zip'))->sendKeys($this->account->zip);
        } else {
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('zip')));

            $this->driver->findElement(WebDriverBy::id('first_name'))->clear();
            $this->driver->findElement(WebDriverBy::id('first_name'))->sendKeys($this->account->first_name);

            $this->driver->findElement(WebDriverBy::id('last_name'))->clear();
            $this->driver->findElement(WebDriverBy::id('last_name'))->sendKeys($this->account->last_name);

            $this->driver->findElement(WebDriverBy::id('birth_tmstmp'))->clear();
            $this->driver->findElement(WebDriverBy::id('birth_tmstmp'))->sendKeys('07.07.1977');

            $this->driver->findElement(WebDriverBy::id('birth_place'))->clear();
            $this->driver->findElement(WebDriverBy::id('birth_place'))->sendKeys($this->account->birth_place);

            $this->driver->findElement(WebDriverBy::cssSelector('#countrySelectBoxItArrow'))->click();
            $this->driver->findElement(WebDriverBy::cssSelector('#countrySelectBoxItOptions .selectboxit-option[data-val="DE"]'))->click();
            usleep(250000);

            $this->driver->findElement(WebDriverBy::id('city'))->clear();
            $this->driver->findElement(WebDriverBy::id('city'))->sendKeys($this->account->city);

            $this->driver->findElement(WebDriverBy::id('street'))->clear();
            $this->driver->findElement(WebDriverBy::id('street'))->sendKeys($this->account->street);

            $this->driver->findElement(WebDriverBy::id('zip'))->clear();
            $this->driver->findElement(WebDriverBy::id('zip'))->sendKeys($this->account->zip);

            $this->driver->findElement(WebDriverBy::cssSelector('#languageSelectBoxItArrow'))->click();
            usleep(150000);
            $this->driver->findElement(WebDriverBy::cssSelector('#languageSelectBoxItOptions .selectboxit-option[data-val="es"]'))->click();

            usleep(250000);
            $this->account->language = strtolower($this->driver->findElement(WebDriverBy::id('languageSelectBoxItText'))->getText());
        }
    }

    /**
     * @Given /^I see that all changed data is saved$/
     */
    public function iSeeThatAllChangedDataIsSaved()
    {
        $driver = $this->driver;
        $this->ifIDoLogout();

        if (Support_Helper::isMobile()) {
            Support_Helper::loginUnderAccountOnMobileSite();

            // go to personal data page
            $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter .username a.footer-link.logged-in'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/player/index"]')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page div[onclick*="account_personal_data"]')));

            $this->driver->findElement(WebDriverBy::cssSelector('.active_page div.linked[onclick*="account_personal_data"]'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="mobile/player/settings"]')));

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #first_name')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #street')));
            sleep(1);

            // check personal data
            PHPUnit_Framework_Assert::assertEquals($this->account->first_name, $driver->findElement(WebDriverBy::cssSelector('.active_page #first_name'))->getAttribute('value'));
            PHPUnit_Framework_Assert::assertEquals($this->account->last_name, $driver->findElement(WebDriverBy::cssSelector('.active_page #last_name'))->getAttribute('value'));
            PHPUnit_Framework_Assert::assertEquals($this->account->birth_tmstmp, $driver->findElement(WebDriverBy::cssSelector('.active_page #birth_tmstmp'))->getAttribute('value'));
            PHPUnit_Framework_Assert::assertEquals($this->account->birth_place, $driver->findElement(WebDriverBy::cssSelector('.active_page #birth_place'))->getAttribute('value'));
            PHPUnit_Framework_Assert::assertEquals(Support_Registry::singleton()->account->email, $driver->findElement(WebDriverBy::cssSelector('.active_page #email'))->getAttribute('value'));
//            PHPUnit_Framework_Assert::assertEquals(Support_Registry::singleton()->account->phone, $driver->findElement(WebDriverBy::cssSelector('.active_page #phone'))->getAttribute('value')); #todo parse phone
            PHPUnit_Framework_Assert::assertEquals(strtolower($this->account->country), strtolower($driver->findElement(WebDriverBy::cssSelector('.active_page #country option[selected="selected"]'))->getText()));

            PHPUnit_Framework_Assert::assertEquals($this->account->city, $driver->findElement(WebDriverBy::cssSelector('.active_page #city'))->getAttribute('value'));
            PHPUnit_Framework_Assert::assertEquals($this->account->street, $driver->findElement(WebDriverBy::cssSelector('.active_page #street'))->getAttribute('value'));
            PHPUnit_Framework_Assert::assertEquals($this->account->zip, $driver->findElement(WebDriverBy::cssSelector('.active_page #zip'))->getAttribute('value'));

            PHPUnit_Framework_Assert::assertEquals('deutsch', strtolower($driver->findElement(WebDriverBy::cssSelector('.active_page #language option[selected="selected"]'))->getText()));
        } else {
            Support_Helper::loginUnderAccount();

            $this->iGoToAccountPage();
            $this->iGoToPersonalDataTab();

            // check personal data
            PHPUnit_Framework_Assert::assertEquals($this->account->first_name, $driver->findElement(WebDriverBy::id('first_name'))->getAttribute('value'));
            PHPUnit_Framework_Assert::assertEquals($this->account->last_name, $driver->findElement(WebDriverBy::id('last_name'))->getAttribute('value'));
            PHPUnit_Framework_Assert::assertEquals($this->account->birth_tmstmp, $driver->findElement(WebDriverBy::id('birth_tmstmp'))->getAttribute('value'));
            PHPUnit_Framework_Assert::assertEquals($this->account->birth_place, $driver->findElement(WebDriverBy::id('birth_place'))->getAttribute('value'));
            PHPUnit_Framework_Assert::assertEquals(Support_Registry::singleton()->account->email, $driver->findElement(WebDriverBy::id('email'))->getAttribute('value'));
//            PHPUnit_Framework_Assert::assertEquals(Support_Registry::singleton()->account->phone, $driver->findElement(WebDriverBy::id('phone'))->getAttribute('value'));
            PHPUnit_Framework_Assert::assertEquals($this->account->city, $driver->findElement(WebDriverBy::id('city'))->getAttribute('value'));
            PHPUnit_Framework_Assert::assertEquals($this->account->street, $driver->findElement(WebDriverBy::id('street'))->getAttribute('value'));
            PHPUnit_Framework_Assert::assertEquals($this->account->zip, $driver->findElement(WebDriverBy::id('zip'))->getAttribute('value'));

            $actual_country = $this->driver->findElement(WebDriverBy::cssSelector('#countrySelectBoxItText'))->getText();
            $actual_language = $this->driver->findElement(WebDriverBy::cssSelector('#languageSelectBoxItText'))->getText();
//            exit;
            PHPUnit_Framework_Assert::assertEquals($this->account->country, $actual_country);
            PHPUnit_Framework_Assert::assertEquals(strtolower($this->account->language), strtolower($actual_language));
        }
    }

    /**
     * @When /^I change email$/
     */
    public function iChangeEmail()
    {
        $this->account->old_email = Support_Registry::singleton()->account->email;
        $this->account->email = 'new_' . Support_Registry::singleton()->account->email;
        Support_Registry::singleton()->account->email = $this->account->email;
        $driver = $this->driver;

        if (Support_Helper::isMobile()) {
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #first_name')));
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #street')));

            // just for can save
            $driver->findElement(WebDriverBy::cssSelector('.active_page #first_name'))->sendKeys('Change Email');
            $driver->findElement(WebDriverBy::cssSelector('.active_page #last_name'))->sendKeys('Behat Test');
            $driver->findElement(WebDriverBy::cssSelector('.active_page #birth_tmstmp'))->clear();
            $driver->findElement(WebDriverBy::cssSelector('.active_page #birth_tmstmp'))->sendKeys('12.12.1955');
            $driver->findElement(WebDriverBy::cssSelector('.active_page #country'))->sendKeys('Laplandia');
            $driver->findElement(WebDriverBy::cssSelector('.active_page #city'))->sendKeys('New City');
            $driver->findElement(WebDriverBy::cssSelector('.active_page #street'))->sendKeys('Something street');
            $driver->findElement(WebDriverBy::cssSelector('.active_page #zip'))->sendKeys('123456');

            // change email
            $driver->findElement(WebDriverBy::cssSelector('.active_page #email'))->clear();
            $driver->findElement(WebDriverBy::cssSelector('.active_page #email'))->sendKeys($this->account->email);
        } else {
            // just for can save
            $driver->findElement(WebDriverBy::id('first_name'))->sendKeys('Change Email');
            $driver->findElement(WebDriverBy::id('last_name'))->sendKeys('Behat Test');
            $driver->findElement(WebDriverBy::id('birth_tmstmp'))->clear();
            $driver->findElement(WebDriverBy::id('birth_tmstmp'))->sendKeys('12.12.1955');
            $this->driver->findElement(WebDriverBy::cssSelector('#countrySelectBoxItArrow'))->click();
            $this->driver->findElement(WebDriverBy::cssSelector('#countrySelectBoxItOptions .selectboxit-option[data-val="DE"]'))->click();
            usleep(250000);

            $driver->findElement(WebDriverBy::id('city'))->sendKeys('New City');
            $driver->findElement(WebDriverBy::id('street'))->sendKeys('Something street');
            $driver->findElement(WebDriverBy::id('zip'))->sendKeys('123456');

            // change email
            $driver->findElement(WebDriverBy::id('email'))->clear();
            $driver->findElement(WebDriverBy::id('email'))->sendKeys($this->account->email);
        }
    }

    /**
     * @Then /^I can login under new email$/
     */
    public function iCanLoginUnderNewEmail()
    {
        if (Support_Helper::isMobile()) {
            $this->ifIDoLogout();
            sleep(1);
            Support_GoPage::openMainPageOfMobileSite();
            Support_Helper::loginUnderAccountOnMobileSite();
        } else {
            $this->ifIDoLogout();
            Support_Helper::loginUnderAccount();
        }
    }

    /**
     * @Given /^I see new email in personal data tab$/
     */
    public function iSeeNewEmailInPersonalDataTab()
    {
        $this->iGoToAccountPage();
        $this->iGoToPersonalDataTab();


        if (Support_Helper::isMobile()) {
            $new_email = $this->driver->findElement(WebDriverBy::cssSelector('.active_page #email'))->getAttribute('value');
        } else {
            $new_email = $this->driver->findElement(WebDriverBy::id('email'))->getAttribute('value');
        }
        PHPUnit_Framework_Assert::assertEquals($this->account->email, $new_email);
        PHPUnit_Framework_Assert::assertNotEquals($this->account->old_email, $new_email);
    }

    /**
     * @Given /^I can not login under old email$/
     */
    public function iCanNotLoginUnderOldEmail()
    {
        try {
            if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.account-block .logout-link'), false)) {
                $this->ifIDoLogout();
            }
            if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#wwfooter .username a[onclick*="ac=mobile/player/index"]'), false)) {
                $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter .username a[onclick*="ac=mobile/player/index"]'))->click();

                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/player/index"]')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .linked[onclick*=".mobile_logout"]')));
                $this->driver->findElement(WebDriverBy::cssSelector('.active_page .linked[onclick*=".mobile_logout"]'))->click();

                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened .popup_btn_wrap .popup_btn')));
                $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .popup_btn_wrap .popup_btn'))->click();
                usleep(150000);
            }
        } catch (Exception $e) {}

        if (Support_Helper::isMobile()) {
            // click login button
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#wwfooter .footer-link[onclick*=".mobile_app.show_login"]')));
            $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter .footer-link[onclick*=".mobile_app.show_login"]'))->click();

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened #username')));
            $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened #username'))->sendKeys($this->account->old_email);
            $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened #password'))->sendKeys($this->account->password);

            $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .popup_btn_area .btn_popup[type="submit"]'))->click();
        } else {

            $this->driver->findElement(WebDriverBy::id('username'))->sendKeys($this->account->old_email);
            $this->driver->findElement(WebDriverBy::id('password'))->sendKeys($this->account->password);

            $this->driver->findElement(WebDriverBy::cssSelector('.sign-in-form button[type="submit"]'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content .popup-success-msg')));
            $error_text = $this->driver->findElement(WebDriverBy::cssSelector('#modal_content .popup-success-msg'))->getText();

            PHPUnit_Framework_Assert::assertContains('Invalid username or password', $error_text);

            $this->driver->findElement(WebDriverBy::id('custom_alert_btn'))->click();
            PHPUnit_Framework_Assert::assertTrue(Support_Registry::singleton()->elementPresent(WebDriverBy::id('username')));
            PHPUnit_Framework_Assert::assertTrue(Support_Registry::singleton()->elementPresent(WebDriverBy::id('password')));
            PHPUnit_Framework_Assert::assertTrue(Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('.account-block .logout-link')));

            $username_value = $this->driver->findElement(WebDriverBy::id('username'))->getAttribute('value');

            PHPUnit_Framework_Assert::assertEquals($this->account->old_email, $username_value);
        }
    }

    /**
     * @When /^I open confirm change email url$/
     */
    public function iOpenConfirmChangeEmailUrl()
    {
        $this->driver->get($this->email_confirmation_url);
    }

    /**
     * @When /^I change phone number$/
     */
    public function iChangePhoneNumber()
    {
        $driver = $this->driver;
        $this->new_mobile_phone = '';


        if (Support_Helper::isMobile()) {
            // just for can save
            $driver->findElement(WebDriverBy::id('first_name'))->sendKeys('Change Email');
            $driver->findElement(WebDriverBy::id('last_name'))->sendKeys('Behat Test');
            $driver->findElement(WebDriverBy::id('birth_tmstmp'))->clear();
            $driver->findElement(WebDriverBy::id('birth_tmstmp'))->sendKeys('12.12.1955');
            $driver->findElement(WebDriverBy::id('country'))->sendKeys('Laplandia');
            $driver->findElement(WebDriverBy::id('city'))->sendKeys('New City');
            $driver->findElement(WebDriverBy::id('street'))->sendKeys('Something street');
            $driver->findElement(WebDriverBy::id('zip'))->sendKeys('123456');

            // change phone
            $phone_template = $this->driver->findElement(WebDriverBy::cssSelector('.active_page .phone-plugin-wrapper .phone-plugin-input'))->getAttribute('placeholder');

            $this->new_mobile_phone = Support_Account::generatePhoneByTemplate($phone_template);
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page .phone-plugin-wrapper .phone-plugin-input'))->clear();
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page .phone-plugin-wrapper .phone-plugin-input'))->sendKeys($this->new_mobile_phone);
        } else {
            // just for can save
            $driver->findElement(WebDriverBy::id('first_name'))->sendKeys('Change Email');
            $driver->findElement(WebDriverBy::id('last_name'))->sendKeys('Behat Test');
            $driver->findElement(WebDriverBy::id('birth_tmstmp'))->clear();
            $driver->findElement(WebDriverBy::id('birth_tmstmp'))->sendKeys('12.12.1955');
            $this->driver->findElement(WebDriverBy::cssSelector('#countrySelectBoxItArrow'))->click();
            $this->driver->findElement(WebDriverBy::cssSelector('#countrySelectBoxItOptions .selectboxit-option[data-val="DE"]'))->click();
            usleep(250000);

            $driver->findElement(WebDriverBy::id('city'))->sendKeys('New City');
            $driver->findElement(WebDriverBy::id('street'))->sendKeys('Something street');
            $driver->findElement(WebDriverBy::id('zip'))->sendKeys('123456');

            $phone_template = $this->driver->findElement(WebDriverBy::cssSelector('.phone-plugin-wrapper .phone-plugin-input'))->getAttribute('placeholder');

            $this->new_mobile_phone = Support_Account::generatePhoneByTemplate($phone_template);
            $this->driver->findElement(WebDriverBy::cssSelector('.phone-plugin-wrapper .phone-plugin-input'))->clear();
            $this->driver->findElement(WebDriverBy::cssSelector('.phone-plugin-wrapper .phone-plugin-input'))->sendKeys($this->new_mobile_phone);
        }
    }

    /**
     * @Then /^I see "([^"]*)" form$/
     */
    public function iSeeForm($form_type)
    {
        if ($form_type === 'Confirm phone') {
            if (Support_Helper::isMobile()) {
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #mobile_confirm_key')));
            } else {
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('mobile_confirm_key')));
            }
        }
    }

    /**
     * @Then /^I can login under account$/
     */
    public function iCanLoginUnderAccount()
    {
        try {
            if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.popup-is-opened .popup_btn.btn'), false)) {
                $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .popup_btn.btn'))->click();
                usleep(250000);
            }
            if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#account-row .logout-link'), false)) {
                $this->ifIDoLogout();
            }
            if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#wwfooter .footer-icon-logout'), false)) {
                $this->ifIDoLogout();
            }
            if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#wwfooter .username .footer-link.logged-in'), false)) {
                if (Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('.active_page .linked[onclick*=".mobile_logout"]'), false)) {
                    $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter .username .footer-link.logged-in'))->click();
                }

                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .linked[onclick*=".mobile_logout"]')));
                $this->driver->findElement(WebDriverBy::cssSelector('.active_page .linked[onclick*=".mobile_logout"]'))->click();

                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened .msg_success')));
                $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .popup_btn.btn'))->click();
                usleep(250000);
            }
        } catch (Exception $e) {}

        if (Support_Helper::isMobile()) {
            Support_Helper::loginUnderAccountOnMobileSite();
        } else {
            Support_Helper::loginUnderAccount();
        }
    }

    /**
     * @Given /^I see new phone in personal data tab$/
     */
    public function iSeeNewPhoneInPersonalDataTab()
    {
        $this->iGoToAccountPage();
        $this->iGoToPersonalDataTab();

        if (Support_Helper::isMobile()) {
            $curr_phone = $this->driver->findElement(WebDriverBy::cssSelector('.active_page #phone'))->getAttribute('value');
        } else {
            $curr_phone = $this->driver->findElement(WebDriverBy::cssSelector('.phone-plugin-wrapper .phone-plugin-input'))->getAttribute('value');
        }
        $curr_phone = str_replace(array('+', '(', ')', '-', ' '), '', $curr_phone);

        PHPUnit_Framework_Assert::assertContains($this->new_mobile_phone, $curr_phone);
        PHPUnit_Framework_Assert::assertNotEquals($this->mobile_phone, $curr_phone);
    }

    /**
     * @Then /^I can not login under account$/
     */
    public function iCanNotLoginUnderAccount()
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::id('username')
        ));

        $this->driver->findElement(WebDriverBy::id('username'))->sendKeys($this->account->email);
        $this->driver->findElement(WebDriverBy::id('password'))->sendKeys($this->account->password);

        $error_text = $this->iClickLoginAndGetErrorText();

        PHPUnit_Framework_Assert::assertContains('Invalid username or password', $error_text);

        $this->driver->findElement(WebDriverBy::cssSelector('a.form-link[href*="restore_player_password"]'));
        $this->driver->findElement(WebDriverBy::id('username'));
        $username_value = $this->driver->findElement(WebDriverBy::id('username'))->getAttribute('value');
        PHPUnit_Framework_Assert::assertEquals($this->account->email, $username_value);
    }

    /**
     * @Given /^If i enable account in backoffice$/
     */
    public function ifIEnableAccountInBackoffice()
    {
        Support_AdminHelper::goToBackoffice();
        Support_AdminHelper::goToPlayerPage();
        Support_AdminHelper::enablePlayer(Support_Registry::singleton()->account->email);
    }

    /**
     * @When /^I choose "([^"]*)" functionality to block$/
     */
    public function iChooseFunctionalityToBlock($part_to_block)
    {
        $arToBlock = array(
            'betting' => 'functionality-betting',
            'livebetting' => 'functionality-livebetting',
            'head-to-head' => 'functionality-h2h',
            'deposit' => 'functionality-fund_account',
            );
        $id_to_block = $arToBlock[$part_to_block];

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id($id_to_block)));
        $this->driver->findElement(WebDriverBy::id($id_to_block))->click();
        usleep(10000);
    }

    /**
     * @Then /^I can "([^"]*)" use "([^"]*)" functionality$/
     */
    public function iCanUseFunctionality($can_not_use, $part)
    {
        sleep(2);
        $arParts = array(
            'place bets' => 'uCanPlaceBet',
            'livebetting' => 'uCanLivebetting',
            'head-to-head' => 'uCanH2H',
            'fill up deposit' => 'uCanFillUpDeposit',
            'add one more temporarily block' => 'uCanAddOneMoreBlock',
            'view tickets' => 'uCanViewTickets',
            'view games' => 'uCanViewGames',
        );
        $method = $arParts[$part];

        $positive_test = ($can_not_use === "not") ? false : true;
        $result = $this->$method($positive_test);

        PHPUnit_Framework_Assert::assertTrue($result);
    }

    /**
     * @Given /^If i remove all blocking from user in backoffice$/
     */
    public function ifIRemoveAllBlockingFromUserInBackoffice()
    {
        Support_AdminHelper::goToBackoffice();
        Support_AdminHelper::goToPlayerPage();
        Support_AdminHelper::removeBlocksFromPlayer(Support_Registry::singleton()->account->email);
    }

    /**
     * @When /^I click to "([^"]*)" link$/
     */
    public function iClickToLink($control)
    {
        if ($control === 'upload documents') {
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.main-info-block a[href*="upload_account_documents"]')
            ));
            $this->driver->findElement(WebDriverBy::cssSelector('.main-info-block a[href*="upload_account_documents"]'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('#modal_content #account_files_upload_area')
            ));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('#modal_content #LB_Player_Document_Form')
            ));
        } else {
            throw new Exception('Control "'.$control.'" not found');
        }

    }

    /**
     * @Given /^I fill upload documents form$/
     */
    public function iFillUploadDocumentsForm()
    {
        $driver = $this->driver;
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('file_categorySelectBoxItArrow')));
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('fileupload_file_id')));
        sleep(2);

        $driver->findElement(WebDriverBy::id('file_categorySelectBoxItArrow'))->click();
        usleep(250000);
        $driver->findElement(WebDriverBy::cssSelector('#file_categorySelectBoxItOptions .selectboxit-option[data-id="6"]'))->click();

        $driver->findElement(WebDriverBy::id('fileupload_file_id'))->sendKeys(Support_Configs::get()->UPLOAD_FILES_DIR.'example_pdf.pdf');
        sleep(2);
        usleep(12000);

        $driver->findElement(WebDriverBy::id('file_categorySelectBoxItArrow'))->click();
        usleep(250000);
        $driver->findElement(WebDriverBy::cssSelector('#file_categorySelectBoxItOptions .selectboxit-option[data-id="6"]'))->click();

        $driver->findElement(WebDriverBy::id('fileupload_file_id'))->sendKeys(Support_Configs::get()->UPLOAD_FILES_DIR.'upload_as_document.jpg');

        usleep(12000);
        sleep(2);
    }

    /**
     * @Given /^I submit upload document form$/
     */
    public function iSubmitUploadDocumentForm()
    {
        $this->driver->findElement(WebDriverBy::cssSelector('#modal_content .modal_body_close .fa-close'))->click();
        usleep(120000);
    }

    /**
     * @Then /^Document is uploaded$/
     */
    public function documentIsUploaded()
    {
        $this->iClickToLink('upload documents');

        $el_docs = $this->driver->findElements(WebDriverBy::cssSelector('#account_files_list_area a[onclick*="show_player_document"]'));
        $file_count = 0;

        foreach ($el_docs as $docs) {
            if ($docs->getText() === 'example_pdf.pdf') $file_count++;
            if ($docs->getText() === 'upload_as_document.jpg') $file_count++;
        }

        PHPUnit_Framework_Assert::assertEquals(2, $file_count);
    }

    /**
     * @Given /^Admin can see document in backoffice$/
     */
    public function adminCanSeeDocumentInBackoffice()
    {
        // go to backoffice
        Support_AdminHelper::goToBackoffice();
        $page_timestamp = Support_AdminHelper::goToPlayerPage();

        // find player
        $this->driver->findElement(WebDriverBy::id('simple_search_keyword'))->clear();
        $this->driver->findElement(WebDriverBy::id('simple_search_keyword'))->sendKeys(Support_Registry::singleton()->account->email);
        $this->driver->findElement(WebDriverBy::cssSelector('.detail-search-buttons button[type=submit]'))->click();

        $page_timestamp = self::waitForPageTimestampUpdated(WebDriverBy::cssSelector('div.t-page-gen-timestamp'), $page_timestamp, 'data-page-get');
        $this->driver->findElement(WebDriverBy::id('f_has_new_documents-1'))->click();
        self::waitForPageTimestampUpdated(WebDriverBy::cssSelector('div.t-page-gen-timestamp'), $page_timestamp, 'data-page-get');

        // wait timeout variables
        $current_time = microtime(true);
        $timeout = 60 * 1000;
        $driver = $this->driver;

        // store current windows data
        $current_window = $this->driver->getWindowHandle();
        $curr_window_count = count($this->driver->getWindowHandles());

        // open player
        $row_id = $this->driver->findElement(WebDriverBy::cssSelector('.pager_rows tr.h[id]'))->getAttribute('id');

        $el_show_docs = $this->driver->findElement(WebDriverBy::cssSelector('#'.$row_id.' .t-email a[href*="playerDetails"]'));
        $el_show_docs->click();

        // wait for new window
        while ($current_time + $timeout > microtime(true)) {
            $arWnd = $driver->getWindowHandles();

            if (count($arWnd) > $curr_window_count)
                break;
        }

        // select popUp window
        $new_window = end($arWnd);
        $driver->switchTo()->window($new_window);

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('admin_popup_content')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.player-overview-account .link-actions a[onclick*="switch_to_documents_tab"]')));
            sleep(1);

            //switch to document tab
            $this->driver->findElement(WebDriverBy::cssSelector('.player-overview-account .link-actions a[onclick*="switch_to_documents_tab"]'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Player_Admin_Documents_Pager')));

            usleep(120000);

            // find documents
            $el_docs = $this->driver->findElements(WebDriverBy::cssSelector('.pager_rows a[onclick*="show_player_document"]'));
            $file_count = 0;
            foreach ($el_docs as $docs) {
                if ($docs->getText() === 'example_pdf.pdf') $file_count++;
                if ($docs->getText() === 'upload_as_document.jpg') $file_count++;
            }
            // check doc count
            PHPUnit_Framework_Assert::assertEquals(2, $file_count);


        // close popup
        $driver->findElement(WebDriverBy::cssSelector('.hide_on_print button[onclick*="window.close"]'))->click();
        $driver->switchTo()->window($current_window);

        // wait for pop up close
        while ($current_time + $timeout > microtime(true)) {
            $arWnd = $this->driver->getWindowHandles();
            if (count($arWnd) === $curr_window_count)
                break;
        }

        // check that popUp is close
        PHPUnit_Framework_Assert::assertTrue($curr_window_count === count($arWnd));
    }

    /**
     * @Given /^I fill up deposit$/
     */
    public function iFillUpDeposit($amount = '10')
    {
        // go to account settings page
        Support_GoPage::openAccountSettingsPage();

        if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.account-buttons a[href*="player_add_payment_method"]'), false)) {
            // click to deposit button
            Support_Helper::clickButton('deposit');

            // select visa payment method
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('payment_amount')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.payment-method-list a[data-method="visa"]')
            ));

            usleep(120000);
            $this->driver->findElement(WebDriverBy::cssSelector('.payment-method-list a[data-method="visa"]'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector(
                '.payment-method-list a.active[data-method="visa"]'
            )));

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('payment_amount')));
            sleep(2);   //todo: just for stable work
            $elAmount = $this->driver->findElement(WebDriverBy::id('payment_amount'));
            $elAmount->clear();

            $this->driver->findElement(WebDriverBy::id('payment_amount'))->sendKeys($amount);

            // submit visa payment
            $this->driver->findElement(WebDriverBy::cssSelector('#LB_Player_PaymentMethod_Form_Visa button[type="submit"]'))->click();

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::id('st-pan-textfield')
            ));

            // fill visa payment data on secure payment side
            $this->driver->findElement(WebDriverBy::id('st-pan-textfield'))->sendKeys('4111111111111111');
            $this->driver->findElement(WebDriverBy::cssSelector('#st-expirymonth-dropdown option[value="12"]'))->click();
            $this->driver->findElement(WebDriverBy::cssSelector('#st-expiryyear-dropdown option[value="2030"]'))->click();
            $this->driver->findElement(WebDriverBy::id('st-securitycode-textfield'))->sendKeys('123');
            $this->driver->findElement(WebDriverBy::id('submit'))->click();

        } elseif (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.account-buttons a[href*="fund_account"]'), false)) {
            Support_Helper::clickButton('deposit');

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('selected_payment_method')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('payment_amount')));

            $this->driver->findElement(WebDriverBy::id('payment_amount'))->clear();
            $this->driver->findElement(WebDriverBy::id('payment_amount'))->sendKeys($amount);

            $this->driver->findElement(WebDriverBy::cssSelector('.form-row-buttons button[type="submit"]'))->click();
        }
        // wait for success
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('modal_content')));

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-content .popup-success-msg')));
        $this->driver->findElement(WebDriverBy::id('custom_alert_btn'))->click();
        usleep(120000);

    }

    /**
     * @When /^I go to transactions tab$/
     */
    public function iGoToTransactionsTab()
    {
        $this->driver->get(Support_Configs::get()->BASE_URL.'/index.php?ac=user/player/transactions');
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('result_search')));

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('#player_transactions_list_content td.amount')
        ));
    }

    /**
     * @Then /^I see record about fill up and withdraw money$/
     */
    public function iSeeRecordAboutFillUpDeposit()
    {
        $records = $this->driver->findElements(WebDriverBy::cssSelector('#player_transactions_list_content td.amount'));

        foreach ($records as $rec) {
            PHPUnit_Framework_Assert::assertEquals('10 EUR', $rec->getText());
        }
    }

    /**
     * @Given /^I mark user as advanced$/
     */
    public function iMarkUserAsAdvanced()
    {
        Support_AdminHelper::markUserAsAdvanced(Support_Registry::singleton()->account->email);
    }

    /**
     * @Given /^I withdraw money from deposit$/
     */
    public function iWithdrawMoneyFromDeposit()
    {
        Support_Helper::loginUnderAccount();

        // go to account settings page
        $this->driver->get(Support_Configs::get()->BASE_URL.'/index.php?ac=user/player/index');
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.main-info-block a[href*="close_account"]')));

        // open withdraw form
        $this->driver->findElement(WebDriverBy::cssSelector('.account-buttons a[href*="withdraw_funds"]'))->click();
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('#modal_content #payment_amount')
        ));

        $this->driver->findElement(WebDriverBy::cssSelector('#modal_content #payment_amount'))->clear();
        $this->driver->findElement(WebDriverBy::cssSelector('#modal_content #payment_amount'))->sendKeys(10);
        usleep(250000);

        // click to pay button
        $this->driver->findElement(WebDriverBy::cssSelector('.form-row-buttons button[type="submit"]'))->click();

        // fill the withdraw form
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('#modal_content div.popup-success-msg.alert-msg')
        ));

        $text_msg = $this->driver->findElement(WebDriverBy::cssSelector('#modal_content div.popup-success-msg.alert-msg'))->getText();
        PHPUnit_Framework_Assert::assertContains('successfully', $text_msg);
        PHPUnit_Framework_Assert::assertContains('created', $text_msg);

        // approve payout and return back to account


        $this->driver->findElement(WebDriverBy::cssSelector('#modal_content .form-row-buttons #custom_alert_btn'))->click();
        usleep(150000);
    }

    /**
     * @Given /^I set "([^"]*)" deposit limits as "([^"]*)"$/
     */
    public function iSetDepositLimitsAs($limit_type, $value)
    {
        // open deposit limit window
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.account-settings a[href*="set_account_deposit_limits"]')));
        $this->driver->findElement(WebDriverBy::cssSelector('.account-settings a[href*="set_account_deposit_limits"]'))->click();
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#LB_Player_DepositLimits_Form #max_deposit')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#LB_Player_DepositLimits_Form #monthly_limit')));

        if ($limit_type === 'Max') {
            $this->driver->findElement(WebDriverBy::cssSelector('#LB_Player_DepositLimits_Form #max_deposit'))->clear();
            $this->driver->findElement(WebDriverBy::cssSelector('#LB_Player_DepositLimits_Form #max_deposit'))->sendKeys($value);
        } elseif ($limit_type === 'Min') {
            $this->driver->findElement(WebDriverBy::cssSelector('#LB_Player_DepositLimits_Form #min_deposit'))->clear();
            $this->driver->findElement(WebDriverBy::cssSelector('#LB_Player_DepositLimits_Form #min_deposit'))->sendKeys($value);
        } elseif ($limit_type === 'Daily') {
            $this->driver->findElement(WebDriverBy::cssSelector('#LB_Player_DepositLimits_Form #daily_limit'))->clear();
            $this->driver->findElement(WebDriverBy::cssSelector('#LB_Player_DepositLimits_Form #daily_limit'))->sendKeys($value);
        } elseif ($limit_type === 'Weekly') {
            $this->driver->findElement(WebDriverBy::cssSelector('#LB_Player_DepositLimits_Form #weekly_limit'))->clear();
            $this->driver->findElement(WebDriverBy::cssSelector('#LB_Player_DepositLimits_Form #weekly_limit'))->sendKeys($value);
        } elseif ($limit_type === 'Monthly') {
            $this->driver->findElement(WebDriverBy::cssSelector('#LB_Player_DepositLimits_Form #monthly_limit'))->clear();
            $this->driver->findElement(WebDriverBy::cssSelector('#LB_Player_DepositLimits_Form #monthly_limit'))->sendKeys($value);
        }
        usleep(120000);
        $this->driver->findElement(WebDriverBy::cssSelector('#LB_Player_DepositLimits_Form button[type="submit"]'))->click();

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content .popup-success-msg')));
        $this->driver->findElement(WebDriverBy::cssSelector('#modal_content #custom_alert_btn'))->click();
        usleep(120000);

        $this->activateDepositLimit(Support_Registry::singleton()->account->email);
    }

    /**
     * @When /^I try to fill up deposit on "([^"]*)" EUR$/
     */
    public function iTryToFillUpDepositOnEUR($amount)
    {
        sleep(2);

        // go to account settings page
        Support_GoPage::openAccountSettingsPage();

        if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.account-buttons a[href*="player_add_payment_method"]'), false)) {
            // click to deposit button
            Support_Helper::clickButton('deposit');

            // select visa payment method
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('payment_amount')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.payment-method-list a[data-method="visa"]')
            ));

            usleep(120000);
            $this->driver->findElement(WebDriverBy::cssSelector('.payment-method-list a[data-method="visa"]'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector(
                '.payment-method-list a.active[data-method="visa"]'
            )));

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('payment_amount')));
            sleep(2);   //todo: just for stable work
            $elAmount = $this->driver->findElement(WebDriverBy::id('payment_amount'));
            $elAmount->clear();

            $this->driver->findElement(WebDriverBy::id('payment_amount'))->sendKeys($amount);

            // submit visa payment
            $this->driver->findElement(WebDriverBy::cssSelector('#LB_Player_PaymentMethod_Form_Visa button[type="submit"]'))->click();
        } elseif (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.account-buttons a[href*="fund_account"]'), false)) {
            // click to deposit button
            Support_Helper::clickButton('deposit');

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('payment_amount')));
            $this->driver->findElement(WebDriverBy::id('payment_amount'))->sendKeys($amount);

            $this->driver->findElement(WebDriverBy::cssSelector('.form-row-buttons button[type="submit"]'))->click();
        }
    }

    /**
     * @Then /^I see that "([^"]*)" limits is work$/
     */
    public function iSeeThatLimitsIsWork($limit)
    {
        if ($limit === "Max") {
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#t_item_errorlist li')));
            usleep(400000);
            $err_text = $this->driver->findElement(WebDriverBy::cssSelector('#t_item_errorlist li'))->getText();

            PHPUnit_Framework_Assert::assertEquals('Your personal max deposit limit is 30', $err_text);
        } elseif ($limit === "Min") {
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#t_item_errorlist li')));
            usleep(400000);
            $err_text = $this->driver->findElement(WebDriverBy::cssSelector('#t_item_errorlist li'))->getText();

            PHPUnit_Framework_Assert::assertEquals('Your personal min deposit limit is 5', $err_text);
        } elseif ($limit === "Daily") {
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#t_item_errorlist li')));
            usleep(400000);
            sleep(1);
            $err_text = $this->driver->findElement(WebDriverBy::cssSelector('#t_item_errorlist li'))->getText();

            PHPUnit_Framework_Assert::assertEquals('Your personal daily deposit limit has been exceeded.', $err_text);
        } elseif ($limit === "Weekly") {
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#t_item_errorlist li')));
            usleep(400000);
            $err_text = $this->driver->findElement(WebDriverBy::cssSelector('#t_item_errorlist li'))->getText();

            PHPUnit_Framework_Assert::assertEquals('Your personal weekly deposit limit has been exceeded.', $err_text);
        } elseif ($limit === "Monthly") {
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#t_item_errorlist li')));
            usleep(400000);
            sleep(1);
            $err_text = $this->driver->findElement(WebDriverBy::cssSelector('#t_item_errorlist li'))->getText();

            PHPUnit_Framework_Assert::assertEquals('Your personal monthly deposit limit has been exceeded.', $err_text);
        }
    }

    /**
     * @Given /^I if i fill up deposit on "([^"]*)" EUR$/
     */
    public function iIfIFillUpDepositOnEUR($amount)
    {
        $this->iFillUpDeposit($amount);
    }

    /**
     * @When /^I fill up deposit on "([^"]*)" EUR$/
     */
    public function iFillUpDepositOnEUR($amount)
    {
        $this->iFillUpDeposit($amount);
    }

    /**
     * @Given /^If i change transaction date on one "([^"]*)" back$/
     */
    public function ifIChangeTransactionDateOnOneBack($period)
    {
        $email = Support_Registry::singleton()->account->email;
        $result = Support_Helper::doCurlRequest(Support_Configs::get()->MANAGE_URL.'index.php?ac=selenium-test/change_player_transaction_date&email='.$email.'&move='.$period);
        PHPUnit_Framework_Assert::assertEquals('OK', $result);
    }

    /**
     * @Then /^I can fill up deposit on "([^"]*)" EUR$/
     */
    public function iCanFillUpDepositOnEUR($amount)
    {
        $this->iFillUpDeposit($amount);
    }

    /**
     * @Then /^I can not login under account because account closed$/
     */
    public function iCanNotLoginUnderAccountBecauseAccountClosed()
    {
        if (Support_Helper::isMobile()) {
            // check that user is not login
            Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#wwfooter .footer-link[onclick*="mobile/player/registration"]'));
            Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#wwfooter .footer-link[onclick*="mobile_app.show_login"]'));

            // try login
            $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter .footer-link[onclick*="mobile_app.show_login"]'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened #username')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened #password')));
            usleep(150000);

            $this->driver->findElement(WebDriverBy::cssSelector('.popup.popup-is-opened #username'))->clear();
            $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened #username'))->sendKeys(Support_Registry::singleton()->account->email);
            $this->driver->findElement(WebDriverBy::cssSelector('.popup.popup-is-opened #password'))->clear();
            $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened #password'))->sendKeys(Support_Registry::singleton()->account->password);

            $this->driver->findElement(WebDriverBy::cssSelector('.popup.popup-is-opened .popup_btn_area button[type="submit"]'))->click();

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened .form_errors div')));
            usleep(150000);

            $error_text = strtolower($this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .form_errors div'))->getText());

            PHPUnit_Framework_Assert::assertContains('account', $error_text);
            PHPUnit_Framework_Assert::assertContains('is', $error_text);
            PHPUnit_Framework_Assert::assertContains('closed', $error_text);
        } else {
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::id('username')
            ));

            $this->driver->findElement(WebDriverBy::id('username'))->sendKeys(Support_Registry::singleton()->account->email);
            $this->driver->findElement(WebDriverBy::id('password'))->sendKeys(Support_Registry::singleton()->account->password);

            $error_text = $this->iClickLoginAndGetErrorText();

            PHPUnit_Framework_Assert::assertContains('Account is closed!', $error_text);

            $this->driver->findElement(WebDriverBy::cssSelector('a.form-link[href*="restore_player_password"]'));
            $this->driver->findElement(WebDriverBy::id('username'));
            $username_value = $this->driver->findElement(WebDriverBy::id('username'))->getAttribute('value');
            PHPUnit_Framework_Assert::assertEquals(Support_Registry::singleton()->account->email, $username_value);
        }
    }

    private function uCanPlaceBet($positive = true) {
        if (Support_Helper::isMobile()) {
            Support_GoPage::game_mobile();
            Support_Mobile_TicketHelper::selectBetOn('each');

            // go to betslip
            $this->driver->findElement(WebDriverBy::cssSelector('.head-btn-betslip'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="mobile/ticket/detail"]')));

            // click to place bet button
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #btn_place_bet'))->click();

            // wait for success
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup.popup-is-opened .msg_success')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup.popup-is-opened .popup_btn_wrap .popup_btn.btn')));

            $msg_text = $this->driver->findElement(WebDriverBy::cssSelector('.popup.popup-is-opened .msg_success'))->getText();
            $msg_text = strtolower($msg_text);
            $this->driver->findElement(WebDriverBy::cssSelector('.popup.popup-is-opened .popup_btn_wrap .popup_btn.btn'))->click();

            // check message
            //You disabled possibility to betting until 05.12.15
            PHPUnit_Framework_Assert::assertContains('you', $msg_text);
            PHPUnit_Framework_Assert::assertContains('disabled', $msg_text);
            PHPUnit_Framework_Assert::assertContains('possibility', $msg_text);
            PHPUnit_Framework_Assert::assertContains('betting', $msg_text);
            PHPUnit_Framework_Assert::assertContains('until', $msg_text);

            // check ticket count
            $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter .username .footer-link.logged-in'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="mobile/player/index"]')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .linked[onclick*="urls.account_tickets"]')));
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page .linked[onclick*="urls.account_tickets"]'))->click();

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="mobile/ticket/list"]')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .ticket-item')));

            // check ticket count
            $ticket_count = $this->driver->findElements(WebDriverBy::cssSelector('.active_page .ticket-item'));
            PHPUnit_Framework_Assert::assertEquals(1, count($ticket_count));

            return true;
        } else {
            Support_GoPage::game();
            Support_TicketHelper::selectBetToGame('each');
            Support_TicketHelper::setStake('10');
            Support_Helper::clickButton('set');

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content .popup-success-msg')));


            if (!$positive) {
                $msg_text = strtolower($this->driver->findElement(WebDriverBy::cssSelector('#modal_content .popup-success-msg'))->getText());

                //You disabled possibility to betting until 05.12.15
                PHPUnit_Framework_Assert::assertContains('you', $msg_text);
                PHPUnit_Framework_Assert::assertContains('disabled', $msg_text);
                PHPUnit_Framework_Assert::assertContains('possibility', $msg_text);
                PHPUnit_Framework_Assert::assertContains('betting', $msg_text);
                PHPUnit_Framework_Assert::assertContains('until', $msg_text);

                $this->driver->findElement(WebDriverBy::cssSelector('#modal_content #custom_alert_btn'))->click();
                sleep(1);

                $ticket_count = (count($this->driver->findElements(WebDriverBy::cssSelector('#last_user_tickets div.ticket-list-item .ticket_list_stake'))));;
                if ($ticket_count > 1) return false;
                else return true;

            } else {
                $msg_text = strtolower($this->driver->findElement(WebDriverBy::cssSelector('#modal_content .popup-success-msg'))->getText());

                //Your bet was successfully accepted!
                PHPUnit_Framework_Assert::assertContains('your', $msg_text);
                PHPUnit_Framework_Assert::assertContains('bet', $msg_text);
                PHPUnit_Framework_Assert::assertContains('was', $msg_text);
                PHPUnit_Framework_Assert::assertContains('successfully', $msg_text);
                PHPUnit_Framework_Assert::assertContains('placed', $msg_text);

                $this->driver->findElement(WebDriverBy::cssSelector('#modal_content #custom_alert_btn'))->click();
                sleep(1);

                return Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#last_user_tickets div.ticket-list-item .ticket_list_stake'));
            }
        }
    }

    private function uCanLivebetting($positive = true)  //todo check that user realy can livebeting
    {
        if (Support_Helper::isMobile()) {
            // go to livebet page
            $this->driver->findElement(WebDriverBy::cssSelector('#head .head-btn[onclick*="show_sidebar"]'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.side-bar-list-item .linked[onclick*="mobile/livebet/match/index"]')));
            sleep(1);
            $this->driver->findElement(WebDriverBy::cssSelector('.side-bar-list-item .linked[onclick*="mobile/livebet/match/index"]'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/livebet/match/index"]')));
            Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('.active_page .e-odd-outcome'));
//            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="mobile/livebet/match/index"]')));
//            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .e-odd-outcome')));
//            // select odd
//            $this->driver->findElement(WebDriverBy::cssSelector('.active_page .e-odd-outcome[key="1"]'))->click();
//            Support_Wait::forTextInElement(WebDriverBy::cssSelector('#wwfooter div.selected_bets_amount'), '1');
//
//            // click to betslip button
//            $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter a.footer-icon-ticket'))->click();
//            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="mobile/ticket/detail"]')));
//
//            // click to place bet button
//            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #btn_place_bet'))->click();
//
//            // wait for success
//            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup.popup-is-opened .msg_success')));
//            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup.popup-is-opened .popup_btn_wrap .popup_btn.btn')));
//
//            $msg_text = $this->driver->findElement(WebDriverBy::cssSelector('.popup.popup-is-opened .msg_success'))->getText();
//            $msg_text = strtolower($msg_text);
//            $this->driver->findElement(WebDriverBy::cssSelector('.popup.popup-is-opened .popup_btn_wrap .popup_btn.btn'))->click();
//
//            // check message
//            //You disabled possibility to betting until 05.12.15
//            PHPUnit_Framework_Assert::assertContains('you', $msg_text);
//            PHPUnit_Framework_Assert::assertContains('disabled', $msg_text);
//            PHPUnit_Framework_Assert::assertContains('possibility', $msg_text);
//            PHPUnit_Framework_Assert::assertContains('betting', $msg_text);
//            PHPUnit_Framework_Assert::assertContains('until', $msg_text);
//
//            // check ticket count
//            $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter .footer-icon-account'))->click();
//            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/player/index"]')));
//            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page div.linked[onclick*="account_tickets"]')));
//            $this->driver->findElement(WebDriverBy::cssSelector('.active_page div.linked[onclick*="account_tickets"]'))->click();
//
//            // wait for ticket page to load
//            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/ticket/list"]')));
//            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .ticket-item')));
//
//            // check ticket count
//            $ticket_count = $this->driver->findElements(WebDriverBy::cssSelector('.active_page .ticket-item'));
//            PHPUnit_Framework_Assert::assertEquals(1, count($ticket_count));

            return true;
        } else {

            if (!$positive) {

                return Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('#head-menu a[href*="ac=user/lb/index"]'));

            } else {
                Support_GoPage::openTab('live');

                $ticket_tips = $this->driver->findElement(WebDriverBy::id('ticket_tips'))->getText();

                for ($i = 0; $i < 5; $i++) {
                    $game = $this->driver->findElements(WebDriverBy::cssSelector('.match_items tr.res[index]'))[$i];

                    $arOdds = $game->findElements(WebDriverBy::cssSelector('.odd div.clickable'));
                    $arCanPlace = array();
                    foreach ($arOdds as $odd) {
                        $attr = $odd->getAttribute('class');

                        if (strpos($attr, 'not_active') !== false) continue;
                        else $arCanPlace[] = $odd;
                    }

//            $bet_to = $arCanPlace[array_rand($arCanPlace)];
//            $bet_to->click(); //todo wait to fix
                    PHPUnit_Framework_Assert::assertTrue(false);

                    $ticket_tips = Support_Wait::forTextUpdated('#ticket_tips', $ticket_tips);
                }

                Support_TicketHelper::setStake('10');
                Support_Helper::clickButton('set');

                $msg_text = strtolower($this->driver->findElement(WebDriverBy::cssSelector('#modal_content .popup-success-msg'))->getText());

                //Your bet was successfully accepted!
                PHPUnit_Framework_Assert::assertContains('your', $msg_text);
                PHPUnit_Framework_Assert::assertContains('bet', $msg_text);
                PHPUnit_Framework_Assert::assertContains('was', $msg_text);
                PHPUnit_Framework_Assert::assertContains('successfully', $msg_text);
                PHPUnit_Framework_Assert::assertContains('accepted', $msg_text);

                $this->driver->findElement(WebDriverBy::cssSelector('#modal_content #custom_alert_btn'))->click();
                sleep(1);

                return Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#last_user_tickets div.ticket-list-item .ticket_list_stake'));
            }
        }
    }

    private function uCanH2H($positive = true) {
        return $positive;
    }

    private function uCanAddOneMoreBlock($positive = true) {
        if (Support_Helper::isMobile()) {
            $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter .username .footer-link.logged-in'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/player/index"]')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page div.linked[onclick*="account_tickets"]')));
            throw new Exception('wait for fix issue 3688');

            return Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('.active_page .linked[onclick*="urls.account_block_account"]'));
        } else {
            if ($positive) {
                throw new Exception('test does not implemented');
            } else {
                $this->driver->get(Support_Configs::get()->BASE_URL . 'index.php?ac=user/player/index');
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.main-info-block a[href*="upload_account_documents"]')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.main-info-block a[href*="close_account"]')));

                Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('.main-info-block a[href*="block_account"]'));
                Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('.main-info-block a[href*="set_account_deposit_limits"]'));
            }
        }
        return true;
    }

    private function uCanViewTickets($positive = true) {
        $driver = $this->driver;

        if (Support_Helper::isMobile()) {
            $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter .username .footer-link.logged-in'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/player/index"]')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .linked[onclick*="urls.account_tickets"]')));

            $this->driver->findElement(WebDriverBy::cssSelector('.active_page .linked[onclick*="urls.account_tickets"]'))->click();

            // wait for ticket page to load
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/ticket/list"]')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .ticket-item[onclick*="go_to_ticket"]')));

            // check view ticket
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page .ticket-item[onclick*="go_to_ticket"]'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened .ticketDetail')));

            // close ticket
            $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .popup_close'))->click();
            sleep(1);

            return true;
        } else {
            if ($positive) {
                // open user details form
                $this->driver->findElement(WebDriverBy::cssSelector('#last_user_tickets .ticket-list-item[onclick*="open_ticket_details"]'))->click();
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content .popup-ticket-details')));

                sleep(3);
                // check ticket info

                Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.ticketDetail'));
                Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.ticketBets'));
                Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.ticketFooter'));

                // close popup
                $driver->findElement(WebDriverBy::cssSelector('#modal_content .modal_body_close .fa-close'))->click();
                usleep(200000);

                return true;
            } else {
                throw new Exception('test does not implemented');
            }
        }
    }

    private function uCanViewGames($positive = true) {
        if (Support_Helper::isMobile()) {
            Support_GoPage::game_mobile();
            $gid = key(Support_Registry::singleton()->arGames[0]->getArGames());

            return Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.active_page li.e-item[key="'.$gid.'"]'));
        } else {
            if ($positive) {
                Support_GoPage::game();
                $gid = key(Support_Registry::singleton()->arGames[0]->getArGames());
                return Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('div[data-match="' . $gid . '"]'));
            } else {
                throw new Exception('test does not implemented');
            }
        }
    }

    private function uCanFillUpDeposit($positive = true) {
        if (Support_Helper::isMobile()) {

            $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter .username .footer-link.logged-in'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/player/index"]')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .linked[onclick*="urls.account_deposit"]')));

            $this->driver->navigate()->refresh();
            sleep(2);
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/player/index"]')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .linked[onclick*="urls.account_deposit"]')));

            throw new Exception('rewrite test after resolve issue #3686');
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page .linked[onclick*="urls.account_deposit"]'))->click();

            return Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('.active_page div.linked[onclick*="account_deposit"]'));
        } else {
            Support_Helper::openAccountPage();

            if (!$positive) {
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('.account-buttons a.disabled[href]')
                ));
                Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('.account-content a[href*="fund_account"]'));
                Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('.account-content a[href*="player_add_payment_method"]'));
                return true;

            } else {
                $this->driver->findElement(WebDriverBy::cssSelector('.account-content a[href*="player_add_payment_method"]'))->click();

                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('LB_Player_PaymentMethod_Form_Visa')));

                $this->driver->findElement(WebDriverBy::cssSelector('#add_payment_method .modal_body_close .fa-close'))->click();
                sleep(1);
                return true;
            }
        }
    }

    private function waitForPageTimestampUpdated(WebDriverBy $cssSelector, $page_timestamp, $timestamp_attribute = "data-page-get", $wait_timeout = 90)
    {
        for ($second = 0; $second < 90; $second++) {
            if ($second == $wait_timeout) {
                throw new Support_WWTimeoutException('Timeout: can\'t reset search filter');
            }
            try {

                $new_page_timestamp = $this->driver->findElement($cssSelector)->getAttribute($timestamp_attribute);
                if ($page_timestamp !== $new_page_timestamp) {
                    return $new_page_timestamp;
                }
            } catch (Exception $e) {
            }
            sleep(1);
        }
        return false;
    }

    private function activateDepositLimit($email)
    {
        $result = Support_Helper::doCurlRequest(Support_Configs::get()->MANAGE_URL.'index.php?ac=selenium-test/set_account_deposit_limits_date&email='.$email);
        PHPUnit_Framework_Assert::assertEquals('OK', $result);
    }

    private function iClickLoginAndGetErrorText()
    {
        $this->driver->findElement(WebDriverBy::cssSelector('.sign-in-form button[type="submit"]'))->click();
        $error_text = $this->driver->wait()->until(function(){
            if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#t_item_errorlist'), false)) {
                usleep(50000);
                return $this->driver->findElement(WebDriverBy::cssSelector('#t_item_errorlist li'))->getText();
            }
            if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#modal_content .popup-success-msg.alert-msg'), false)) {
                usleep(50000);
                return $this->driver->findElement(WebDriverBy::cssSelector('#modal_content .popup-success-msg.alert-msg'))->getText();
            };
        });

        return $error_text;
    }


}