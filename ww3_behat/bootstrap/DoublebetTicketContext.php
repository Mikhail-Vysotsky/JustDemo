<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 07.12.15
 * Time: 17:14
 */

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;


class DoublebetTicketContext implements Context, SnippetAcceptingContext
{
    /**
     * @var array
     */
    public $arQuotas;
    /**
     * @var array
     */
    public $arVariantsQuota;
    public $max_payoff;
    public $max_quota;
    public $bet_stake;

    function __construct()
    {
        $this->driver = Support_Registry::singleton()->driver;
//        $this->arGames = Support_Registry::singleton()->arGames;
    }

    /**
     * @Then /^I can't place double bet to "([^"]*)" game$/
     */
    public function iCanTPlaceDoubleBetToGame($fork_arg)
    {
        if ($fork_arg == '2way') {
            $two_way_mc = Support_Registry::singleton()->arGames[1];
            $gid = key($two_way_mc->getArGames());

            $arButtons = $this->driver->findElements(WebDriverBy::cssSelector('#ticket_bets div.tb[index="' . $gid . '"] .double-bet-b'));
            $arButtons_selected = $this->driver->findElements(WebDriverBy::cssSelector('#ticket_bets div.tb[index="' . $gid . '"] .double-bet-b.selected'));

            PHPUnit_Framework_Assert::assertEquals(2, count($arButtons)); // check match outcomes exist
            PHPUnit_Framework_Assert::assertEquals(1, count($arButtons_selected));    // check selected games
        }

        if ($fork_arg == 'first') {
            $arGames = Support_Registry::singleton()->arGames[0];
            $gid = key($arGames->getArGames());

//            $css_selector = '#ticket_bets div[index="' . $gid . '"] .double-bet-b[index="2"]';
            $css_selector = '#ticket_bets div[index="' . $gid . '"] .double-bet-b[index="1"]';
            $this->driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector($css_selector)
                )
            );
            $this->driver->findElement(WebDriverBy::cssSelector($css_selector))->click();
            $this->driver->findElement(WebDriverBy::id('ticket_insert_button'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-success-msg.alert-msg')));
            $msg_text = $this->driver->findElement(WebDriverBy::cssSelector('.popup-success-msg.alert-msg'))->getText();
            PHPUnit_Framework_Assert::assertContains('is locked', $msg_text);
//            $class_attr = $this->driver->findElement(WebDriverBy::cssSelector($css_selector))->getAttribute('class');
//            sleep(9999999);
//            PHPUnit_Framework_Assert::assertNotContains('selected', $class_attr);
//            PHPUnit_Framework_Assert::assertContains('not_active', $class_attr);
        }

        if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#modal_content #custom_alert_btn'), false)) {
            $this->driver->findElement(WebDriverBy::cssSelector('#modal_content #custom_alert_btn'))->click();
            usleep(250000);
        }

        if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#custom_alert_btn[onclick*="close_custom_alert"]'), false)) {
            $this->driver->findElement(WebDriverBy::cssSelector('#custom_alert_btn[onclick*="close_custom_alert"]'))->click();
            usleep(250000);
        }
    }

    /**
     * @Given /^"([^"]*)" game have a locked outcome on "([^"]*)" game$/
     */
    public function gameHaveALockedOutcomeOnGame($game, $outcome)
    {
        $gid = 0;   // game ID
        $url = Support_Registry::singleton()->arGames[0]->getUrlRegular();
        // $outcome - outcome index. '1', 'X' or '2'
        if ($game === 'first') {
            $arGames = Support_Registry::singleton()->arGames[0];
            $gid = key($arGames->getArGames());
        }

//        AdminHelper::goToBackoffice($this);
//        $this->changed_limits = AdminHelper::setTipLimits($this, '*', '1000');

        // lock game outcome
        $isLocked = false;
        while (!$isLocked) {
            $this->driver->get($url);

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.data-ticket-full_bets_amount_label')));
            sleep(2);
            $ticket_bets = $this->driver->findElement(WebDriverBy::cssSelector('.data-ticket-full_bets_amount_label'))->getText();

            $outcome_attr = $this->driver->findElement(WebDriverBy::cssSelector('div.outcome[data-match="' . $gid . '"][data-outcome="' . $outcome . '"]'))->getAttribute('class');
            if (strpos($outcome_attr, 'deactive') !== false || strpos($outcome_attr, 'locked') !== false) {
                $isLocked = true;
                continue;
            }
            // select outcome
            $this->driver->findElement(WebDriverBy::cssSelector('div.outcome[data-match="' . $gid . '"][data-outcome="' . $outcome . '"]'))->click();
            Support_Wait::forTextUpdated( '.data-ticket-full_bets_amount_label', $ticket_bets);

            // set stake
//            $this->driver->findElement(WebDriverBy::id('ticket_stake'))->clear();
//            $this->driver->findElement(WebDriverBy::id('ticket_stake'))->sendKeys('500');
            $this->driver->findElement(WebDriverBy::id('ticket_allin_button'))->click();


            // create ticket
            $success_text = Support_TicketHelper::doClickToCreateTicket();

            if (strpos($success_text, 'is locked') !== false) $isLocked = true;
        }

    }

    /**
     * @When /^I store outcomes of ticket$/
     */
    public function iStoreOutcomesOfTicket()
    {
        // get all games id
//        $arGames = array_keys($this->arTestData[0]->getArGames());
//
//        // store quota for each game
//        foreach ($arGames as $gid) {
//            $arElements = $this->driver->findElements(WebDriverBy::cssSelector('.double-bet-b.selected[data-match_id="' . $gid . '"]'));
//
//            $this->arQuotas[$gid] = array();
//
//            foreach ($arElements as $el) {
//                $outcome_idx = $el->getAttribute('index');
//                $outcome_quo = $el->findElement(WebDriverBy::cssSelector('b.q'))->getText();
//
//                $this->arQuotas[$gid][$outcome_idx] = $outcome_quo;
//            }
//        }

        return true;
    }

    /**
     * @Then /^Quota of each variant is a product of outcomes quota$/
     */
    public function quotaOfEachVariantIsAProductOfOutcomesQuota()
    {
        // open ticket details
        $this->driver->findElement(WebDriverBy::cssSelector('#last_user_tickets div[onclick*="open_ticket_details"]'))->click();
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content .popup-ticket-details')));

        // wait for new window
        Support_TicketHelper::waitForTicketDetailWindowLoad();
        $arVariants = $this->driver->findElements(WebDriverBy::cssSelector('tr.variant'));

        // store quotas
        $arBetItems = $this->driver->findElements(WebDriverBy::cssSelector('.betItem'));
        $game_count = 0;
        foreach ($arBetItems as $bet) {
            $d_quota = $bet->findElement(WebDriverBy::cssSelector('.betQuota.doubleBet'))->getText();
            $ex_arr = explode('/', $d_quota);
            $_q1 = $ex_arr[0];
            $_q2 = $ex_arr[1];

            $this->arQuotas[$game_count]['1'] = $_q1;
            $this->arQuotas[$game_count]['2'] = $_q2;
            $game_count++;
        }

        foreach ($arVariants as $variant) {
            $odd = doubleval(str_replace('\'', '', $variant->findElement(WebDriverBy::cssSelector('td.quota'))->getText()));
//            $payoff = doubleval(str_replace('\'', '', $variant->findElement(WebDriverBy::cssSelector('td.payoff'))->getText()));


            // calculate
            $c_quota = 1;
            $ar_outcomes = $variant->findElements(WebDriverBy::cssSelector('td.tip div'));
            for ($_i = 0; $_i < 4; $_i++) {

                if ($ar_outcomes[$_i]->getText() === '1') {
                    $c_quota *= $this->arQuotas[$_i]['1'];
                } else {
                    $c_quota *= $this->arQuotas[$_i]['2'];
                }
            }
            $c_quota = round($c_quota, 2);

            $this->arVariantsQuota[] = $c_quota;
            PHPUnit_Framework_Assert::assertEquals($c_quota, $odd);
        }

        $this->max_payoff = doubleval(str_replace('\'', '', $this->driver->findElement(WebDriverBy::cssSelector('.ticketMaximumPayoff span.value'))->getText()));
        $this->bet_stake = round(doubleval(100 / 16), 2);
//        $this->bet_stake = 5;
        $this->max_quota = round($this->max_payoff / $this->bet_stake, 2);

        // close popup
        $this->driver->findElement(WebDriverBy::cssSelector('#modal_content .modal_body_close .fa-close'))->click();
        usleep(200000);
    }

    /**
     * @Given /^Maximum payoff is a maximum payoff by variant$/
     */
    public function maximumPayoffIsAMaximumPayoffByVariant()
    {
        // find greatest variant quota
        $greatest = 0;
        foreach ($this->arVariantsQuota as $v_quo) {
            if ($v_quo > $greatest) $greatest = $v_quo;
        }

        // check maximum payoff
        PHPUnit_Framework_Assert::assertEquals(round($greatest * $this->bet_stake, 0), round($this->max_payoff, 0));
    }

    /**
     * @When /^First game win but other games is draw$/
     */
    public function firstGameWinButOtherGamesIsDraw()
    {
        $arGames = Support_Registry::singleton()->arGames[0]->getArGames();
        $first = true;

        foreach ($arGames as $gid => $id) {
            if ($first) {
                Support_AdminHelper::markGame($gid, 'won');
                Support_Helper::processTicketQueue();
                $first = false;
            } else {
                Support_AdminHelper::markGame($gid, 'draw');
                Support_Helper::processTicketQueue();
            }
        }
    }

    /**
     * @Given /^I can switch ticket to "([^"]*)" type$/
     */
    public function iCanSwitchTicketToType($type)
    {
        Support_TicketHelper::selectTicketType($type, false);
    }


}