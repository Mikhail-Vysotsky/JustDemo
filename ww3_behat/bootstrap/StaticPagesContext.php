<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 02.02.16
 * Time: 17:32
 */
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class StaticPagesContext implements Context, SnippetAcceptingContext {

    private $driver;
    private $account;

    function __construct()
    {
        $this->driver = Support_Registry::singleton()->driver;
        $this->account = Support_Registry::singleton()->account;
    }

    /**
     * @When /^I click to "([^"]*)" link in footer$/
     */
    public function iClickToLinkInFooter($link_name)
    {
        switch ($link_name) {
            case 'Terms and conditions':
                $this->driver->findElement(WebDriverBy::cssSelector('#page-footer-container a[href*="ac=v3/info/terms_and_conditions"]'))->click();
                break;
            case 'Privacy policy':
                $this->driver->findElement(WebDriverBy::cssSelector('#page-footer-container a[href*="ac=v3/info/privacy"]'))->click();
                break;
            case 'Responsibility':
                $this->driver->findElement(WebDriverBy::cssSelector('#page-footer-container a[href*="ac=v3/info/responsibility"]'))->click();
                break;
            case 'Bonus program':
                $this->driver->findElement(WebDriverBy::cssSelector('#page-footer-container a[href*="ac=v3/info/bonus-program"]'))->click();
                break;
            case 'Bets':
                $this->driver->findElement(WebDriverBy::cssSelector('#page-footer-container a[href*="ac=v3/sports/index"]'))->click();
                break;
            case 'Live':
                $this->driver->findElement(WebDriverBy::cssSelector('#page-footer-container a[href*="ac=user/lb/index"]'))->click();
                break;
            case 'Results':
                $this->driver->findElement(WebDriverBy::cssSelector('#page-footer-container a[href*="ac=user/results/search/init"]'))->click();
                break;
            case 'Live Score':
                $this->driver->findElement(WebDriverBy::cssSelector('#page-footer-container a[href*="ac=user/livescore/init"]'))->click();
                break;

            default:
                throw new Exception("No link found in test method ".__CLASS__.'::'.__METHOD__);
        }
    }

    /**
     * @Then /^I see "([^"]*)" page$/
     */
    public function iSeePage($page_name)
    {
        switch ($page_name) {
            case 'Terms and conditions':
                Support_Wait::forTextInElement( WebDriverBy::cssSelector('.terms-head h2'), 'Terms & Conditions');
                $cur_url = $this->driver->getCurrentURL();
                PHPUnit_Framework_Assert::assertContains('v3/info/terms_and_conditions', $cur_url);
                break;
            case 'Privacy policy':
                Support_Wait::forTextInElement( WebDriverBy::cssSelector('.terms-head h2'), 'Privacy');
                $cur_url = $this->driver->getCurrentURL();
                PHPUnit_Framework_Assert::assertContains('v3/info/privacy', $cur_url);
                break;
            case 'Responsibility':
                Support_Wait::forTextInElement( WebDriverBy::cssSelector('.terms-head h2'), 'Responsibility');
                $cur_url = $this->driver->getCurrentURL();
                PHPUnit_Framework_Assert::assertContains('v3/info/responsibility', $cur_url);
                break;
            case 'Bonus program':
                Support_Wait::forTextInElement( WebDriverBy::cssSelector('.terms-head h2'), 'Bonus program');
                $cur_url = $this->driver->getCurrentURL();
                PHPUnit_Framework_Assert::assertContains('v3/info/bonus-program', $cur_url);
                break;
            case 'Bets':
                $this->driver->findElement(WebDriverBy::cssSelector('#page-footer-container a[href*="ac=v3/sports/index"]'))->click();
                break;
            case 'Live':
                $this->driver->findElement(WebDriverBy::cssSelector('#page-footer-container a[href*="ac=user/lb/index"]'))->click();
                break;
            case 'Results':
                $this->driver->findElement(WebDriverBy::cssSelector('#page-footer-container a[href*="ac=user/results/search/init"]'))->click();
                break;
            case 'Live Score':
                $this->driver->findElement(WebDriverBy::cssSelector('#page-footer-container a[href*="ac=user/livescore/init"]'))->click();
                break;

            default:
                throw new Exception("No link found in test method ".__CLASS__.'::'.__METHOD__);
        }
    }

    /**
     * @Given /^"([^"]*)" is correct$/
     */
    public function isCorrect($page_name)
    {
        switch ($page_name) {
            case 'Terms and conditions':
                $page_text = $this->driver->findElement(WebDriverBy::cssSelector('#center-side .read-text'))->getText();
                PHPUnit_Framework_Assert::assertContains('„Wir Wetten“ or Wir Wetten (Alderney) Limited is a company, that was registered under the laws of Alderney and a registered office and business address at Inchalla, Le Val GY9 3UL Alderney. E-mail: info@wir-wetten.com ; Website: www.wir-wetten.com', $page_text);
                PHPUnit_Framework_Assert::assertContains('Any substantional amendment to the Wir Wetten Terms and Conditions will be notified in before coming into effect by Wir Wetten to the account holder.', $page_text);
                break;
            case 'Privacy policy':
                $page_text = $this->driver->findElement(WebDriverBy::cssSelector('#center-side .read-text'))->getText();
                PHPUnit_Framework_Assert::assertContains('As the operating company of the website Wir-Wetten (hereinafter jointly referred to as “the company“) aims to actively work towards enhancing trust in data protection and thus takes every possible step to inform its users of all relevant security measures.', $page_text);
                PHPUnit_Framework_Assert::assertContains('As a registered user you may close your betting account at any time. When your account is closed, any positive balance will be paid out to you and the personal data stored encrypted.', $page_text);
                break;
            case 'Responsibility':
                $page_text = $this->driver->findElement(WebDriverBy::cssSelector('#center-side .read-text'))->getText();
                PHPUnit_Framework_Assert::assertContains('Wir Wetten understands the potential dangers that come along with gambling. Not only is it important for us to see what potential dangers there are, but also that you are aware of those too. It is a fact, that gambling might result in addiction.', $page_text);
                PHPUnit_Framework_Assert::assertContains('For any further information concerning our "responsibility" section, please read our Terms & Conditions or contact our customer support.', $page_text);
                break;
            case 'Bonus program':
                $elts = $this->driver->findElements(WebDriverBy::cssSelector('.bonus-program-info .bonus-program-item'));
                PHPUnit_Framework_Assert::assertEquals(5, count($elts));
                Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.bonus-program-info .bonus-program-notice.top7-top-description'));
                break;

            default:
                throw new Exception("No link found in test method ".__CLASS__.'::'.__METHOD__);
        }
    }

    /**
     * @Then /^"([^"]*)" page is opened$/
     */
    public function pageIsOpened($page_name)
    {
        switch ($page_name) {
            case 'Bets':
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#head-menu .head-menu-item.active a[href*="ac=v3/sports/index"]')));
                Support_Registry::singleton()->elementPresent(WebDriverBy::id('filtered-matches-area'));
                break;
            case 'Live':
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#head-menu .head-menu-item.active a[href*="ac=user/lb/index"]')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('.matches .match_items tr')
                ));
                Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.pager .prev_page'));
                Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.pager .next_page'));
                break;
            case 'Results':
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#head-menu .head-menu-item.active a[href*="ac=user/results/search/init"]')));

                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('result_search_content')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('result_search')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('date_start')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('date_end')));

                Support_Registry::singleton()->elementPresent(WebDriverBy::id('result_search_content'));
                Support_Registry::singleton()->elementPresent(WebDriverBy::id('result_search'));
                Support_Registry::singleton()->elementPresent(WebDriverBy::id('date_start'));
                Support_Registry::singleton()->elementPresent(WebDriverBy::id('date_end'));
                break;
            case 'Live Score':
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#head-menu .head-menu-item.active a[href*="ac=user/livescore/init"]')));

                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.t-item-gentime')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('livescore_list')));

                Support_Check::livescorePageIsValid();
                break;

            default:
                throw new Exception("No link found in test method ".__CLASS__.'::'.__METHOD__);
        }
    }

    /**
     * @When /^I click to "([^"]*)" banner$/
     */
    public function iClickToBanner($banner)
    {
        if ($banner === "Bonus program") {
            $this->driver->findElement(WebDriverBy::cssSelector('a.left-col-button[href*="ac=v3/info/bonus-program"]'))->click();
        } elseif ($banner === "Mobile version") {
            $cleanhost = parse_url(Support_Configs::get()->MOBILE_BASE_URL, PHP_URL_HOST);
            $this->driver->findElement(WebDriverBy::cssSelector('a.left-col-button[href*="'.$cleanhost.'"]'))->click();
        }
    }

    /**
     * @Then /^I navigate to mobile site$/
     */
    public function iNavigateToMobileSite()
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#wwfooter a[onclick*="ac=mobile/player/registration"]')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .t-sport')));
    }
}