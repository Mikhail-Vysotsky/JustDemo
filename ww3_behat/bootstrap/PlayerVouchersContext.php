<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 21.03.16
 * Time: 12:29
 */
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class PlayerVouchersContext implements Context, SnippetAcceptingContext {
    /**
     * @var RemoteWebDriver
     */
    private $driver;
    private $comment = '';
    private $income_file;

    function __construct()
    {
        $this->income_file = null;
        $this->comment = 'Behat test vouchers '.time();
        $this->driver = Support_Registry::singleton()->driver;
    }
    /**
     * @Given /^I fill Player Voucher Generation form$/
     */
    public function iFillPlayerVoucherGenerationForm()
    {
        Support_AdminHelper::fillVoucherForm('1', '50', 'EUR', $this->comment);
    }

    /**
     * @Given /^I submit player vouchers generation form$/
     */
    public function iSubmitPlayerVouchersGenerationForm()
    {
        Support_Registry::singleton()->page_timestamp = $this->driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp'))->getAttribute('data-page-get');
        $this->driver->findElement(WebDriverBy::cssSelector('#LB_Player_Admin_VoucherGeneration_Form #submit'))->click();
        Support_Registry::singleton()->page_timestamp = Support_Wait::forAttributeUpdated(WebDriverBy::cssSelector('.t-page-gen-timestamp'), 'data-page-get', Support_Registry::singleton()->page_timestamp);

        // find created voucher
        $this->driver->findElement(WebDriverBy::id('simple_search_keyword'))->sendKeys($this->comment);
        $this->driver->findElement(WebDriverBy::cssSelector('.detail-search-buttons button[type="submit"]'));
        Support_Registry::singleton()->page_timestamp = Support_Wait::forAttributeUpdated(WebDriverBy::cssSelector('.t-page-gen-timestamp'), 'data-page-get', Support_Registry::singleton()->page_timestamp);
        Support_Registry::singleton()->voucher_id = $this->driver->findElement(WebDriverBy::cssSelector('.pager_rows tr.h[id]'))->getAttribute('key');
    }

    /**
     * @Then /^I see and activate player vouchers$/
     */
    public function iSeeAndActivatePlayerVouchers()
    {
        $row_id = Support_Registry::singleton()->voucher_id;
        Support_Registry::singleton()->elementPresent(WebDriverBy::id('r'.$row_id));

        // download excel file
        Support_Registry::singleton()->arVouchers = Support_FilesHelper::getVouchersFromExcel(Support_Registry::singleton()->voucher_id);
        PHPUnit_Framework_Assert::assertTrue(count(Support_Registry::singleton()->arVouchers) > 0);
        Support_Registry::singleton()->page_timestamp = $this->driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp'))->getAttribute('data-page-get');

        // activate vouchers
        $this->driver->findElement(WebDriverBy::cssSelector('#r'.$row_id.' .player-voucher-generation-active-toggler'))->click();
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.v3-admin-popup-area .odds-edit-popup-form')));
        usleep(150000);
        $this->driver->findElement(WebDriverBy::cssSelector('.v3-admin-popup-area .odds-edit-popup-outcomes-form-buttons .save-button'))->click();

        Support_Registry::singleton()->page_timestamp = Support_Wait::forAdminPageLoaded('.t-page-gen-timestamp', 'data-page-get', Support_Registry::singleton()->page_timestamp);
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#r'.$row_id.' .active-1')));
        usleep(150000);
    }

    /**
     * @Given /^I can activate player vouchers$/
     */
    public function iCanActivatePlayerVouchers()
    {
        Support_Registry::singleton()->account = new Support_AccountClass();
        Support_Registry::singleton()->account->createNewAccount(false, '0', 'EUR');

        Support_Helper::loginUnderAccount();
        Support_GoPage::pageAccountSettings();
        Support_Helper::redeemVoucher(Support_Registry::singleton()->arVouchers[0]['number']);

        $this->driver->navigate()->refresh();
        sleep(1);
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.account-block .balance span')));
        usleep(120000);
    }

    /**
     * @Given /^I see not active player vouchers$/
     */
    public function iSeeNotActivePlayerVouchers()
    {
        $row_id = Support_Registry::singleton()->voucher_id;
        Support_Registry::singleton()->elementPresent(WebDriverBy::id('r'.$row_id));

        // download excel file
        Support_Registry::singleton()->arVouchers = Support_FilesHelper::getVouchersFromExcel(Support_Registry::singleton()->voucher_id);
        PHPUnit_Framework_Assert::assertTrue(count(Support_Registry::singleton()->arVouchers) > 0);

        // check that voucher not active
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#r'.$row_id.' .active-0')));
    }

    /**
     * @Given /^I enter and submit voucher code$/
     */
    public function iEnterAndSubmitVoucherCode()
    {
        $this->driver->findElement(WebDriverBy::id('code'))->click();
        usleep(150000);
        $this->driver->findElement(WebDriverBy::id('code'))->sendKeys(Support_Registry::singleton()->arVouchers[0]['number']);
        $this->driver->findElement(WebDriverBy::cssSelector('#modal_content button[type="submit"]'))->click();
        usleep(150000);
    }

    /**
     * @Then /^I see error message about voucher is locked$/
     */
    public function iSeeErrorMessageAboutVoucherIsLocked()
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-content #t_item_errorlist li')));
        $error_text = $this->driver->findElement(WebDriverBy::cssSelector('#t_item_errorlist li'))->getText();

        PHPUnit_Framework_Assert::assertEquals('The voucher is locked', $error_text);

        $this->driver->findElement(WebDriverBy::cssSelector('#modal_content .fa-close'))->click();
        usleep(455000);

        $this->driver->navigate()->refresh();
        sleep(1);
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.account-block .balance span')));
        usleep(120000);
    }

    /**
     * @Given /^I have not active voucher$/
     */
    public function iHaveNotActiveVoucher()
    {
        Support_AdminHelper::loginAsAdmin();
        Support_GoPage::openAdminPage("player vouchers");

        if (Support_Registry::singleton()->elementPresent(WebDriverBy::id('reset'))) {
            $this->driver->findElement(WebDriverBy::id('reset'))->click();
            Support_Registry::singleton()->page_timestamp = Support_Wait::forAdminPageLoaded('.t-page-gen-timestamp', 'data-page-get', Support_Registry::singleton()->page_timestamp);
        }

        Support_Helper::clickButton("Generate new Player Vouchers");
        $this->iFillPlayerVoucherGenerationForm();
        $this->iSubmitPlayerVouchersGenerationForm();
        $this->iSeeNotActivePlayerVouchers();
    }

    /**
     * @Given /^I have active voucher$/
     */
    public function iHaveActiveVoucher()
    {
        Support_AdminHelper::loginAsAdmin();
        Support_GoPage::openAdminPage("player vouchers");

        if (Support_Registry::singleton()->elementPresent(WebDriverBy::id('reset'))) {
            $this->driver->findElement(WebDriverBy::id('reset'))->click();
            Support_Registry::singleton()->page_timestamp = Support_Wait::forAdminPageLoaded('.t-page-gen-timestamp', 'data-page-get', Support_Registry::singleton()->page_timestamp);
        }

        Support_Helper::clickButton("Generate new Player Vouchers");
        Support_AdminHelper::fillVoucherForm('1', '50', 'EUR', $this->comment);
        $this->iSubmitPlayerVouchersGenerationForm();
        $this->iSeeAndActivatePlayerVouchers();
    }

    /**
     * @Then /^I can redeem voucher$/
     */
    public function iCanRedeemVoucher()
    {
        Support_Helper::redeemVoucher(Support_Registry::singleton()->arVouchers[0]['number']);

        $this->driver->navigate()->refresh();
        sleep(1);
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.account-block .balance span')));
        usleep(120000);
    }

    /**
     * @Given /^I have \'([^\']*)\' active voucher$/
     */
    public function iHaveActiveVoucher1($amount)
    {
        Support_AdminHelper::loginAsAdmin();
        Support_GoPage::openAdminPage("player vouchers");

        if (Support_Registry::singleton()->elementPresent(WebDriverBy::id('reset'))) {
            $this->driver->findElement(WebDriverBy::id('reset'))->click();
            Support_Registry::singleton()->page_timestamp = Support_Wait::forAdminPageLoaded('.t-page-gen-timestamp', 'data-page-get', Support_Registry::singleton()->page_timestamp);
        }

        Support_Helper::clickButton("Generate new Player Vouchers");
        Support_AdminHelper::fillVoucherForm($amount, '50', 'EUR', $this->comment);

        $this->iSubmitPlayerVouchersGenerationForm();
        $this->iSeeAndActivatePlayerVouchers();
    }

    /**
     * @Then /^I can redeem remaining two vouchers$/
     */
    public function iCanRedeemRemainingTwoVouchers()
    {
        Support_Helper::redeemVoucher(Support_Registry::singleton()->arVouchers[1]['number']);

        $this->driver->navigate()->refresh();
        sleep(1);
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.account-block .balance span')));
        usleep(120000);

        //-------------------------------------------------------------

        Support_Helper::redeemVoucher(Support_Registry::singleton()->arVouchers[2]['number']);

        $this->driver->navigate()->refresh();
        sleep(1);
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.account-block .balance span')));
        usleep(120000);

    }

    /**
     * @When /^I login under second account$/
     */
    public function iLoginUnderSecondAccount()
    {
        Support_Helper::logout();
        Support_Helper::loginUnderAccount(Support_Registry::singleton()->account_second->email, Support_Registry::singleton()->account_second->password);

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('page-footer')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.account-block a.logout-link')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.account-block a[href*="ac=user/player/settings"]')));
    }

    /**
     * @Then /^I see error message about voucher already been used$/
     */
    public function iSeeErrorMessageAboutVoucherAlreadyBeenUsed()
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-content #t_item_errorlist li')));
        $error_text = $this->driver->findElement(WebDriverBy::cssSelector('#t_item_errorlist li'))->getText();

        PHPUnit_Framework_Assert::assertEquals('The voucher has already been used', $error_text);

        $this->driver->findElement(WebDriverBy::cssSelector('#modal_content .fa-close'))->click();
        usleep(455000);

        $this->driver->navigate()->refresh();
        sleep(1);
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.account-block .balance span')));
        usleep(120000);
    }

    /**
     * @When /^I have one more account with "([^"]*)" "([^"]*)" in balance$/
     */
    public function iHaveOneMoreAccountWithInBalance($amount, $currency)
    {
        Support_Helper::iGetOneMoreAccount(false, $amount, $currency);
    }

    /**
     * @Given /^I redeem voucher$/
     */
    public function iRedeemVoucher()
    {
        $this->iCanRedeemVoucher();
    }

    /**
     * @Then /^I can not redeem voucher again$/
     */
    public function iCanNotRedeemVoucherAgain()
    {
        return true; // just dummy
    }

    /**
     * @When /^I enter comment as search keyword$/
     */
    public function iEnterCommentAsSearchKeyword()
    {
        $this->findVoucherByComment();
    }

    /**
     * @Then /^I found this vouchers$/
     */
    public function iFoundThisVouchers()
    {
        Support_Registry::singleton()->elementPresent(WebDriverBy::id('r'.Support_Registry::singleton()->voucher_id));
    }

    /**
     * @Given /^I find test voucher$/
     */
    public function iFindTestVoucher()
    {
        $this->findVoucherByComment();
        Support_Registry::singleton()->elementPresent(WebDriverBy::id('r'.Support_Registry::singleton()->voucher_id));
    }

    /**
     * @When /^I click to "([^"]*)" link in voucher row$/
     */
    public function iClickToLinkInVoucherRow($target)
    {
        // wait timeout variables
        $current_time = microtime(true);
        $timeout = 60 * 1000;

        $outDir = Support_Configs::get()->BROWSER_DOWNLOAD_DIR;
        $f_count = Support_FilesHelper::scanDir($outDir);

        switch ($target) {
            case 'PDF':
                $this->driver->findElement(WebDriverBy::cssSelector('#r'.Support_Registry::singleton()->voucher_id.' .t-pdf-view'))->click();
                $this->income_file = Support_Wait::waitForAnyNewFile($outDir, count($f_count));
                break;
            case 'Excel':
                // just dummy: i check this on test where i get voucher data
//                $this->driver->findElement(WebDriverBy::cssSelector('#r'.Support_Registry::singleton()->voucher_id.' .t-excel-view'))->click();
//                sleep(1);
//                $this->driver->findElement(WebDriverBy::cssSelector('#r'.Support_Registry::singleton()->voucher_id.' .t-excel-view'))->click();
//                $this->income_file = Support_Wait::waitForAnyNewFile($outDir, count($f_count));
                break;
            case 'Vouchers':
                // store current windows data
                Support_Registry::singleton()->current_window = $current_window = $this->driver->getWindowHandle();
                $curr_window_count = count($this->driver->getWindowHandles());

                $this->driver->findElement(WebDriverBy::cssSelector('#r'.Support_Registry::singleton()->voucher_id.' .t-view-cards'))->click();

                // wait for new window
                while ($current_time + $timeout > microtime(true)) {
                    $arWnd = $this->driver->getWindowHandles();

                    if (count($arWnd) > $curr_window_count)
                        break;
                }

                // select popUp window
                $new_window = end($arWnd);
                $this->driver->switchTo()->window($new_window);
                break;
            default:
                throw new Exception("target to click no found in test");
        }
    }

    /**
     * @Then /^I download PDF document with vouchers$/
     */
    public function iDownloadPDFDocumentWithVouchers()
    {
            return true; // just dummy
    }

    /**
     * @Then /^I get excel document with vouchers$/
     */
    public function iGetExcelDocumentWithVouchers()
    {
        return true; // just dummy
    }

    /**
     * @Then /^I see popUp with each voucher detail$/
     */
    public function iSeePopUpWithEachVoucherDetail()
    {
        $arVouchers = $this->driver->findElements(WebDriverBy::cssSelector('.pager_rows tr.h'));

        $current_time = microtime(true);
        $timeout = 60 * 1000;

        $curr_window_count = count($this->driver->getWindowHandles());
        $this->driver->findElement(WebDriverBy::cssSelector('#admin_popup_content button[onclick*="window.close"]'))->click();

        // wait for pop up close
        while ($current_time + $timeout > microtime(true)) {
            $arWnd = $this->driver->getWindowHandles();
            if (count($arWnd) === $curr_window_count-1)
                break;
        }
        $this->driver->switchTo()->window(Support_Registry::singleton()->current_window);

        PHPUnit_Framework_Assert::assertEquals(3, count($arVouchers));

    }

    private function findVoucherByComment()
    {
        Support_Registry::singleton()->page_timestamp = $this->driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp'))->getAttribute('data-page-get');

        $this->driver->findElement(WebDriverBy::id('simple_search_keyword'))->clear();
        $this->driver->findElement(WebDriverBy::id('simple_search_keyword'))->sendKeys($this->comment);

        $this->driver->findElement(WebDriverBy::cssSelector('.detail-search-buttons button[type="submit"]'))->click();
        Support_Registry::singleton()->page_timestamp = Support_Wait::forAdminPageLoaded('.t-page-gen-timestamp', 'data-page-get', Support_Registry::singleton()->page_timestamp);
    }
}