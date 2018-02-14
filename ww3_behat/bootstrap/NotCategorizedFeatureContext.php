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

class NotCategorizedFeatureContext implements Context, SnippetAcceptingContext {
    private $lang;
    private $driver;
    private $account;

    function __construct()
    {
        $this->driver = Support_Registry::singleton()->driver;
        $this->account = Support_Registry::singleton()->account;
    }
    /**
     * @When /^I switch language switcher to each of available language$/
     */
    public function iSwitchLanguageSwitcherToEachOfAvailableLanguage()
    {
        // just dummy
        return true;
    }

    /**
     * @Then /^Page translation switched$/
     */
    public function pageTranslationSwitched()
    {
        // clear session and open main page
        Support_Registry::singleton()->startNewBrowserSession();
        Support_Helper::openMainPage();

        $arExistingLanguages = array(
                    "select_language('de')",
                    "select_language('en')",
                    "select_language('fr')",
                    "select_language('it')",
                    "select_language('es')",
                    "select_language('tr')",
                    "select_language('ro')",
                    "select_language('sr')",
                    "select_language('hu')"
        );

        // check all language exist in language-switcher
        foreach ($arExistingLanguages as $__lang) {
            Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#language-select-box div[onclick*="'.$__lang.'"'), true);
        }

        // check how to switcher work
        foreach ($arExistingLanguages as $l_lang) {
            preg_match_all('/\\(([^()]*)\\)/', $l_lang, $matches);
            $lang = trim($matches[1][0],'\'');

            // clear session and open main page
            Support_Registry::singleton()->startNewBrowserSession();

            // switch language
            $this->driver->findElement(WebDriverBy::cssSelector('#current-language'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.select_language_box_item[onclick="'.$l_lang.'"]')));
            $this->driver->findElement(WebDriverBy::cssSelector('.select_language_box_item[onclick="'.$l_lang.'"]'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('#current-language img[src*="'.$lang.'"]')
            ));
            // check language
            $this->checkLang($lang);
            $this->driver->navigate()->refresh();
            $this->checkLang($lang);
        }
    }

    /**
     * @Given /^Language stored in browser cookies$/
     */
    public function languageStoredInBrowserCookies()
    {
        // just dummy
        return true;
    }

    /**
     * @Given /^Language stay same when i navigate to other pages$/
     */
    public function languageStaySameWhenINavigateToOtherPages()
    {
        // just dummy
        return true;
    }

    /**
     * @Given /^Language stay same when i restart browser session$/
     */
    public function languageStaySameWhenIRestartBrowserSession()
    {
        // just dummy
        return true;
    }

    private function checkLang($lang)
    {
        // check cookie
        $this->lang = $lang;
        // wait for cookies changed and check stored language
        $c_lang = $this->driver->wait()->until(function(){
            $c_lang = $this->driver->manage()->getCookieNamed('CONTEXT_CURRENT_USER_LANGUAGE_1');

            if ($c_lang['value'] === $this->lang)
                return $c_lang['value'];
        });
        PHPUnit_Framework_Assert::assertEquals($lang, $c_lang);

        // check button
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.sign-in-form button[type="submit"]')));
        $b_sign_in = strtolower($this->driver->findElement(WebDriverBy::cssSelector('.sign-in-form button[type="submit"]'))->getText());
        $b_register = strtolower($this->driver->findElement(WebDriverBy::cssSelector('.sign-in-form a.register-link span'))->getText());

        switch ($lang) {
            case 'de':
                PHPUnit_Framework_Assert::assertEquals('einloggen', $b_sign_in);
                PHPUnit_Framework_Assert::assertEquals('registrieren', $b_register);
                break;
            case 'en':
                PHPUnit_Framework_Assert::assertEquals('sign in', $b_sign_in);
                PHPUnit_Framework_Assert::assertEquals('register', $b_register);
                break;
            case 'fr':
                PHPUnit_Framework_Assert::assertEquals('connection', $b_sign_in);
                PHPUnit_Framework_Assert::assertEquals('enregistrer', $b_register);
                break;
            case 'it':
                PHPUnit_Framework_Assert::assertEquals('login', $b_sign_in);
                PHPUnit_Framework_Assert::assertEquals('registra', $b_register);
                break;
            case 'es':
                PHPUnit_Framework_Assert::assertEquals('iniciar sesiÓn', $b_sign_in);
                PHPUnit_Framework_Assert::assertEquals('registrarse', $b_register);
                break;
            case 'tr':
                PHPUnit_Framework_Assert::assertEquals("oturum aÇ", $b_sign_in);
                PHPUnit_Framework_Assert::assertEquals("kayit ol", $b_register);
                break;
            case 'ro':
                PHPUnit_Framework_Assert::assertEquals('sign in', $b_sign_in);
                PHPUnit_Framework_Assert::assertEquals('register', $b_register);
                break;
            case 'sr':
                PHPUnit_Framework_Assert::assertEquals('prijavite se', $b_sign_in);
                PHPUnit_Framework_Assert::assertEquals('register', $b_register);
                break;
            case 'hu':
                PHPUnit_Framework_Assert::assertEquals('bejelentkezÉs', $b_sign_in);
                PHPUnit_Framework_Assert::assertEquals('nyilvÁntartÁs', $b_register);
                break;
            default:
                throw new Exception('No translation found in tests');
                break;
        }
    }

    /**
     * @When /^I switch to live section$/
     */
    public function iSwitchToLiveSection()
    {
        $this->driver->findElement(WebDriverBy::cssSelector('#head-menu .head-menu-item a[href*="ac=user/lb/index"]'))->click();
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#head-menu .head-menu-item.active a[href*="ac=user/lb/index"]')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('.matches .match_items tr')
        ));
    }

    /**
     * @Then /^Livebet section is valid$/
     */
    public function livebetSectionIsValid()
    {
        Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.pager .prev_page'));
        Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.pager .next_page'));
    }

    /**
     * @When /^I switch to result section$/
     */
    public function iSwitchToResultSection()
    {
        $this->driver->findElement(WebDriverBy::cssSelector('#head-menu .head-menu-item a[href*="ac=user/results/search/init"]'))->click();
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#head-menu .head-menu-item.active a[href*="ac=user/results/search/init"]')));

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('result_search_content')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('result_search')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('date_start')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('date_end')));
    }

    /**
     * @Then /^Result page is valid$/
     */
    public function resultPageIsValid()
    {
        Support_Registry::singleton()->elementPresent(WebDriverBy::id('result_search_content'));
        Support_Registry::singleton()->elementPresent(WebDriverBy::id('result_search'));
        Support_Registry::singleton()->elementPresent(WebDriverBy::id('date_start'));
        Support_Registry::singleton()->elementPresent(WebDriverBy::id('date_end'));
    }

    /**
     * @When /^I switch to live score section$/
     */
    public function iSwitchToLiveScoreSection()
    {
        $this->driver->findElement(WebDriverBy::cssSelector('#head-menu .head-menu-item a[href*="ac=user/livescore/init"]'))->click();
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#head-menu .head-menu-item.active a[href*="ac=user/livescore/init"]')));

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.t-item-gentime')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('livescore_list')));

    }

    /**
     * @Then /^Live score page is valid$/
     */
    public function liveScorePageIsValid()
    {
        Support_Check::livescorePageIsValid();
    }
}