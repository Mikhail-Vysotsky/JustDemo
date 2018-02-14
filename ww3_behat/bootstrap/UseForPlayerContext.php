<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 16.03.16
 * Time: 17:03
 */
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class UseForPlayerContext implements Context, SnippetAcceptingContext {
    private $driver;
    /**
     * @var array
     */
    private $arDisabledCountries = [];
    private $arAvailableCountries = [];
    private $player_country;

    function __construct()
    {
        $this->driver = Support_Registry::singleton()->driver;
        $this->arDisabledCountries = [];
        $this->arAvailableCountries = [];
        $this->player_country = '';
    }

    /** @AfterScenario */
    public function teardown()
    {
        if (count($this->arDisabledCountries) > 0) {
            // go to backoffice
            Support_AdminHelper::loginAsAdmin();
            Support_GoPage::openAdminPage('countries');

            foreach ($this->arDisabledCountries as $disabled) {
                $this->enableCountry($disabled);
            }
            
        }
    }

    /**
     * @Given /^I store available countries$/
     */
    public function iStoreAvailableCountries()
    {
        $arGetCountries = $this->driver->findElements(WebDriverBy::cssSelector('#country option'));
        foreach ($arGetCountries as $element) {
            $country = $element->getAttribute('value');

            if (strlen($country) > 0)
                $this->arAvailableCountries[] = $country;
        }
    }

    /**
     * @Given /^I disable several counties$/
     */
    public function iDisableSeveralCounties()
    {
        // disable first 10 countries
        for ($i = 0; $i < 2; $i++) {
            $country_to_disable = $this->arAvailableCountries[$i];
            if ($this->disableCountry($country_to_disable))
                $this->arDisabledCountries[] = $country_to_disable;
        }
    }

    /**
     * @Then /^I see that disabled countries is not available$/
     */
    public function iSeeThatDisabledCountriesIsNotAvailable()
    {
        // check that disabled countries is not available but other countries available
        foreach ($this->arAvailableCountries as $country) {
            $isDisabled = false;
            foreach ($this->arDisabledCountries as $disabled) {
                if ($country === $disabled) {
                    $isDisabled = true;
                    break;
                }
            }
            if (!$isDisabled) {
                Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#country option[value="'.$country.'"]'));
            } else {
                Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('#country option[value="'.$country.'"]'));
            }

        }
    }

    /**
     * @Given /^I enable all countries$/
     */
    public function iEnableAllCountries()
    {
        foreach ($this->arDisabledCountries as $to_enable) {
            $this->enableCountry($to_enable);
        }


        Support_AdminHelper::clearCache();
    }

    /**
     * @Then /^I see that all countries available$/
     */
    public function iSeeThatAllCountriesAvailable()
    {
        // check that disabled countries is not available but other countries available
        foreach ($this->arAvailableCountries as $country) {
            Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#country option[value="'.$country.'"]'));
        }
    }

    /**
     * @Given /^I set country$/
     */
    public function iSetCountry()
    {
        // fill required fields
        $this->driver->findElement(WebDriverBy::id('first_name'))->sendKeys('Behat');
        $this->driver->findElement(WebDriverBy::id('last_name'))->sendKeys(time());

        $this->driver->findElement(WebDriverBy::id('birth_tmstmp'))->clear();
        $this->driver->findElement(WebDriverBy::id('birth_tmstmp'))->sendKeys('13.03.1978');
        $this->driver->findElement(WebDriverBy::id('city'))->sendKeys('Berlin');
        $this->driver->findElement(WebDriverBy::id('street'))->sendKeys('Some Street 123');
        $this->driver->findElement(WebDriverBy::id('zip'))->sendKeys('123456');

        // set country
        $this->iStoreAvailableCountries();
        $this->player_country = $this->arAvailableCountries[0];
        $this->driver->findElement(WebDriverBy::cssSelector('#countrySelectBoxItText'))->click();
        usleep(500000);
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#countrySelectBoxItOptions li[data-val="'.$this->player_country.'"]')));

        $this->driver->findElement(WebDriverBy::cssSelector('#countrySelectBoxItOptions li[data-val="'.$this->player_country.'"]'))->getLocationOnScreenOnceScrolledIntoView();
        usleep(500000);
        $this->driver->findElement(WebDriverBy::cssSelector('#countrySelectBoxItOptions li[data-val="'.$this->player_country.'"]'))->click();

        usleep(250000);

        // save
        $this->driver->findElement(WebDriverBy::cssSelector('#LB_Player_Settings_Form button[type="submit"]'))->click();

        // wait for success message
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-success-msg.alert-msg')));
        usleep(123000);
        $this->driver->findElement(WebDriverBy::cssSelector('#modal_content #custom_alert_btn'))->click();

        usleep(150000);
    }

    /**
     * @Given /^I disable user country$/
     */
    public function iDisableUserCountry()
    {
        Support_AdminHelper::loginAsAdmin();
        Support_GoPage::openAdminPage('countries');

        if (Support_Registry::singleton()->elementPresent(WebDriverBy::id('reset'))) {
            $this->driver->findElement(WebDriverBy::id('reset'))->click();
            Support_Registry::singleton()->page_timestamp = Support_Wait::forAdminPageLoaded('.t-page-gen-timestamp', 'data-page-get', Support_Registry::singleton()->page_timestamp);
        }

        if ($this->disableCountry($this->player_country))
            $this->arDisabledCountries[] = $this->player_country;
    }

    /**
     * @Then /^I see that country field is not set$/
     */
    public function iSeeThatCountryFieldIsNotSet()
    {
        Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('#country option[selected]'));
        Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('#country option[value="'.$this->player_country.'"]'));
    }

    private function disableCountry($country_to_disable)
    {
        $country_id = $this->getCountryId($country_to_disable);

        if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#'.$country_id.' .country-f-use-for-player-toggler.active-1'), false)) {
            // disable country
            $this->confirmUseForPlayerStatusChange($country_id);

        }
        return true;
    }

    private function enableCountry($country_to_enable)
    {
        $country_id = $this->getCountryId($country_to_enable);

        // disable country
        if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#'.$country_id.' .country-f-use-for-player-toggler.active-0'), false)) {
            usleep(120000);
            $this->confirmUseForPlayerStatusChange($country_id);

            // remove enabled country from array
            unset($this->arDisabledCountries[array_search($country_to_enable, $this->arDisabledCountries)]);
        }

        return true;
    }

    private function getCountryId($country_to_enable)
    {
        Support_Registry::singleton()->page_timestamp = $this->driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp'))->getAttribute('data-page-get');
        $country_id = null;

        // search country
        $this->driver->findElement(WebDriverBy::id('code'))->clear();
        $this->driver->findElement(WebDriverBy::id('code'))->sendKeys($country_to_enable);
        $this->driver->findElement(WebDriverBy::cssSelector('.detail-search-buttons button[type="submit"]'))->click();

        Support_Registry::singleton()->page_timestamp = Support_Wait::forAdminPageLoaded('.t-page-gen-timestamp', 'data-page-get', Support_Registry::singleton()->page_timestamp);

        // get id of country to enable
        $arCountries = $this->driver->findElements(WebDriverBy::cssSelector('.pager_rows tr.h'));
        foreach ($arCountries as $el_rows) {
            $country_short_code = $el_rows->findElement(WebDriverBy::cssSelector('.t-code_2'))->getText();
            if ($country_short_code === $country_to_enable)
                $country_id = $el_rows->getAttribute('id');
        }


        return $country_id;
    }

    private function confirmUseForPlayerStatusChange($country_id)
    {
        $this->driver->findElement(WebDriverBy::cssSelector('#'.$country_id.' .country-f-use-for-player-toggler'))->click();

        sleep(1);
        $wd_confirm = WebDriverBy::cssSelector('.v3-admin-popup-area button.save-button');
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated($wd_confirm));
        $this->driver->findElement($wd_confirm)->click();

        Support_Registry::singleton()->page_timestamp = Support_Wait::forAdminPageLoaded('.t-page-gen-timestamp', 'data-page-get', Support_Registry::singleton()->page_timestamp);
        usleep(150000);
    }
}