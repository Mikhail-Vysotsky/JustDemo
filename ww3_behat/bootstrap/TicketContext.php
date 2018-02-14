<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 30.11.15
 * Time: 14:41
 */
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class TicketContext implements Context, SnippetAcceptingContext {
    private $stake;

    private $driver;
    private $account;

    private $ticket;

    function __construct()
    {
        $this->driver = Support_Registry::singleton()->driver;
        $this->account = Support_Registry::singleton()->account;
    }

    /**
     * @Given /^Ticket type is "([^"]*)"$/
     */
    public function ticketTypeIs($expected_ticket_type)
    {
        Support_TicketHelper::ticketTypeIs($expected_ticket_type);

    }

    /**
     * @Given /^Ticket contain (\d+) outright and (\d+) regular games and ticket type is single$/
     */
    public function ticketContainOutrightAndRegularGamesAndTicketTypeIsSingle($outright_amount, $regular_amount)
    {
        $this->ticketTypeIs('check_mixed');
    }



    /**
     * @Given /^I select two banks$/
     */
    public function iSelectTwoBanks()
    {
        sleep(2);   // for any cases

        $driver = $this->driver;
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#ticket_bets div.tb[index]')));
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div.b-bank')));

        $wd_idx = WebDriverBy::cssSelector('#ticket_bets div.tb[index]');
        $wd_b = WebDriverBy::cssSelector('div.b-bank');
        var_dump(Support_Wait::forCssCountIs( $wd_idx, 4));
        Support_Wait::forCssCountIs( $wd_b, 4);

        for ($i = 0 ; $i < 2; $i++) {
            usleep(120000);
            $arNewItems = $driver->findElements(WebDriverBy::cssSelector('#ticket_bets div.tb[index]'));
            $item = $arNewItems[$i];

            $rows = $driver->findElement(WebDriverBy::id('ticket_rows'));

            $bank = $item->findElement(WebDriverBy::cssSelector('div.b-bank'));
            $bank->click();

            Support_Wait::forTextUpdatedInElement($rows, $rows->getText());
        }

    }

    /**
     * @When /^I place bet to outright game$/
     */
    public function iPlaceBetToOutrightGame()
    {
//todo comment for debug
//        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
//            WebDriverBy::cssSelector('.outright a.show'))
//        );
//        $this->driver->findElement(WebDriverBy::cssSelector('.outright a.show'))->click();
        usleep(120000);


        $tips = $this->driver->findElement(WebDriverBy::id('ticket_tips'))->getText();
        $obOutright = $this->driver->findElement(WebDriverBy::cssSelector('.tournament-content .outright-odds'));
        $arOdds = $obOutright->findElements(WebDriverBy::cssSelector('.outright-outcome[data-match]'));

        $odd = $arOdds[array_rand($arOdds)];

        $odd->click();

        Support_Wait::forTextUpdated('#ticket_tips', $tips);
    }

    /**
     * @When /^I select bet to any special odd$/
     */
    public function iSelectBetToAnySpecialOdd()
    {
        $gid = key(Support_Registry::singleton()->arGames[0]->getArGames());

        // find match
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('a.match-total-odds.special[data-match="'.$gid.'"]')
        ));

        $match = $this->driver->findElement(WebDriverBy::cssSelector('a.match-total-odds.special[data-match="'.$gid.'"]'));

        // open match details
        $match->click();
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('div.betDetail[data-event="'.$gid.'"]')
        ));
        sleep(2);   //todo probably fix

//         select random odd
        $popUp = $this->driver->findElement(WebDriverBy::cssSelector('div.betDetail[data-event="'.$gid.'"]'));
        $arOdds = $popUp->findElements(WebDriverBy::cssSelector('.outcome[data-odds]'));

        $tips = $this->driver->findElement(WebDriverBy::id('ticket_tips'))->getText();
        $arOdds[array_rand($arOdds)]->click();
        Support_Wait::forTextUpdated('#ticket_tips', $tips);

        $this->driver->findElement(WebDriverBy::cssSelector('#modal_content .modal_body_close .fa-close'))->click();

        $this->driver->wait()->until(function() {
            for ($sec = 0; $sec < 60; $sec++) {
                if (Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('#modal_content .betDetail .match-details-head'), false)) {
                    return true;
                }
                sleep(1);
            }
            return false;
        });
    }

    /**
     * @Then /^Ticket can be only single type$/
     */
    public function ticketCanBeOnlySingleType()
    {
//        multi
        $multi = $this->driver->findElement(WebDriverBy::cssSelector('#ticket_tabs div.tab[index="multi"]'));
        PHPUnit_Framework_Assert::assertContains('not_active', $multi->getAttribute('class'));
        $multi->click();
        sleep(2);
        $multi = $this->driver->findElement(WebDriverBy::cssSelector('#ticket_tabs div.tab[index="multi"]'));
        PHPUnit_Framework_Assert::assertNotContains('selected', $multi->getAttribute('class'));

//        system
        $system = $this->driver->findElement(WebDriverBy::cssSelector('#ticket_tabs div.tab[index="system"]'));
        PHPUnit_Framework_Assert::assertContains('not_active', $system->getAttribute('class'));
        $system->click();
        sleep(2);
        $system = $this->driver->findElement(WebDriverBy::cssSelector('#ticket_tabs div.tab[index="system"]'));
        PHPUnit_Framework_Assert::assertNotContains('selected', $system->getAttribute('class'));

//        double
        $double = $this->driver->findElement(WebDriverBy::cssSelector('#ticket_tabs div.tab[index="double"]'));
        PHPUnit_Framework_Assert::assertContains('not_active', $double->getAttribute('class'));
        $double->click();
        sleep(2);
        $double = $this->driver->findElement(WebDriverBy::cssSelector('#ticket_tabs div.tab[index="double"]'));
        PHPUnit_Framework_Assert::assertNotContains('selected', $double->getAttribute('class'));

//        single
        $single = $this->driver->findElement(WebDriverBy::cssSelector('#ticket_tabs div.tab[index="single"]'));
        PHPUnit_Framework_Assert::assertContains('selected', $single->getAttribute('class'));
    }

    /**
     * @When /^I delete two games$/
     */
    public function iDeleteTwoGames()
    {
        $driver = $this->driver;

        for ($i = 0 ; $i < 2; $i++) {
            $arNewItems = $driver->findElements(WebDriverBy::cssSelector('#ticket_bets div.tb[index]'));
            $item = $arNewItems[$i];

            $tips = $driver->findElement(WebDriverBy::id('ticket_tips'))->getText();
            $remove_ticket = $item->findElement(WebDriverBy::cssSelector('div.close'));
            $remove_ticket->click();

            Support_Wait::forTextUpdated("#ticket_tips", $tips);
        }
    }

    /**
     * @Given /^Ticket contain "([^"]*)" games$/
     */
    public function ticketContainGames($how_many_bets)
    {
        $this->ticketTypeIs("multi");
    }

    /**
     * @When /^Ticket is "([^"]*)"$/
     */
    public function ticketIs($status)
    {
        Support_AdminHelper::setTicketStatus(Support_Registry::singleton()->ticket, $status);
    }

    /**
     * @Given /^Ticket mark as "([^"]*)" in user interface$/
     */
    public function ticketMarkAsInUserInterface($expected_status)
    {
        $driver = $this->driver;

        $driver->findElement(WebDriverBy::cssSelector('#last_user_tickets div.ticket-list-item[onclick*="open_ticket_details"]'))->click();
        // wait for new window
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content .popup-ticket-details')));

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.ticketDetail .ticketType.item')));
        $opened_ticket = str_replace('\'', '', $this->driver->findElement(WebDriverBy::cssSelector('.ticketCoupon.item span.value'))->getText());
        $ticket_status = $this->driver->findElement(WebDriverBy::cssSelector('.ticketStatus'))->getText();

        // check status
        if ($expected_status === "won") {
            Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.ticketPayoff'));
            PHPUnit_Framework_Assert::assertEquals('WON', $ticket_status);
            PHPUnit_Framework_Assert::assertEquals(Support_Registry::singleton()->ticket, $opened_ticket);
        } elseif ($expected_status === "lost") {
            PHPUnit_Framework_Assert::assertEquals('LOST', $ticket_status);
            PHPUnit_Framework_Assert::assertEquals(Support_Registry::singleton()->ticket, $opened_ticket);
        } elseif ($expected_status === "canceled") {
            PHPUnit_Framework_Assert::assertEquals('CANCELED', $ticket_status);
            PHPUnit_Framework_Assert::assertEquals(Support_Registry::singleton()->ticket, $opened_ticket);
        }

        // close popup
        $driver->findElement(WebDriverBy::cssSelector('#modal_content .modal_body_close .fa-close'))->click();
        usleep(200000);

    }

    /**
     * @When /^I open top7 list$/
     */
    public function iOpenTop7list()
    {
        Support_GoPage::openTop7List();
    }

    /**
     * @Given /^I select (\d+) games$/
     */
    public function iSelectGames($amount)
    {
        Support_TicketHelper::selectGames($amount);
    }

    /**
     * @Given /^I create top7 list$/
     */
    public function iCreateTop7list()
    {
        Support_AdminHelper::goToBackoffice();
        Support_AdminHelper::goToTop7Page();
        Support_AdminHelper::disableAllTop7Lists();
        Support_AdminHelper::createTop7List();
    }

    /**
     * @Given /^I create main event$/
     */
    public function iCreateMainEvent()
    {
        Support_Helper::loginUnderAccount();

        Support_GoPage::game();

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#sport-page- h2.sport-title')));

        $tournament_title = $this->driver->findElement(WebDriverBy::cssSelector('h3.tournament-title'))->getText();

        Support_AdminHelper::goToBackoffice();
        Support_AdminHelper::goToMainEventsPage();
        Support_AdminHelper::disableAllMainEvents();
        Support_AdminHelper::createMainEvent( $tournament_title);

        Support_AdminHelper::logoutFromBackoffice();
    }

    /**
     * @When /^I open main event$/
     */
    public function iOpenMainEvent()
    {
        $this->driver->wait(120000)->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.slick-track .left .text')));
        $this->driver->findElement(WebDriverBy::cssSelector('.slick-track .left a.btn_green'))->click();

        $gid = key(Support_Registry::singleton()->arGames[0]->getArGames());
        $this->driver->wait(120000)->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.outcome[data-match="'.$gid.'"]')));
    }

    /**
     * @Then /^"([^"]*)" not available$/
     */
    public function notAvailable($ticket_type)
    {
        sleep(2);   //todo should wait until tab with ticket type updated but tab can stay same and this is not error
        $this->driver->findElement(WebDriverBy::cssSelector("#current_ticket div.not_active[index=$ticket_type]"));
    }

    /**
     * @Then /^"([^"]*)" available$/
     */
    public function available($ticket_type)
    {
        Support_TicketHelper::available($ticket_type);
    }

    /**
     * @Given /^I can create "([^"]*)" ticket$/
     */
    public function iCanCreateTicket($ticket_type)
    {
        $waitQuota = true;

        if ($ticket_type === 'double') {
            $waitQuota = false;
        }

        // check that ticket type available and selected
        $this->available($ticket_type);
        $this->selectTicketType($ticket_type, $waitQuota);

        if ($ticket_type === 'double') {
            $this->placeBetToEachGames(Support_Registry::singleton()->arGames[0], true);
        }

        $stake = $this->driver->findElement(WebDriverBy::id('ticket_winning'))->getText();
        if (((int)($stake) < 1) && ((int)(Support_Registry::singleton()->stake) < 1)) {

            $this->driver->findElement(WebDriverBy::cssSelector('#ticket_stake_buttons li.ticket-stake-set[data-val="10"]'))->click();

            // make max stake       //todo https://redmine.rssystems.ru/issues/3118
        }

        // store ticket quota
        Support_Registry::singleton()->ticketQuota = $this->driver->findElement(WebDriverBy::cssSelector('.score.data-ticket-quota'))->getText();

        // create ticket
        $this->doClickToCreateTicket();

        $this->checkTicketType($ticket_type);
    }

    /**
     * @Given /^I place double bet to "([^"]*)" game$/
     */
    public function iPlaceDoubleBetToGame($control)
    {
        if ($control === "last outcome in each") {
            $this->placeBetToEachGames(Support_Registry::singleton()->arGames[0], 'last');
        } else if ($control === "each") {
            $this->placeBetToEachGames(Support_Registry::singleton()->arGames[0], true);
        } else if ($control === 'draw outcome of first'){
            $this->placeBetToEachGames(Support_Registry::singleton()->arGames[0], 'draw of first');
        } else {
            PHPUnit_Framework_Assert::assertTrue(false);
        }
    }

    public function selectTicketType($ticket_type, $waitQuota = true)
    {
        Support_TicketHelper::selectTicketType($ticket_type, $waitQuota);
    }

    public function placeBetToEachGames(Support_MatchClass $__testData, $double = false, $skip_game_id = null)
    {
        Support_TicketHelper::placeBetToEachGames($__testData, $double);
    }

    public function doClickToCreateTicket()
    {
        Support_TicketHelper::doClickToCreateTicket();
    }

    private function checkTicketType($expected_ticket_type)
    {

        // open ticket details
        $arTickets = $this->driver->findElements(WebDriverBy::cssSelector('#last_user_tickets div[onclick*="open_ticket_details"]'));
        $arTickets[0]->click();


        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content .popup-ticket-details')));

            $this->waitForTicketDetailWindowLoad();
            $ticket_head = $this->driver->findElement(WebDriverBy::cssSelector('.ticketHead .ticketType'))->getText();
            $ticket_head = strtolower($ticket_head);

            PHPUnit_Framework_Assert::assertContains(strtolower($expected_ticket_type), $ticket_head);

            Support_Registry::singleton()->ticket = $this->driver->findElement(WebDriverBy::cssSelector('.ticketDetail .ticketCoupon span.value'))->getText();
            Support_Registry::singleton()->ticket = str_replace('\'', '', Support_Registry::singleton()->ticket);

        // close popup
        $this->driver->findElement(WebDriverBy::cssSelector('#modal_content .modal_body_close .fa-close'))->click();
        usleep(200000);
    }

    public function waitForTicketDetailWindowLoad()
    {
        Support_TicketHelper::waitForTicketDetailWindowLoad();
    }

    public function isRegularGame($game_id)
    {
        Support_TicketHelper::isRegularGame($game_id);
    }

    public function isOutrightGame($game_id)
    {
        Support_TicketHelper::isOutrightGame($game_id);
    }

    public function selectOutrightGame($game_id)
    {
        Support_TicketHelper::selectOutrightGame($game_id);
    }

    /**
     * @Given /^I switch ticket to "([^"]*)" type$/
     */
    public function iSwitchTicketToType($ticket_type)
    {
        Support_TicketHelper::selectTicketType($ticket_type, false);
    }

    /**
     * @Then /^bonuses "([^"]*)" available$/
     */
    public function bonusesAvailable($bonus_amount)
    {
        sleep(1);
        if ($bonus_amount === '0') {
            Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.ticket-block-head .ticket-bonus-info.discount0'));
        } elseif ($bonus_amount === '5') {
            Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.ticket-block-head .ticket-bonus-info.discount5'));
        } else {
            PHPUnit_Framework_Assert::assertTrue(false);
        }
    }

    /**
     * @Given /^I create "([^"]*)" ticket$/
     */
    public function iCreate_type_Ticket($ticket_type)
    {
        $this->iCanCreateTicket($ticket_type);
    }

    /**
     * @Given /^I click to create "([^"]*)" ticket$/
     */
    public function iClickToCreateTicket($ticket_type)
    {
        // create ticket
        $this->doClickToCreateTicket();

        $this->checkTicketType($ticket_type);
    }

    /**
     * @When /^All bets in ticket is win$/
     */
    public function allBetsInTicketIsWin()
    {
        $arGames = Support_Registry::singleton()->arGames[0]->getArGames();

        foreach ($arGames as $gid => $id) {
            $this->markGameAsWin($gid);
        }
    }

    public function markGameAsWin($gid)
    {
        Support_AdminHelper::markGame($gid, 'won');
        Support_Helper::processTicketQueue();
    }

    public function markGameAsDraw($gid)
    {
        Support_AdminHelper::markGame( $gid, 'draw');
        Support_Helper::processTicketQueue();
    }

    public function markGameAsLose($gid)
    {
        Support_AdminHelper::markGame($gid, 'lose');
        Support_Helper::processTicketQueue();
    }

    /**
     * @Then /^Check in public interface that "([^"]*)" ticket is "([^"]*)"$/
     */
    public function checkInPublicInterfaceThatTicketIs($ticket_type, $ticket_status)
    {
        Support_TicketHelper::checkInPublicInterfaceThatTicketIs($ticket_type, $ticket_status);
    }

    /**
     * @When /^I open ticket$/
     */
    public function iOpenTicket()
    {
        return Support_TicketHelper::openTicket();
    }

    /**
     * @Given /^Ticket quota is "([^"]*)"$/
     */
    public function ticketQuotaIs($expected_ticket_quota)
    {
        $current_ticket_quota = $this->driver->findElement(WebDriverBy::cssSelector('div.score.data-ticket-quota'))->getText();
        PHPUnit_Framework_Assert::assertEquals($expected_ticket_quota, $current_ticket_quota);
    }

    /**
     * @Given /^Ticket detail is: payoff "([^"]*)", bonus "([^"]*)", stake "([^"]*)"$/
     */
    public function ticketDetailIsPayoffBonusStake($expected_payoff, $expected_bonus, $expected_stake)
    {
        if (Support_Helper::isMobile()) {
            $this->driver->get(Support_Configs::get()->MOBILE_BASE_URL.'#/index.php?ac=mobile/ticket/list');
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/ticket/list"]')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .ticket_list .ticket-item')));
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page .ticket_list .ticket-item[onclick*="go_to_ticket"]'))->click();

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened .ticketStatus')));
            sleep(1);

            $ticket_status = $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .ticketStatus'))->getText(); //WAITING APPROVING
            $ticket_payoff_details = str_replace('\'', '', $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .ticketPayoff'))->getText()); // 30000 (Wette 25000 + Bonus 20% 5000)
            $max_payoff = str_replace('\'', '', $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .ticketMaximumPayoff .value'))->getText());
            $bonuses = str_replace('\'', '', $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .ticketBonus'))->getText()); //*ist inklusive bonus 20%: 5000
            $curr_stake = $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .ticketBet.item .value'))->getText();

            // close ticket details
            $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .popup_close'))->click();
            usleep(150000);
        } else {
            Support_Helper::openMainPage();
            $this->iOpenTicket();

            usleep(120000);

                $ticket_status = $this->driver->findElement(WebDriverBy::cssSelector('div.ticketStatus'))->getText(); //WAITING APPROVING
                $ticket_payoff_details = str_replace('\'', '', $this->driver->findElement(WebDriverBy::cssSelector('div.ticketPayoff'))->getText()); // 30000 (Wette 25000 + Bonus 20% 5000)
                $max_payoff = str_replace('\'', '', $this->driver->findElement(WebDriverBy::cssSelector('div.ticketMaximumPayoff .value'))->getText());
                $bonuses = str_replace('\'', '', $this->driver->findElement(WebDriverBy::cssSelector('.ticketBonus.item'))->getText()); //*ist inklusive bonus 20%: 5000
                $curr_stake = $this->driver->findElement(WebDriverBy::cssSelector('.ticketBet.item'))->getText();

            // wait for pop up close
            // close popup
            $this->driver->findElement(WebDriverBy::cssSelector('#modal_content .modal_body_close .fa-close'))->click();
            usleep(200000);

        }

        PHPUnit_Framework_Assert::assertContains('WAITING APPROVING', strtoupper($ticket_status));
        PHPUnit_Framework_Assert::assertContains($expected_payoff, $ticket_payoff_details);
        PHPUnit_Framework_Assert::assertContains($expected_bonus, $ticket_payoff_details);
        PHPUnit_Framework_Assert::assertEquals($expected_payoff, $max_payoff);
        PHPUnit_Framework_Assert::assertContains($expected_bonus, $bonuses);
        PHPUnit_Framework_Assert::assertContains($expected_stake, $curr_stake);

    }

    /**
     * @Given /^Possible winning is "([^"]*)"$/
     */
    public function possibleWinningIs($expected_winning)
    {
        sleep(1);
        if (Support_Helper::isMobile()) {
            $win_sum = $this->driver->findElement(WebDriverBy::cssSelector('.active_page #max_winning'))->getText();
        } else {
            $win_sum = $this->driver->findElement(WebDriverBy::id('ticket_winning'))->getText();
        }
        PHPUnit_Framework_Assert::assertEquals($expected_winning, $win_sum);
    }

    /**
     * @Given /^I can payout cash$/
     */
    public function iCanPayoutCash()
    {
        Support_Helper::openAccountPage();

        //And Click to withdraw button
        $this->clickToWithdrawButton();
    }

    /**
     * @When /^One game win but other games is lose$/
     */
    public function oneGameWinButOtherGamesIsLose()
    {
        $arGames = Support_Registry::singleton()->arGames[0]->getArGames();
        $oneGameWin = false;

        foreach ($arGames as $game) {
            $game = str_replace('r', '', $game);

            if (!$oneGameWin) {
                $this->markGameAsWin($game);
                $oneGameWin = true;
            } else {
                $this->markGameAsLose($game);
            }

        }


        // process ticket queue
        PHPUnit_Framework_Assert::assertTrue(Support_Helper::processTicketQueue());
    }

    /**
     * @When /^All bets in ticket is lose$/
     */
    public function allBetsInTicketIsLose()
    {
        $arGames = Support_Registry::singleton()->arGames[0]->getArGames();

        foreach ($arGames as $gid => $id) {
            $this->markGameAsLose($gid);
        }

        // process ticket queue
        PHPUnit_Framework_Assert::assertTrue(Support_Helper::processTicketQueue());
    }

    /**
     * @Given /^I open livebet page$/
     */
    public function iOpenLivebetPage()
    {
        $this->driver->get(Support_Configs::get()->BASE_URL.'index.php?ac=user/lb/index');
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.sport_matches .sport-soccer .td-odds')));
    }

    /**
     * @Given /^I place bet to each livebet game$/
     */
    public function iPlaceBetToEachLivebetGame()
    {
        $arGames = Support_Registry::singleton()->arGames[0]->getArGames();

        if (Support_Helper::isMobile()) {
            foreach ($arGames as $gid => $__id) {
                $this->driver->get(Support_Configs::get()->MOBILE_BASE_URL . '#/index.php?ac=mobile/livebet/match/detail&sport_id=1&match_id='.$gid);
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/livebet/match/detail&sport_id=1&match_id='.$gid.'"]')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .e-item[key="'.$gid.'"]')));
                $tips_count = $this->driver->findElement(WebDriverBy::cssSelector('#head-right-icons .selected_bets_amount'))->getText();
                $this->driver->findElement(WebDriverBy::cssSelector('.active_page .e-item[key="'.$gid.'"] .e-odd-outcome[index="1"]'))->click();
                Support_Wait::forTextUpdated('#head-right-icons .selected_bets_amount', $tips_count);
            }
        } else {
            foreach ($arGames as $gid => $__id) {
                $this->driver->get(Support_Configs::get()->BASE_URL . 'index.php?ac=user/lb/index#match' . $gid . 'page1');
                $selector = WebDriverBy::cssSelector('#odds_list_area div.clickable[data-match_id="' . $gid . '"]');
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated($selector));
                usleep(150000);
                $tips_count = $this->driver->findElement(WebDriverBy::cssSelector('#ticket_tips'))->getText();
                $this->driver->findElement($selector)->click();

                Support_Wait::forTextUpdated('#ticket_tips', $tips_count);
            }
        }
    }
}