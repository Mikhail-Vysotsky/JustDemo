<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 28.12.15
 * Time: 17:42
 */

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class Top7Context implements Context, SnippetAcceptingContext
{
    private $driver;
    private $account;
    private $arGames;


    function __construct()
    {
        $this->driver = Support_Registry::singleton()->driver;
        $this->account = Support_Registry::singleton()->account;
        $this->arGames = Support_Registry::singleton()->arGames;
    }

    /**
     * @Then /^Top7 ticket is not created$/
     */
    public function top7ticketIsNotCreated()
    {
        Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.t-item-submit-final #submitgreen.btn_green.disabled'));
        Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('.popup-success-msg.alert-msg'));

        $this->driver->findElement(WebDriverBy::cssSelector('.head-menu-item a[href*="ac=v3/sports/index"]'))->click();

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.ticket-list-items')));

        Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('.ticket-list-item[onclick*="open_ticket_details"]'));

        $curr_balance = $this->driver->findElement(WebDriverBy::cssSelector('.balance'))->getText();
        $curr_balance = str_replace('\'', '', $curr_balance);
        $curr_balance = str_replace('CHF', '', $curr_balance);
        $curr_balance = str_replace('EUR', '', $curr_balance);
        $curr_balance = trim($curr_balance);

        PHPUnit_Framework_Assert::assertEquals('100', $curr_balance);

    }

    /**
     * @Then /^Only (\d+) games selected$/
     */
    public function onlyGamesSelected($amount)
    {
        $amount = (int)$amount;
        $selected_bets = count($this->driver->findElements(WebDriverBy::cssSelector('.select-bet-btn.outcome.selected')));

        PHPUnit_Framework_Assert::assertEquals($amount, $selected_bets);
    }

    /**
     * @Then /^Ticket type is top7 and ticket contain only 7 games$/
     */
    public function ticketTypeIsTop7andTicketContainOnly7Games()
    {
        Support_TicketHelper::ticketTypeIs('top 7');
    }

    /**
     * @Given /^I try to select 8 games$/
     */
    public function iTryToSelectGames()
    {
        Support_TicketHelper::selectGames(7);
        $driver = Support_Registry::singleton()->driver;

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.top7-deactive')));

        $top7s = $driver->findElements(WebDriverBy::cssSelector('.top7-events-area'));
        $arGames = $top7s[0]->findElements(WebDriverBy::cssSelector('.top7-events-area tr.odd[index]'));

        $game_count = 0;

        sleep(2);

        foreach ($arGames as $game) {
            $game_count++;
            if ($game_count < 8 ) {
                continue;
            } else {
                // get available odds
                $arOdds = $game->findElements(WebDriverBy::cssSelector('div.outcome'));
                $odd = $arOdds[0];
                $odd->click();

                $class = $odd->getAttribute('class');
                PHPUnit_Framework_Assert::assertContains('top7-deactive', $class);
                PHPUnit_Framework_Assert::assertNotContains('selected', $class);

                sleep(1);
            }


        }
        sleep(3);
    }

    /**
     * @Given /^I create top7 ticket$/
     */
    public function iCreateTop7_Ticket()
    {
        // create top7 ticket
        Support_GoPage::openTop7List();
        Support_TicketHelper::selectGames(7);
        Support_Helper::clickButton('10 set and print coupon');
    }

    /**
     * @When /^Each of user bet win$/
     */
    public function eachOfUserBetWin()
    {
        $this->selectTop7OutcomesToWin(7);
    }

    /**
     * @When /^Six games win but other lose$/
     */
    public function sixGamesWinButOtherLose()
    {
        $this->selectTop7OutcomesToWin(6);
    }


    /**
     * @Then /^Top7 ticket is win$/
     */
    public function top7TicketIsWin()
    {
        Support_Helper::loginUnderAccount();
        Support_TicketHelper::checkInPublicInterfaceThatTicketIs('top7', 'won');
    }

    /**
     * @Then /^Top7 ticket is lost$/
     */
    public function top7TicketIsLost()
    {
        Support_Helper::loginUnderAccount();
        Support_TicketHelper::checkInPublicInterfaceThatTicketIs('top7', 'lost');
    }

    private function selectTop7OutcomesToWin($win_bets)
    {
        $arGames = Support_Registry::singleton()->top7_games[0]->getArGames();

        $cnt = 0;
        foreach ($arGames as $gid => $id) {
            if ($cnt < $win_bets) {
                Support_AdminHelper::markGame($gid, 'won');
                Support_Helper::processTicketQueue();
            } else {
                Support_AdminHelper::markGame($gid, 'lose');
                Support_Helper::processTicketQueue();
            }
            $cnt++;
        }

        Support_AdminHelper::logoutFromBackoffice();

    }

}