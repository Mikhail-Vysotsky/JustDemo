<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 04.02.16
 * Time: 16:44
 */
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class MobilePagesContext implements Context, SnippetAcceptingContext {
    private $driver;

    function __construct()
    {
        $this->driver = Support_Registry::singleton()->driver;
    }
    /**
     * @Given /^I on mobile main page$/
     */
    public function iOnMobileMainPage()
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="'.Support_Configs::get()->MOBILE_BASE_URL.'"]')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .t-about')));
//        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .t-account')));
//        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .t-priv-pol')));
//        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .t-bonus-rules')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page a[href*="/?_webversion=1"]')));
    }

    /**
     * @When /^I click to "([^"]*)"$/
     */
    public function iClickTo($target)
    {
        switch ($target) {
            case 'go to website':
                $this->driver->findElement(WebDriverBy::cssSelector('.active_page a[href*="/?_webversion=1"]'))->click();
                break;
//            case 'account':
//                $this->driver->findElement(WebDriverBy::cssSelector('.active_page .t-account div[onclick]'))->click();
//                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/player/index"]')));
//                break;
            case 'sign in':
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#wwfooter a.footer-link[onclick*=".mobile_app.show_login"]')));
                $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter a.footer-link[onclick*=".mobile_app.show_login"]'))->click();
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened #password')));
                break;
            case 'sign up':
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened .t-register[onclick*="ac=mobile/player/registration"]')));
                $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .t-register[onclick*="ac=mobile/player/registration"]'))->click();

                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/player/registration"]')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #password')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #password2')));
                usleep(250000);
                break;
            case 'edit personal data':
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/player/index"]')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page div[onclick*="account_personal_data"]')));
                $this->driver->findElement(WebDriverBy::cssSelector('.active_page div[onclick*="account_personal_data"]'))->click();
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/player/settings"]')));
                break;
            case 'redeem voucher':
                $this->driver->findElement(WebDriverBy::cssSelector('.account-settings a[href*="redeem_voucher"]'))->click();
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('LB_Player_Voucher_RedeemForm')));
                break;
            default:
                throw new Exception("target to click no found in test");
        }
    }

    /**
     * @Then /^I navigate to regular wir\-wetten site$/
     */
    public function iNavigateToRegularWirWettenSite()
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('ticket_stake_buttons_area')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#head-menu a[href*="v3/sports/index"]')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('ticket_bets_area')));
    }

    /**
     * @When /^I click to "([^"]*)" button in footer$/
     */
    public function iClickToButtonInFooter($target)
    {
        switch ($target) {
            case 'home':
                $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter a.footer-icon-home'))->click();
                break;
            case 'sports':
                $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter a.footer-icon-bets'))->click();
                break;
            case 'LIVE':
                $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter a.footer-icon-live'))->click();
                break;
            case 'Betslip':
                $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter a.footer-icon-ticket'))->click();
                break;
            case 'betslip':
                $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter a.footer-icon-ticket'))->click();
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="mobile/ticket/detail"]')));
                break;
            case 'Account':
                $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter .username a.footer-link.logged-in'))->click();
                break;
            default:
                throw new Exception("target to click no found in test");
        }
    }

    /**
     * @Then /^Mobile "([^"]*)" page is opened$/
     */
    public function mobilePageIsOpened($expected_page)
    {
        switch ($expected_page) {
            case 'Information':
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/page/list"]')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page div.linked[onclick*="ac=mobile/page/info"]')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page div.linked[onclick*="ac=mobile/page/privacy-policy"]')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page div.linked[onclick*="ac=mobile/page/bonus-rules"]')));
                break;
            case 'Sports':
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div.active_page[pageurl*="ac=mobile/sport/index"]')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div.active_page .jt_list')));
                PHPUnit_Framework_Assert::assertEquals('Sports', trim($this->driver->findElement(WebDriverBy::id('head-title'))->getText()));
                PHPUnit_Framework_Assert::assertContains('ac=mobile/sport/index', $this->driver->getCurrentURL());
                break;
            case 'LIVE':
            case 'Live':
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div.active_page[pageurl*="ac=mobile/livebet/match/index"]')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div.active_page .jt_live')));
                PHPUnit_Framework_Assert::assertEquals('Soccer', trim($this->driver->findElement(WebDriverBy::id('head'))->getText()));
                PHPUnit_Framework_Assert::assertContains('ac=mobile/livebet/match/index', $this->driver->getCurrentURL());
                break;
            case 'Betslip':
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div.active_page[pageurl*="ac=mobile/ticket/detail"]')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div.active_page #btn_place_bet')));
                PHPUnit_Framework_Assert::assertEquals('Betslip', trim($this->driver->findElement(WebDriverBy::id('head'))->getText()));
                PHPUnit_Framework_Assert::assertContains('ac=mobile/ticket/detail', $this->driver->getCurrentURL());
                break;
            case 'Account':
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div.active_page[pageurl*="ac=mobile/player/index"]')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page div.linked[onclick*="urls.account_tickets"]')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page div.linked[onclick*="urls.account_deposit"]')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page div.linked[onclick*="urls.account_withdraw"]')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page div.linked[onclick*="urls.account_personal_data"]')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page div.linked[onclick*="urls.account_change_password"]')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page div.linked[onclick*="urls.account_block_account"]')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page div.linked[onclick*="urls.account_deposit_limits"]')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page div.linked[onclick*="urls.account_transactions"]')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page div.linked[onclick*="close_account"]')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page div.linked[onclick*="mobile_logout"]')));
                PHPUnit_Framework_Assert::assertEquals('Account settings', trim($this->driver->findElement(WebDriverBy::id('head-title'))->getText()));
                PHPUnit_Framework_Assert::assertContains('ac=mobile/player/index', $this->driver->getCurrentURL());
                break;
            case 'Results':
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div.active_page[pageurl*="ac=mobile/search/search"]')));
//                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div.active_page table.odd_tabs')));
                PHPUnit_Framework_Assert::assertEquals('wir-wetten', strtolower(trim($this->driver->findElement(WebDriverBy::id('head'))->getText())));
                PHPUnit_Framework_Assert::assertContains('ac=mobile/search/search', $this->driver->getCurrentURL());
                break;
            case 'Today':
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div.active_page[pageurl*="ac=mobile/sport/index&filter=today"]')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div.active_page .jt_area.jt_list')));
                PHPUnit_Framework_Assert::assertEquals('Today: Sports', trim($this->driver->findElement(WebDriverBy::id('head'))->getText()));
                PHPUnit_Framework_Assert::assertContains('ac=mobile/sport/index&filter=today', $this->driver->getCurrentURL());
                break;

            default:
                throw new Exception('No target page found');
        }
    }

    /**
     * @When /^I choose "([^"]*)" language$/
     */
    public function iChooseLanguage($lang)
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .language')));

        // open language box
        $this->driver->findElement(WebDriverBy::cssSelector('.active_page .language div[onclick*="languages_box"]'))->click();
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.select-language-box div[onclick*="\''.$lang.'\'"]')));
        sleep(1);
        $this->driver->findElement(WebDriverBy::cssSelector('.select-language-box div[onclick*="\''.$lang.'\'"]'))->click();
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .language.'.$lang)));
    }

    /**
     * @Then /^Site translate to "([^"]*)"$/
     */
    public function siteTranslateTo($lang)
    {
        sleep(3);
        $l_info = strtolower(trim($this->driver->findElement(WebDriverBy::cssSelector('.active_page .t-about .linked'))->getText()));
        $l_register = strtolower(trim($this->driver->findElement(WebDriverBy::cssSelector('#wwfooter a[onclick*="ac=mobile/player/registration"]'))->getText()));
        $l_sign_in = strtolower(trim($this->driver->findElement(WebDriverBy::cssSelector('#wwfooter a[onclick*=".mobile_app.show_login"]'))->getText()));

        switch ($lang) {
            case 'en':
                PHPUnit_Framework_Assert::assertEquals('information', $l_info);
                PHPUnit_Framework_Assert::assertEquals('register', $l_register);
                PHPUnit_Framework_Assert::assertEquals('sign in', $l_sign_in);
                break;
            case 'de':
                PHPUnit_Framework_Assert::assertEquals('information', $l_info);
                PHPUnit_Framework_Assert::assertEquals('registrieren', $l_register);
                PHPUnit_Framework_Assert::assertEquals('einloggen', $l_sign_in);
                break;
            default:
                throw new Exception('No translate for expected languages '.$lang);

        }
    }

    /**
     * @When /^I click to "([^"]*)" in information section$/
     */
    public function iClickToInInformationSection($target)
    {
        switch ($target) {
            case 'About Us':
                $this->driver->findElement(WebDriverBy::cssSelector('.active_page div[onclick*="ac=mobile/page/info"]'))->click();
                break;
            case 'Account':
                $this->driver->findElement(WebDriverBy::cssSelector('.active_page .t-account div[onclick*="ac=mobile/player/index"]'))->click();
                break;
            case 'Privacy police':
                $this->driver->findElement(WebDriverBy::cssSelector('.active_page div[onclick*="ac=mobile/page/privacy-policy"]'))->click();
                break;
            case 'Bonus rules':
                $this->driver->findElement(WebDriverBy::cssSelector('.active_page div[onclick*="ac=mobile/page/bonus-rules"]'))->click();
                break;
            case 'Register':
                $this->driver->findElement(WebDriverBy::cssSelector('.active_page .t-register div[onclick*="ac=mobile/payment/index"]'))->click();
                break;
            default:
                throw new Exception("target to click no found in test");
        }
    }

    /**
     * @Then /^Mobile "([^"]*)" information is opened$/
     */
    public function mobileInformationIsOpened($expected_page)
    {
        switch ($expected_page) {
            case 'About Us':
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div.active_page[pageurl*="ac=mobile/page/info"]')));
//                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div.active_page .t-loaded-about')));
                PHPUnit_Framework_Assert::assertEquals('About us', trim($this->driver->findElement(WebDriverBy::id('head-title'))->getText()));
                PHPUnit_Framework_Assert::assertContains('ac=mobile/page/info', $this->driver->getCurrentURL());
                break;
            case 'Account':
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div.active_page[pageurl*="ac=mobile/player/index"]')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div.active_page .t-buy-new')));
                PHPUnit_Framework_Assert::assertEquals('Account settings', $this->driver->findElement(WebDriverBy::id('head'))->getText());
                PHPUnit_Framework_Assert::assertContains('ac=mobile/player/index', $this->driver->getCurrentURL());
                break;
            case 'Privacy police':
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div.active_page[pageurl*="ac=mobile/page/privacy-policy"]')));
//                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div.active_page .t-loaded-privacy')));
                sleep(1);
                var_dump(trim($this->driver->findElement(WebDriverBy::id('head-title'))->getText())); 
                PHPUnit_Framework_Assert::assertEquals('Privacy policy', trim($this->driver->findElement(WebDriverBy::id('head-title'))->getText()));
                PHPUnit_Framework_Assert::assertContains('ac=mobile/page/privacy-policy', $this->driver->getCurrentURL());
                break;
            case 'Bonus rules':
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div.active_page[pageurl*="ac=mobile/page/bonus-rules"]')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div.active_page .t-loaded-bonus')));
                PHPUnit_Framework_Assert::assertEquals('Bonus rules', trim($this->driver->findElement(WebDriverBy::id('head'))->getText()));
                PHPUnit_Framework_Assert::assertContains('ac=mobile/page/bonus-rules', $this->driver->getCurrentURL());
                break;
            case 'Register':
                throw new Exception('BROKEN REGISTER PAGE');
                break;

            default:
                throw new Exception('No target page found');
        }
    }

    /**
     * @When /^I click to "([^"]*)" in bets section$/
     */
    public function iClickToInBetsSection($target)
    {
        switch ($target) {
            case 'Live':
                $this->driver->findElement(WebDriverBy::cssSelector('.active_page .t-live div[onclick*="ac=mobile/livebet/match/index"]'))->click();
                break;
            case 'Sports':
                $this->driver->findElement(WebDriverBy::cssSelector('.active_page .t-sport div[onclick*="ac=mobile/sport/index"]'))->click();
                break;
            case 'Results':
                $this->driver->findElement(WebDriverBy::cssSelector('.active_page .t-results div[onclick*="ac=mobile/search/search"]'))->click();
                break;
            default:
                throw new Exception("target to click no found in test");
        }
    }

    /**
     * @Given /^I click to "([^"]*)" button in header$/
     */
    public function iClickToButtonInHeader($target)
    {
        switch ($target) {
            case 'information':
                $this->driver->findElement(WebDriverBy::cssSelector('.active_page .t-about div[onclick*="ac=mobile/page/list"]'))->click();
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/page/list"]')));
                break;
            case 'Betslip':
                $this->driver->findElement(WebDriverBy::cssSelector('#head .head-btn-betslip[onclick*="ac=mobile/ticket/detail"]'))->click();
                break;
            case 'betslip':
                $this->driver->findElement(WebDriverBy::cssSelector('#head .head-btn-betslip[onclick*="ac=mobile/ticket/detail"]'))->click();
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="mobile/ticket/detail"]')));
                break;
            default:
                throw new Exception("target to click no found in test");
        }
    }

    /**
     * @When /^I click to "([^"]*)" button on page$/
     */
    public function iClickToButtonOnPage($target)
    {
        switch ($target) {
            case 'sports':
                $this->driver->findElement(WebDriverBy::cssSelector('.active_page .t-sport div[onclick*="ac=mobile/sport/index"]'))->click();
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/sport/index"]')));
                break;
            case 'LIVE':
                $this->driver->findElement(WebDriverBy::cssSelector('.active_page .t-live div[onclick*="ac=mobile/livebet/match/"]'))->click();
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/livebet/match/index"]')));
                break;
            case 'results':
                $this->driver->findElement(WebDriverBy::cssSelector('.active_page .t-results div[onclick*="ac=mobile/search/search"]'))->click();
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/search/search"]')));
                break;
            default:
                throw new Exception("target to click no found in test");
        }
    }

    /**
     * @Given /^I open information page$/
     */
    public function iOpenInformationPage()
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .t-about div[onclick*="ac=mobile/page/list"]')));
        $this->driver->findElement(WebDriverBy::cssSelector('.active_page .t-about div[onclick*="ac=mobile/page/list"]'))->click();
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/page/list"]')));

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page div[onclick*="ac=mobile/page/info"]')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page div[onclick*="ac=mobile/page/privacy-policy"]')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page div[onclick*="ac=mobile/page/bonus-rules"]')));
    }
}