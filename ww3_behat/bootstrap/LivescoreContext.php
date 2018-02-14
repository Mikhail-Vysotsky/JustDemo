<?php
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 18.02.16
 * Time: 14:45
 */

class LivescoreContext implements Context, SnippetAcceptingContext
{
    private $driver;
    private $row_id;

    function __construct()
    {
        $this->driver = Support_Registry::singleton()->driver;
    }

    /**
     * @Given /^I go to livescore page$/
     */
    public function iGoToLivescorePage()
    {
        $cur_url = $this->driver->getCurrentURL();

        if (strpos($cur_url, 'ac=user/livescore/show') === false) {
            $this->driver->get(Support_Configs::get()->BASE_URL.'index.php?ac=user/livescore/show');
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.t-item-gentime[data-gen-time]')));
            Support_Registry::singleton()->page_timestamp = $this->driver->findElement(WebDriverBy::cssSelector('.t-item-gentime'))->getAttribute('data-gen-time');
        } else {
            $page_timestamp = $this->driver->findElement(WebDriverBy::cssSelector('.t-item-gentime'))->getAttribute('data-gen-time');
            $this->driver->navigate()->refresh();

            // wait for page refresh
            Support_GoPage::waitForLivescorePageRefresh($page_timestamp);
        }
    }

    /**
     * @When /^I enter "([^"]*)" team name$/
     */
    public function iEnterTeamName($team_name)
    {
        $arGames = Support_Registry::singleton()->arGames[0]->getArGames();
        $gid = key($arGames);
        $this->driver->findElement(WebDriverBy::id('livescore_search'))->clear();
        $this->driver->findElement(WebDriverBy::id('livescore_search'))->sendKeys($team_name.' '.$gid);
        Support_GoPage::waitForLivescorePageRefresh(Support_Registry::singleton()->page_timestamp);
    }

    /**
     * @Then /^Livescore found result by "([^"]*)" team$/
     */
    public function livescoreFoundResultByTeam($team_name)
    {
        // check that found only one game
        $arItems = $this->driver->findElements(WebDriverBy::cssSelector('.livescore_item'));
        PHPUnit_Framework_Assert::assertEquals(1, count($arItems));

        // check that test game present
        $found_name = strtolower($this->driver->findElement(WebDriverBy::cssSelector('.livescore_item span.'.$team_name))->getText());
        PHPUnit_Framework_Assert::assertEquals('team '.$team_name, $found_name);
    }

    /**
     * @When /^I choose any sport$/
     */
    public function iChooseAnySport()
    {
        $this->driver->findElement(WebDriverBy::cssSelector('.modSelector'))->click();
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.modSelector .reset-clear')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.modSelector input[type="checkbox"]')));
        usleep(250000);
        $this->driver->findElement(WebDriverBy::cssSelector('.modSelector input[type="checkbox"]'))->click();
        usleep(250000);
    }

    /**
     * @Then /^Livescore page is refresh and some item found$/
     */
    public function livescorePageIsRefreshAndSomeItemFound()
    {
        Support_GoPage::waitForLivescorePageRefresh(Support_Registry::singleton()->page_timestamp);
        $res_count = count($this->driver->findElements(WebDriverBy::cssSelector('.livescore_item')));
        PHPUnit_Framework_Assert::assertGreaterThanOrEqual(1, $res_count);
    }

    /**
     * @Then /^Livescore page is refresh$/
     */
    public function livescorePageIsRefresh()
    {
        Support_GoPage::waitForLivescorePageRefresh(Support_Registry::singleton()->page_timestamp);
    }

    /**
     * @When /^I click to reset button in sport filter$/
     */
    public function iClickToResetButtonInSportFilter()
    {
        $this->driver->findElement(WebDriverBy::cssSelector('.modSelector'))->click();
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.modSelector .reset-clear')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.modSelector input[type="checkbox"]')));
        usleep(250000);
        $this->driver->findElement(WebDriverBy::cssSelector('.modSelector .reset-clear'))->click();
        usleep(250000);
    }

    /**
     * @When /^I choose "([^"]*)" in livescore filter$/
     */
    public function iChooseInLivescoreFilter($filter_by)
    {
        $this->driver->findElement(WebDriverBy::cssSelector('#statusSelectBoxItArrowContainer'))->click();
        sleep(1);
        $this->driver->executeScript('return $(\'#statusSelectBoxItOptions li[data-val="'.$filter_by.'"] .selectboxit-option-anchor\').trigger(\'mousedown\')');
    }

    /**
     * @Then /^Livescore page is refresh and "([^"]*)" games present$/
     */
    public function livescorePageIsRefreshAndGamesPresent($games_found)
    {
        Support_GoPage::waitForLivescorePageRefresh(Support_Registry::singleton()->page_timestamp);
    }

    /**
     * @When /^I choose refresh every (\d+) second$/
     */
    public function iChooseRefreshEverySecond($refresh_option)
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('refresh_intervalSelectBoxItArrowContainer')));
        usleep(150000);

        $this->driver->findElement(WebDriverBy::cssSelector('#refresh_intervalSelectBoxItArrowContainer'))->click();
        sleep(1);
        $this->driver->executeScript('return $(\'#refresh_intervalSelectBoxItOptions li[data-val="'.$refresh_option.'"] .selectboxit-option-anchor\').trigger(\'mousedown\')');


        Support_GoPage::waitForLivescorePageRefresh(Support_Registry::singleton()->page_timestamp);
    }

    /**
     * @Then /^Page refresh every (\d+) second$/
     */
    public function pageRefreshEverySecond($interval)
    {
        for ($i = 0; $i < 3; $i++) {
            $time = time();
            Support_GoPage::waitForLivescorePageRefresh(Support_Registry::singleton()->page_timestamp);
            $new_time = time();
            $result_time = $new_time - $time;

            PHPUnit_Framework_Assert::assertLessThan(40, $result_time);
            PHPUnit_Framework_Assert::assertGreaterThan(20, $result_time);
        }
    }

    /**
     * @When /^I enter ticket number$/
     */
    public function iEnterTicketNumber()
    {
        $ticket_id = Support_Registry::singleton()->ticket;
        $this->driver->findElement(WebDriverBy::cssSelector('#ticket_id'))->clear();
        usleep(150000);
        $this->driver->findElement(WebDriverBy::cssSelector('#ticket_id'))->sendKeys($ticket_id);
        $this->driver->findElement(WebDriverBy::cssSelector('#ticket_id'))->submit();
        sleep(2);

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.livescore .livescore_item')));
        $this->driver->findElement(WebDriverBy::cssSelector('.livescore .livescore_item'));

        Support_GoPage::waitForLivescorePageRefresh(Support_Registry::singleton()->page_timestamp);
    }

    /**
     * @Then /^I found game from ticket$/
     */
    public function iFoundGameFromTicket()
    {
        // check that found only one game
        $arItems = $this->driver->findElements(WebDriverBy::cssSelector('.livescore_item'));
        PHPUnit_Framework_Assert::assertEquals(1, count($arItems));

        // check that test game present
        $found_name = strtolower($this->driver->findElement(WebDriverBy::cssSelector('.livescore_item span.home'))->getText());
        PHPUnit_Framework_Assert::assertEquals('team home', $found_name);
    }

    /**
     * @When /^I click to checkbox to select game$/
     */
    public function iClickToCheckboxToSelectGame()
    {
        $this->driver->findElement(WebDriverBy::cssSelector('.livescore_item input[type="checkbox"]'))->click();
        Support_Wait::forTextInElement(WebDriverBy::id('livescore_basket'), '1');
        $this->row_id = $this->driver->findElement(WebDriverBy::cssSelector('.livescore_item'))->getAttribute('id');
        sleep(1);
    }

    /**
     * @Given /^I click to tab by name "([^"]*)"$/
     */
    public function iClickToTabByName($tab_name)
    {
        $page_timestamp = $this->driver->findElement(WebDriverBy::cssSelector('.t-item-gentime'))->getAttribute('data-gen-time');
        $this->driver->findElement(WebDriverBy::id('t_item_selected_score'))->click();
        Support_GoPage::waitForLivescorePageRefresh($page_timestamp);
    }

    /**
     * @Then /^Opened tab by name selected$/
     */
    public function openedTabByNameSelected()
    {
        Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.nav-bg-active#t_item_selected_score'));
    }

    /**
     * @Given /^My game found$/
     */
    public function myGameFound()
    {
        Support_Registry::singleton()->elementPresent(WebDriverBy::id($this->row_id));
    }

    /**
     * @Given /^I found test game$/
     */
    public function iFoundTestGame()
    {
        $this->iEnterTeamName('home');
        $this->livescoreFoundResultByTeam('home');
    }

    /**
     * @When /^I select bet to regular game from livescore tab$/
     */
    public function iSelectBetToRegularGameFromLivescoreTab()
    {
        $arGames = Support_Registry::singleton()->arGames[0]->getArGames();
        $gid = key($arGames);

        $this->selectLivebetOutcome($gid);
    }

    /**
     * @When /^I select bet to each livebet game from livescore tab$/
     */
    public function iSelectBetToEachLivebetGameFromLivescoreTab()
    {
        $arGames = Support_Registry::singleton()->arGames[0]->getArGames();

        foreach ($arGames as $gid => $val) {

            $this->driver->findElement(WebDriverBy::id('livescore_search'))->clear();
            $this->driver->findElement(WebDriverBy::id('livescore_search'))->sendKeys($gid);
            Support_GoPage::waitForLivescorePageRefresh(Support_Registry::singleton()->page_timestamp);

            $this->selectLivebetOutcome($gid);
        }
    }

    /**
     * @When /^I select bet to running livebet game from livescore tab$/
     */
    public function iSelectBetToRunningLivebetGameFromLivescoreTab()
    {
        $arGames = Support_Registry::singleton()->arGames[0]->getArGames();
        $gid = key($arGames);


        // click to bet button
        $this->driver->findElement(WebDriverBy::cssSelector('.livescore_item div.make-bet[onclick*="livebet_match_odds"]'))->click();
//        $pref = 'div.betDetail[data-event="' . $gid . '"]';

        // wait for livebet tab open and wait for game load
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.head-menu-item.active a[href*="ac=user/lb/index"]')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.outcomes div.clickable[data-match_id="'.$gid.'"]')));

        // place bet to outcome
        $ticket_tips = $this->driver->findElement(WebDriverBy::id('ticket_tips'))->getText();
        $arOutcomes = $this->driver->findElements(WebDriverBy::cssSelector('.outcomes div.clickable[data-match_id="'.$gid.'"]'));
        $arOutcomes[0]->click();

        // wait for outcome selected
        Support_Wait::forTextUpdated('#ticket_tips', $ticket_tips);
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#ticket_bets div.tb[index="' . $gid . '"]')));

    }

    private function selectLivebetOutcome($gid)
    {
        // click to bet button
        $this->driver->findElement(WebDriverBy::cssSelector('.livescore_item div.make-bet[onclick*="open_match('.$gid.')"]'))->click();
        $pref = 'div.betDetail[data-event="'.$gid.'"]';
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector($pref)));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector($pref.' .oddsTypeButtons .outcome[data-outcome="1"]')));

        $ticket_tips = $this->driver->findElement(WebDriverBy::id('ticket_tips'))->getText();

        // select outcome
        $this->driver->findElement(WebDriverBy::cssSelector($pref.' .oddsTypeButtons .outcome[data-outcome="1"]'))->click();

        // wait for ticket selected
        Support_Wait::forTextUpdated('#ticket_tips', $ticket_tips);
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#ticket_bets div.tb[index="'.$gid.'"]')));

        // close pop-up
        $this->driver->findElement(WebDriverBy::cssSelector('#modal_content .fa-close'))->click();
        usleep(150000);

    }
}