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



class PaymentSystemContext implements Context, SnippetAcceptingContext {
    private $driver;
    private $account;

    function __construct()
    {
        $this->driver = Support_Registry::singleton()->driver;
        $this->account = Support_Registry::singleton()->account;
    }

    /**
     * @Given /^I select "([^"]*)" payment method$/
     */
    public function iSelectPaymentMethod($method)
    {
        Support_PaymentHelperClass::selectPaymentMethod($method);
    }

    /**
     * @Given /^I set payment amount "([^"]*)"$/
     */
    public function iSetPaymentAmount($amount)
    {
        $this->driver->findElement(WebDriverBy::id('payment_amount'))->sendKeys($amount);
    }

    /**
     * @Given /^I fill "([^"]*)" credit card data on secure trading side$/
     */
    public function iFillCreditCardDataOnSecureTradingSide($card)
    {
        Support_PaymentHelperClass::fillPaymentData($card);

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('modal_content')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('custom_alert_btn')));
        $this->driver->findElement(WebDriverBy::id('custom_alert_btn'))->click();
        usleep(120000);
    }

    /**
     * @Then /^I see that deposit is "([^"]*)"$/
     */
    public function iSeeThatDepositIs($expected_balance)
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.account-block span.balance')));
        Support_Wait::forTextInElement(WebDriverBy::cssSelector('.account-block span.balance'), $expected_balance);
        
        $balance = $this->driver->findElement(WebDriverBy::cssSelector('.account-block span.balance'))->getText();
        PHPUnit_Framework_Assert::assertEquals($expected_balance, $balance);
    }

    /**
     * @Given /^"([^"]*)" payment method stored$/
     */
    public function paymentMethodStored($method)
    {
        Support_Helper::clickButton('deposit in account');
        $stored_payment = $this->driver->findElement(WebDriverBy::cssSelector('.player-payment-method-list .card_name'))->getText();

        if ($method === "visa") {
            PHPUnit_Framework_Assert::assertEquals($stored_payment, '411111######1111');
        } elseif ($method === "mastercard") {
            PHPUnit_Framework_Assert::assertEquals($stored_payment, '555544######1111');
        } elseif ($method === "skrill") {
            PHPUnit_Framework_Assert::assertEquals($stored_payment, Support_Configs::get()->skrill_email);
        }
    }

    /**
     * @Given /^If i do one more payment on "([^"]*)" "([^"]*)"$/
     */
    public function ifIDoOneMorePaymentOn($amount, $currency)
    {
        $this->iSetPaymentAmount($amount);
        $this->driver->findElement(WebDriverBy::cssSelector('#LB_Player_PaymentTransaction_Form button[type="submit"]'))->click();
    }

    /**
     * @Then /^I not redirect to secure trading$/
     */
    public function iNotRedirectToSecureTrading()
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('modal_content')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('custom_alert_btn')));
        $this->driver->findElement(WebDriverBy::id('custom_alert_btn'))->click();
        usleep(120000);
    }

    /**
     * @Given /^I submit "([^"]*)" payment$/
     */
    public function iSubmitPayment($method)
    {
        Support_PaymentHelperClass::submitPayment($method);

    }

    /**
     * @Given /^I fill "([^"]*)" credit card data on payment system side$/
     */
    public function iFillCreditCardDataOnPaymentSystemSide($method)
    {
        Support_PaymentHelperClass::fillSkrillPayData();

        // check success message
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('modal_content')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('custom_alert_btn')));
        $this->driver->findElement(WebDriverBy::id('custom_alert_btn'))->click();
        usleep(120000);

    }

    /**
     * @Given /^I fill "([^"]*)" credit card data on paypal side$/
     */
    public function iFillCreditCardDataOnPaypalSide($method)
    {
        Support_PaymentHelperClass::fillPaypal();

        // check success message
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('modal_content')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('custom_alert_btn')));
        $this->driver->findElement(WebDriverBy::id('custom_alert_btn'))->click();
        usleep(120000);
    }

    /**
     * @Given /^I fill up deposit via "([^"]*)" payment method$/
     */
    public function iFillUpDepositViaPaymentMethod($payment_method)
    {
        // login under account and fill up deposit
        Support_Helper::loginUnderAccount();
        Support_Helper::clickButton('deposit');

        Support_PaymentHelperClass::selectPaymentMethod($payment_method);

        Support_Registry::singleton()->driver->findElement(WebDriverBy::id('payment_amount'))->sendKeys('10');

        $this->iSubmitPayment($payment_method);
        if ($payment_method === "skrill") {
            $this->iFillCreditCardDataOnPaymentSystemSide($payment_method);
        } else {
            $this->iFillCreditCardDataOnSecureTradingSide($payment_method);
        }
        $this->iSeeThatDepositIs("10 EUR");
        $this->paymentMethodStored($payment_method);
    }

    /**
     * @Given /^I set "([^"]*)" "([^"]*)" to withdraw amount$/
     */
    public function iSetToWithdrawAmount($amount, $currency)
    {
        $this->driver->findElement(WebDriverBy::id('payment_amount'))->clear();
        $this->driver->findElement(WebDriverBy::id('payment_amount'))->sendKeys($amount);

        $text_commision = $this->driver->findElement(WebDriverBy::id('payout_commission'))->getText();
        PHPUnit_Framework_Assert::assertContains($currency, $text_commision);
    }

    /**
     * @Then /^I see successful message$/
     */
    public function iSeeSuccessfulMessage()
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('#modal_content div.popup-success-msg.alert-msg')
        ));

        $text_msg = $this->driver->findElement(WebDriverBy::cssSelector('#modal_content div.popup-success-msg.alert-msg'))->getText();
        PHPUnit_Framework_Assert::assertContains('successfully', $text_msg);
        PHPUnit_Framework_Assert::assertContains('completed', $text_msg);
        PHPUnit_Framework_Assert::assertContains('transaction number', $text_msg);

        $this->driver->findElement(WebDriverBy::cssSelector('#modal_content .form-row-buttons #custom_alert_btn'))->click();
        $current_balance = str_replace(' EUR', '', $this->driver->findElement(WebDriverBy::cssSelector('.account-block span.balance'))->getText());
        PHPUnit_Framework_Assert::assertLessThan('10', $current_balance);

    }

    /**
     * @Given /^I fill "([^"]*)" credit card data on paysafe payment side$/
     */
    public function iFillCreditCardDataOnPaysafePaymentSide()
    {
        Support_PaymentHelperClass::fillPaysafe();

        // check success message
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('modal_content')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('custom_alert_btn')));
        $this->driver->findElement(WebDriverBy::id('custom_alert_btn'))->click();
        usleep(120000);
    }

    /**
     * @Then /^I see payout information message$/
     */
    public function iSeePayoutInformationMessage()
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content .popup-success-msg')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content #custom_alert_btn')));

        $this->driver->findElement(WebDriverBy::cssSelector('#modal_content #custom_alert_btn'))->click();
        sleep(1);
    }

    /**
     * @Given /^I go to "([^"]*)" page$/
     */
    public function iGoToPage($page)
    {
        Support_GoPage::openAdminPage($page);
    }

    /**
     * @Given /^I find and approve test payout$/
     */
    public function iFindAndApproveTestPayout()
    {
//        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('list__LB_Player_Admin_Tester_Pager')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.t-page-gen-timestamp.t-page-gen-timestamp-LB_Player_Admin_PayoutClaim_Pager')));

        // reset search filter
        $page_timestamp = $this->driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp'))->getAttribute('data-page-get');
        $this->driver->findElement(WebDriverBy::id('reset'))->click();
        $page_timestamp = Support_Wait::forPageTimestampUpdated($page_timestamp);

        // enter email as search keyword
        $this->driver->findElement(WebDriverBy::id('simple_search_keyword'))->sendKeys(Support_Registry::singleton()->account->email);
        $this->driver->findElement(WebDriverBy::id('f_deleted-1'))->click();
        $this->driver->findElement(WebDriverBy::id('f_approved-1'))->click();
        $this->driver->findElement(WebDriverBy::cssSelector('.detail-search-buttons button[type="submit"]'))->click();

//         wait for payout found
        $page_timestamp = Support_Wait::forPageTimestampUpdated($page_timestamp);
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.pager_rows tr.h[key]')));
        $found_email = $this->driver->findElement(WebDriverBy::cssSelector('.pager_rows tr.h[key] .t-email a'))->getText();

        PHPUnit_Framework_Assert::assertEquals(Support_Registry::singleton()->account->email, $found_email);
        $row_id = $this->driver->findElement(WebDriverBy::cssSelector('.pager_rows tr.h[key]'))->getAttribute('id');

        // approve payout
        $this->driver->findElement(WebDriverBy::cssSelector('#'.$row_id.' .player-payout-claims-approved-toggler.active-0'))->click();
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.v3-admin-popup-area .admin-form-control')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.v3-admin-popup-area .save-button')));

        sleep(1);

        $this->driver->findElement(WebDriverBy::cssSelector('.v3-admin-popup-area .admin-form-control'))->sendKeys('Behat test: approve payout. Timestamp: '.time());
        sleep(1);
        $this->driver->findElement(WebDriverBy::cssSelector('.v3-admin-popup-area .save-button'))->click();
        Support_Wait::forPageTimestampUpdated($page_timestamp);

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#'.$row_id.' .player-payout-claims-approved-toggler.active-1')));

        Support_AdminHelper::logoutFromBackoffice();
    }

    /**
     * @Given /^admin try to approve payout and see error$/
     */
    public function adminTryToApprovePayoutAndSeeError()
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.t-page-gen-timestamp.t-page-gen-timestamp-LB_Player_Admin_PayoutClaim_Pager')));

        // reset search filter
        $page_timestamp = $this->driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp'))->getAttribute('data-page-get');
        $this->driver->findElement(WebDriverBy::id('reset'))->click();
        $page_timestamp = Support_Wait::forPageTimestampUpdated($page_timestamp);

        // enter email as search keyword
        $this->driver->findElement(WebDriverBy::id('simple_search_keyword'))->sendKeys(Support_Registry::singleton()->account->email);
        $this->driver->findElement(WebDriverBy::cssSelector('.detail-search-buttons button[type="submit"]'))->click();

        // wait for payout found
        Support_Wait::forPageTimestampUpdated($page_timestamp);
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.pager_rows tr.h[key]')));
        $found_email = $this->driver->findElement(WebDriverBy::cssSelector('.pager_rows tr.h[key] .t-email a'))->getText();

        PHPUnit_Framework_Assert::assertEquals(Support_Registry::singleton()->account->email, $found_email);
        $row_id = $this->driver->findElement(WebDriverBy::cssSelector('.pager_rows tr.h[key]'))->getAttribute('id');

        // approve payout
        $this->driver->findElement(WebDriverBy::cssSelector('#'.$row_id.' .player-payout-claims-approved-toggler.active-0'))->click();
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.v3-admin-popup-area .admin-form-control')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.v3-admin-popup-area .save-button')));

        $this->driver->findElement(WebDriverBy::cssSelector('.v3-admin-popup-area .admin-form-control'))->sendKeys('Behat test: approve payout. Timestamp: '.time());
        usleep(500000);
        $this->driver->findElement(WebDriverBy::cssSelector('.v3-admin-popup-area .save-button'))->click();
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.v3-admin-popup-area .ui-state-error ul li')));

        $err_message = strtolower($this->driver->findElement(WebDriverBy::cssSelector('.v3-admin-popup-area .ui-state-error ul li'))->getText());
        PHPUnit_Framework_Assert::assertEquals('payout amount exceeds your credit', $err_message);
        Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#'.$row_id.' .player-payout-claims-approved-toggler.active-0'));

    }

    /**
     * @Given /^I approve payout in backoffice$/
     */
    public function iApprovePayoutInBackoffice()
    {
        Support_AdminHelper::loginAsAdmin();

        Support_GoPage::openAdminPage('payout claims');
        $this->iFindAndApproveTestPayout();


        // return back to user account
        Support_Helper::loginUnderAccount();

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('page-footer')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.account-block a.logout-link')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.account-block a[href*="ac=user/player/settings"]')));
    }

    /**
     * @When /^I fill bank transfer form and set amount "([^"]*)"$/
     */
    public function iFillBankTransferFormAndSetAmount($amount)
    {
        if (!Support_Helper::isMobile()) {
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content #account_name')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content #iban')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content #bic')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content #payment_amount')));
            usleep(150000);

            $this->driver->findElement(WebDriverBy::cssSelector("#modal_content #account_name"))->sendKeys('behat first');
            $this->driver->findElement(WebDriverBy::cssSelector("#modal_content #iban"))->sendKeys('CH9300762011623852957');
            $this->driver->findElement(WebDriverBy::cssSelector("#modal_content #bic"))->sendKeys('rbabch22955');
            $this->driver->findElement(WebDriverBy::cssSelector("#modal_content #payment_amount"))->sendKeys($amount);
            usleep(150000);
        } else {
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #account_name')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #iban')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #bic')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #payment_amount')));
            usleep(150000);

            $this->driver->findElement(WebDriverBy::cssSelector(".active_page #account_name"))->sendKeys('behat first');
            $this->driver->findElement(WebDriverBy::cssSelector(".active_page #iban"))->sendKeys('CH9300762011623852957');
            $this->driver->findElement(WebDriverBy::cssSelector(".active_page #bic"))->sendKeys('rbabch22955');
            $this->driver->findElement(WebDriverBy::cssSelector(".active_page #payment_amount"))->sendKeys($amount);
            usleep(150000);
        }

    }

    /**
     * @Given /^I submit payout$/
     */
    public function iSubmitPayout()
    {
        if (!Support_Helper::isMobile()) {
            $this->driver->findElement(WebDriverBy::cssSelector("#modal_content #payment_method_btn"))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content .popup-success-msg')));
            usleep(150000);
        } else {
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #payment_method_btn'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened .msg_success')));
        }
    }

    /**
     * @Then /^I see successful claim for payment create$/
     */
    public function iSeeSuccessfulClaimForPaymentCreate()
    {
        if (!Support_Helper::isMobile()) {
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content .popup-success-msg')));
            usleep(150000);

            $msg_text = $this->driver->findElement(WebDriverBy::cssSelector('#modal_content .popup-success-msg'))->getText();

            // close popup
            $this->driver->findElement(WebDriverBy::cssSelector('#modal_content #custom_alert_btn'))->click();
            usleep(150000);
        } else {
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened .msg_success')));
            usleep(150000);

            $msg_text = $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .msg_success'))->getText();

            // close popup
            $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .popup_btn'))->click();
            usleep(150000);
        }

        $msg_text = strtolower($msg_text);
        $expected_text = strtolower("A claim for payment has been successfully created. Once it is approved, you will get the money.");

        PHPUnit_Framework_Assert::assertEquals($expected_text, $msg_text);
    }

    /**
     * @When /^I open withdraw form again$/
     */
    public function iOpenWithdrawFormAgain()
    {
        if (!Support_Helper::isMobile()) {
            Support_Helper::openAccountPage();

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.account-buttons a[href*="withdraw_funds"]')));
            usleep(150000);
            $this->driver->findElement(WebDriverBy::cssSelector('.account-buttons a[href*="withdraw_funds"]'))->click();

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('#modal_content a[href*="player_add_bank_transfer"]')
            ));
        } else {
            Support_Helper::openAccountPage();

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .li.linked[onclick*="urls.account_withdraw"]')));
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page .li.linked[onclick*="urls.account_withdraw"]'))->click();

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="mobile/player/withdraw/index"]')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #payment_amount')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page button[onclick*="mobile/player/withdraw/bank_transfer"]')));
        }
    }


    /**
     * @Then /^I no need fill bank transfer form again$/
     */
    public function iNoNeedFillBankTransferFormAgain()
    {
        if (!Support_Helper::isMobile()) {
            Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#selected_payment_method .public-name'));
        }
    }

    /**
     * @When /^I submit payout via saved method$/
     */
    public function iSubmitPayoutViaSavedMethod()
    {
        if (!Support_Helper::isMobile()) {
            $this->driver->findElement(WebDriverBy::cssSelector("#LB_Player_PaymentTransaction_Form button[type=submit]"))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content .popup-success-msg')));
            usleep(150000);
        } else {
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page button#btn_place_bet[type=submit]'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened .msg_success')));
            usleep(150000);
        }

    }
}