<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 04.02.16
 * Time: 12:34
 */
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class MobileContext implements Context, SnippetAcceptingContext {
    private $driver;

    function __construct()
    {
        $this->driver = Support_Registry::singleton()->driver;
    }
    /**
     * @Given /^I open main page of mobile site$/
     */
    public function iOpenMainPageOfMobileSite()
    {
        Support_GoPage::openMainPageOfMobileSite();
    }

    /**
     * @Given /^I open main page of regular site$/
     */
    public function iOpenMainPageOfRegularSite()
    {
        $this->driver->get(Support_Configs::get()->BASE_URL);
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('ticket_stake_buttons_area')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('category-block-head')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('account-row')));
        sleep(2);
    }

    /**
     * @When /^I login under account on mobile site$/
     */
    public function iLoginUnderAccountOnMobileSite()
    {
        Support_Helper::loginUnderAccountOnMobileSite();
    }

    /**
     * @When /^I login under account on regular site$/
     */
    public function iLoginUnderAccountOnRegularSite()
    {
        Support_Helper::loginUnderAccount();

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('page-footer')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.account-block a.logout-link')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.account-block a[href*="ac=user/player/settings"]')));
    }

    /**
     * @Then /^I authorized on regular site$/
     */
    public function iAuthorizedOnRegularSite()
    {
        $this->driver->get(Support_Configs::get()->BASE_URL);
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('page-footer')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.account-block a.logout-link')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.account-block a[href*="ac=user/player/settings"]')));

        // go to account page
        $this->driver->findElement(WebDriverBy::cssSelector('.account-block a[href*="ac=user/player/settings"]'))->click();
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('email')));
        usleep(120000);
        $player_email = $this->driver->findElement(WebDriverBy::id('email'))->getAttribute('value');

        PHPUnit_Framework_Assert::assertContains(Support_Registry::singleton()->account->email, $player_email);
    }

    /**
     * @Then /^I authorized on mobile site$/
     */
    public function iAuthorizedOnMobileSite()
    {
        $this->driver->get(Support_Configs::get()->MOBILE_BASE_URL);

        // wait for login
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#wwfooter .footer-link.logged-in')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#wwfooter .user-currency')));

        Support_Wait::forTextInElement(WebDriverBy::cssSelector('#wwfooter .footer-link.logged-in'), Support_Registry::singleton()->account->email);
    }

    /**
     * @Given /^I check that i authorized on regular site$/
     */
    public function iCheckThatIAuthorizedOnRegularSite()
    {
        $this->iAuthorizedOnRegularSite();
    }

    /**
     * @Given /^I check that i authorized on mobile site$/
     */
    public function iCheckThatIAuthorizedOnMobileSite()
    {
        $this->iOpenMainPageOfMobileSite();

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#wwfooter .username .logged-in')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#wwfooter .balance .logged-in')));

        Support_Wait::forTextInElement(WebDriverBy::cssSelector('#wwfooter .username .logged-in'), Support_Registry::singleton()->account->email);

    }

    /**
     * @Given /^I do logout from mobile site$/
     */
    public function iDoLogoutFromMobileSite()
    {
        Support_Helper::logout();
    }

    /**
     * @Given /^I do logout from regular site$/
     */
    public function iDoLogoutFromRegularSite()
    {
        $this->driver->findElement(WebDriverBy::cssSelector('.account-block .logout-link'))->click();

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('username')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('password')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.sign-in-form a[href*="register_player"]')));

    }

    /**
     * @Then /^I not authorized on regular site$/
     */
    public function iNotAuthorizedOnRegularSite()
    {
        $this->driver->get(Support_Configs::get()->BASE_URL);
        Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.account-row-content #username'));
        Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.account-row-content #password'));
    }

    /**
     * @Then /^I not authorized on mobile site$/
     */
    public function iNotAuthorizedOnMobileSite()
    {
        $this->iOpenMainPageOfMobileSite();
        Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#wwfooter .footer-link[onclick*="ac=mobile/player/registration"]'));
        Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#wwfooter .footer-link[onclick*=".mobile_app.show_login"]'));
    }

    /**
     * @When /^I go to payment methods page$/
     */
    public function iGoToPaymentMethodsPage()
    {
        Support_GoPage::paymentMethodsPage_mobile();
    }
}