<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 05.02.16
 * Time: 12:35
 */
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class MobilePaymentsContext implements Context, SnippetAcceptingContext {
    private $driver;

    function __construct()
    {
        $this->driver = Support_Registry::singleton()->driver;
    }

    /**
     * @Given /^I enter "([^"]*)" credit card data on secure trading side$/
     */
    public function iEnterCreditCardDataOnSecureTradingSide($card_type)
    {
        Support_PaymentHelperClass::fillPaymentData( $card_type , true);

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="success"]')));
//        sleep(9999999);
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .popup_btn_area div.btn.submitter')));
        $this->driver->findElement(WebDriverBy::cssSelector(('.active_page .popup_btn_area div.btn.submitter')))->click();
        sleep(2);

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/player/deposit/index"]')));
    }

    /**
     * @Given /^I click to "([^"]*)" payment method$/
     */
    public function iClickToPaymentMethod($pay_method)
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .t-'.$pay_method)));
        switch ($pay_method) {
            case 'visa':
                $this->driver->findElement(WebDriverBy::cssSelector('.active_page .t-visa div.linked[onclick]'))->click();
                break;
            case 'mastercard':
                $this->driver->findElement(WebDriverBy::cssSelector('.active_page .t-mastercard div.linked[onclick]'))->click();
                break;
            case 'skrill':
                $this->driver->findElement(WebDriverBy::cssSelector('.active_page .t-skrill div.linked[onclick]'))->click();
                break;
            case 'paypal':
                $this->driver->findElement(WebDriverBy::cssSelector('.active_page .t-paypal div.linked[onclick]'))->click();
                break;
            case 'paysafe':
                $this->driver->findElement(WebDriverBy::cssSelector('.active_page .t-paysafe div.linked[onclick]'))->click();
                break;
            default:
                throw new Exception('Can\'t find payment method in tests');
        }
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="method='.$pay_method.'"]')));
    }

    /**
     * @Given /^I set amount "([^"]*)" EUR$/
     */
    public function iSetAmountEUR($amount)
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #payment_amount')));
        $this->driver->findElement(WebDriverBy::cssSelector('.active_page #payment_amount'))->clear();
        $this->driver->findElement(WebDriverBy::cssSelector('.active_page #payment_amount'))->sendKeys($amount);
    }

    /**
     * @Given /^I click to pay button$/
     */
    public function iClickToPayButton()
    {
        $this->driver->findElement(WebDriverBy::cssSelector('.active_page #btn_place_bet'))->click();
        $this->driver->wait()->until(function(){
            if (Support_Registry::singleton()->elementPresent(WebDriverBy::id('pan'), false)) {
                return true;
            }
            if (Support_Registry::singleton()->elementPresent(WebDriverBy::id('pan'), false)) {
                return true;
            }
            if (Support_Registry::singleton()->elementPresent(WebDriverBy::id('login_emaildiv'), false)) {
                return true;
            }
            if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#firstName'), false)) {
                return true;
            }
            if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#classicPin-addPinField'), false)) {
                return true;
            }
            if (Support_Registry::singleton()->elementPresent(WebDriverBy::id('password'), false)) {
                return true;
            }
        });
        usleep(120000);
    }

    /**
     * @Then /^I see that my deposit is "([^"]*)"$/
     */
    public function iSeeThatMyDepositIs($amount)
    {
        if (Support_Helper::isMobile()) {
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#wwfooter .balance')));
            $balance = $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter .balance .no-format-balance'))->getText();
            $currency = $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter .balance .user-currency'))->getText();
            $user_deposit = "$balance $currency";
        } else {
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#account-row .balance span')));
            $user_deposit = $this->driver->findElement(WebDriverBy::cssSelector('#account-row .balance span'))->getText();
        }
        PHPUnit_Framework_Assert::assertEquals($amount, $user_deposit);
    }

    /**
     * @Given /^"([^"]*)" payment method saved$/
     */
    public function paymentMethodSaved($pay_method)
    {
        $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter .username a.footer-link.logged-in'))->click();
        $this->driver->findElement(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/player/index"]'));

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page div.li.linked[onclick*="urls.account_deposit"]')));
        $this->driver->findElement(WebDriverBy::cssSelector('.active_page div.li.linked[onclick*="urls.account_deposit"]'))->click();


        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/player/deposit/index"]')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #payment_amount')));

        $stored_method = $this->driver->findElement(WebDriverBy::cssSelector('.active_page .player-payment-method-list label'))->getText();
        switch ($pay_method) {
            case 'visa':
                PHPUnit_Framework_Assert::assertEquals('Visa', $stored_method);
                break;
            case 'mastercard':
                PHPUnit_Framework_Assert::assertEquals('Mastercard', $stored_method);
                break;
            case 'skrill':
                PHPUnit_Framework_Assert::assertEquals('Skrill', $stored_method);
                break;
            case 'paysafe':
                PHPUnit_Framework_Assert::assertEquals('Paysafe', $stored_method);
                break;
            default:
                throw new Exception('No paymethod found');
        }
    }

    /**
     * @Given /^If i try one more payment on "([^"]*)" "([^"]*)"$/
     */
    public function ifITryOneMorePaymentOn($amount, $currency)
    {
        $this->driver->findElement(WebDriverBy::cssSelector('.active_page #payment_amount'))->clear();
        $this->driver->findElement(WebDriverBy::cssSelector('.active_page #payment_amount'))->sendKeys($amount);

        $this->driver->findElement(WebDriverBy::cssSelector('.active_page #btn_place_bet'))->click();
    }

    /**
     * @Then /^Secure trading proccess payment without any redirect$/
     */
    public function secureTradingProccessPaymentWithoutAnyRedirect()
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="deposit/success"]')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page div.btn.submitter[onclick]')));
        $this->driver->findElement(WebDriverBy::cssSelector('.active_page div.btn.submitter[onclick]'))->click();

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #payment_amount')));

    }

    /**
     * @Given /^I set email$/
     */
    public function iSetEmail()
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #email')));
        $this->driver->findElement(WebDriverBy::cssSelector('.active_page #email'))->clear();
        $this->driver->findElement(WebDriverBy::cssSelector('.active_page #email'))->sendKeys(Support_Registry::singleton()->account->email);
    }

    /**
     * @Given /^I enter "([^"]*)" auth data on skrill payment side$/
     */
    public function iEnterAuthDataOnSkrillPaymentSide($arg1)
    {
        Support_PaymentHelperClass::fillSkrillPayData();

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="success"]')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .popup_btn_area div.btn.submitter')));
        $this->driver->findElement(WebDriverBy::cssSelector(('.active_page .popup_btn_area div.btn.submitter')))->click();
        sleep(2);

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/player/deposit/index"]')));

    }

    /**
     * @Given /^I enter "([^"]*)" credit card data on paypal side$/
     */
    public function iEnterCreditCardDataOnPaypalSide($arg1)
    {
        Support_PaymentHelperClass::fillPaypal();

        // check success message
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('modal_content')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('custom_alert_btn')));
        $this->driver->findElement(WebDriverBy::id('custom_alert_btn'))->click();
        usleep(120000);
    }

    /**
     * @Given /^I fill "([^"]*)" credit card data on paysafe side$/
     */
    public function iFillCreditCardDataOnPaysafeSide($arg1)
    {
        Support_PaymentHelperClass::fillPaysafe();

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="mobile/player/deposit/success"]')));
//        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .btn.submitter')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .popup_btn_area div.btn.submitter')));
        usleep(120000);

//        $this->driver->findElement(WebDriverBy::cssSelector('.active_page .btn.submitter'))->click();
        $this->driver->findElement(WebDriverBy::cssSelector('.active_page .popup_btn_area div.btn.submitter'))->click();
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page li.t-paysafe')));
    }

    /**
     * @Given /^I fill up deposit via "([^"]*)" payment method on mobile site$/
     */
    public function iFillUpDepositViaPaymentMethodOnMobileSite($pay_method)
    {
        Support_GoPage::openMainPageOfMobileSite();
        Support_Helper::loginUnderAccountOnMobileSite();
        Support_GoPage::paymentMethodsPage_mobile();
        $this->iClickToPaymentMethod($pay_method);

        $this->iSetAmountEUR('10');
        if ($pay_method === 'skrill') {
            $this->iSetEmail();
        }
        $this->iClickToPayButton();
        if ($pay_method === 'skrill') {
            $this->iEnterAuthDataOnSkrillPaymentSide('skrill');
        } else {
            $this->iEnterCreditCardDataOnSecureTradingSide($pay_method);
        }

//        $this->iSeeThatMyDepositIs('10 EUR');
    }

    /**
     * @Given /^I go to withdraw page$/
     */
    public function iGoToWithdrawPage()
    {
        if (Support_Helper::isMobile()) {
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#wwfooter .username a.footer-link.logged-in')));
            $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter .username a.footer-link.logged-in'))->click();

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="mobile/player/index"]')));

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .li.linked[onclick*="urls.account_withdraw"]')));
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page .li.linked[onclick*="urls.account_withdraw"]'))->click();

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="mobile/player/withdraw/index"]')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #payment_amount')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #btn_place_bet')));
        } else {    // is regular
            sleep(9999);
        }
    }

    /**
     * @When /^I set "([^"]*)" EUR to withdraw$/
     */
    public function iSetEURToWithdraw($amount)
    {
        if (Support_Helper::isMobile()) {
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #payment_amount')));
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #payment_amount'))->clear();
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #payment_amount'))->sendKeys($amount);
            usleep(120000);
        } else {
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('payment_amount')));
            $this->driver->findElement(WebDriverBy::id('payment_amount'))->clear();
            $this->driver->findElement(WebDriverBy::id('payment_amount'))->sendKeys($amount);
            usleep(120000);
        }
    }

    /**
     * @Given /^I click on "([^"]*)" button$/
     */
    public function iClickOnButton($target)
    {
        if ($target === 'pay') {
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #btn_place_bet')));
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #btn_place_bet'))->click();
        } else if ($target === "Change password" && Support_Helper::isMobile()) {
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page div[onclick*="account_change_password"]')));
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page div[onclick*="account_change_password"]'))->click();

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/player/settings/change_password"]')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #password2')));
        } else {
            throw new Exception ('no target found in tests');
        }
    }

    /**
     * @Then /^I see successful alert$/
     */
    public function iSeeSuccessfulAlert()
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened .msg_success')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened .popup_btn_wrap .popup_btn')));

        $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .popup_btn_wrap .popup_btn'))->click();
        usleep(120000);
    }
}