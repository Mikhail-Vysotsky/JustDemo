<?php

/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 14.06.16
 * Time: 11:08
 */
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class MainEventContext implements Context, SnippetAcceptingContext
{
    private $driver;
    private $account;
    private $tournament_label;

    function __construct()
    {
        $this->driver = Support_Registry::singleton()->driver;
        $this->account = Support_Registry::singleton()->account;
        $this->tournament_label = '';
    }

    /**
     * @Given /^I fill create main events form$/
     */
    public function iFillCreateMainEventsForm()
    {
        // wait for form open
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#LB_MainEvent_Form #f_active')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#LB_MainEvent_Form #font_size')));

        //fill the form
        $this->driver->findElement(WebDriverBy::cssSelector('#LB_MainEvent_Form #f_active option[value="1"]'))->click();
        $this->driver->findElement(WebDriverBy::cssSelector('#LB_MainEvent_Form #fileupload_icon'))->sendKeys(Support_Configs::get()->UPLOAD_FILES_DIR.'120x120_fc.png');
        $this->driver->findElement(WebDriverBy::cssSelector('#LB_MainEvent_Form #title_de'))->sendKeys($this->tournament_label);
        $this->driver->findElement(WebDriverBy::cssSelector('#LB_MainEvent_Form #title_en'))->sendKeys($this->tournament_label);
        $this->driver->findElement(WebDriverBy::cssSelector('#LB_MainEvent_Form #tournament_keywords'))->sendKeys($this->tournament_label);

        $urlName = 'seleniumTest'.time();
        $this->driver->findElement(WebDriverBy::cssSelector('#LB_MainEvent_Form #banner_url'))->sendKeys($urlName);
    }

    /**
     * @Given /^I open game page and store tournament label$/
     */
    public function iOpenGamePageAndStoreTournamentLabel()
    {
        $gid = key(Support_Registry::singleton()->arGames[0]->getArGames());
        $this->driver->get(Support_Registry::singleton()->arGames[0]->getUrlRegular());
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('div[data-match="' . $gid . '"]')
        ));
        $this->tournament_label = $this->driver->findElement(WebDriverBy::cssSelector('.tournament-title'))->getText();
    }

    /**
     * @Given /^I submit create main events form$/
     */
    public function iSubmitCreateMainEventsForm()
    {
        $page_timestamp = $this->driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp'))->getAttribute('data-page-get');
        $this->driver->findElement(WebDriverBy::cssSelector('#LB_MainEvent_Form .form-row-buttons button#submit'))->click();
        Support_Wait::forPageTimestampUpdated($page_timestamp);
        sleep(1);
        Support_AdminHelper::clearCache();
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#custom_alert_dialog')));
        usleep(250000);
        $this->driver->findElement(WebDriverBy::cssSelector('#custom_alert_dialog button.button'))->click();
        usleep(150000);
    }

    /**
     * @Then /^I can place ticket to main event$/
     */
    public function iCanPlaceTicketToMainEvent()
    {
        // check that main event present on main page
        Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#main-events .item'));
        Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#main-events .button a[href*="ac=v3/sports/index"]'));

        // go to main event
        $this->driver->findElement(WebDriverBy::cssSelector('#main-events .button a[href*="ac=v3/sports/index"]'))->click();
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.odds .outcome')));

        $gid = key(Support_Registry::singleton()->arGames[0]->getArGames());
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('div[data-match="' . $gid . '"]')
        ));
        
        Support_TicketHelper::placeBetToEachGames(Support_Registry::singleton()->arGames[0]);
        Support_TicketHelper::setStake('10');
        Support_TicketHelper::doClickToCreateTicket();
        Support_TicketHelper::openTicket();

        sleep(1);
        // check ticket details
        PHPUnit_Framework_Assert::assertContains('single', strtolower($this->driver->findElement(WebDriverBy::cssSelector('#ticket-details-wrapper .ticketType'))->getText()));
        PHPUnit_Framework_Assert::assertContains('10 eur', strtolower($this->driver->findElement(WebDriverBy::cssSelector('#ticket-details-wrapper .ticketBet .value'))->getText()));

        $this->driver->findElement(WebDriverBy::cssSelector('#modal_content .fa-close'))->click();
        usleep(250000);   
    }

    /**
     * @Given /^I mark main event as not active$/
     */
    public function iMarkMainEventAsNotActive()
    {
        // find event
        $el_event = $this->driver->findElement(WebDriverBy::cssSelector('.pager_rows tr.h[key]'));
        $row_id = $el_event->getAttribute('id');
        $el_event->findElement(WebDriverBy::cssSelector('.visible-toggler.active-1'))->click();

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.v3-admin-popup-area .admin-form-control')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.v3-admin-popup-area .save-button')));
        sleep(1);
        $this->driver->findElement(WebDriverBy::cssSelector('.v3-admin-popup-area .admin-form-control'))->sendKeys('Behat test: disable main event. Timestamp: '.time());
        sleep(1);
        $this->driver->findElement(WebDriverBy::cssSelector('.v3-admin-popup-area .save-button'))->click();
        sleep(1);
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#'.$row_id.' .active-0')));
        Support_AdminHelper::clearCache();
    }

    /**
     * @Then /^Main event is not available on public part$/
     */
    public function mainEventIsNotAvailableOnPublicPart()
    {
        // check that main event present on main page
        Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('#main-events .item'));
        Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('#main-events .button a[href*="ac=v3/sports/index"]'));
    }
}