<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 14.12.15
 * Time: 9:24
 */

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;


class MultiTicketContext implements Context, SnippetAcceptingContext
{
    private $driver;
    private $account;

    function __construct()
    {
        $this->driver = Support_Registry::singleton()->driver;
        $this->account = Support_Registry::singleton()->account;
    }

    /**
     * @Then /^Multi ticket quota is equals to multiply of each bets quota$/
     */
    public function multiTicketQuotaIsEqualsToMultiplyOfEachBetsQuota()
    {
        $arTicket = $this->getTicketData();

        $expectedTicketQuota = $this->calculateMultiTicketQuota($arTicket['arBetsQuota']);

        $expected_quota_diff = $expectedTicketQuota - Support_Registry::singleton()->ticketQuota;

        PHPUnit_Framework_Assert::assertTrue($expected_quota_diff <= 0.1, "to many difference between calculated and real ticket quota: $expected_quota_diff");
        PHPUnit_Framework_Assert::assertTrue($expected_quota_diff >= -0.1, "to many difference between calculated and real ticket quota: $expected_quota_diff");

        $calculatedWon = $arTicket['stake'] * $expectedTicketQuota;
        $expected_won_diff = $calculatedWon - $arTicket['maximumWonAmount'];

        PHPUnit_Framework_Assert::assertTrue($expected_won_diff <= 0.1, "to many difference between calculated and real possible ticket win amount: $expected_won_diff");
        PHPUnit_Framework_Assert::assertTrue($expected_won_diff >= -0.1, "to many difference between calculated and real possible ticket win amount: $expected_won_diff");
    }

    private function calculateMultiTicketQuota($arBetsQuotas)
    {
        $result = 1;

        foreach ($arBetsQuotas as $betQuota) {
            $result = $result * $betQuota;
        }

        return $result;
    }

    /**
     * @Then /^Win by multi ticket is equal to multiply ticket quota and ticket stake$/
     */
    public function winByMultiTicketIsEqualToMultiplyTicketQuotaAndTicketStake()
    {
        $arTicket = $this->getTicketData();
        $ticketStatus = strtolower($arTicket['ticket_status']);
        $won_sum = $arTicket['won_sum'];
        $ticket_quota = $arTicket['ticket_quota'];
        $stake = $arTicket['stake'];


        $expextedWonDiff = $won_sum - ($ticket_quota * $stake);
        if ($ticketStatus === 'won' || $ticketStatus === 'waiting approving') {
            PHPUnit_Framework_Assert::assertTrue(true);
        } else {
            PHPUnit_Framework_Assert::assertTrue(strtolower($ticketStatus) === 'won', 'wrong ticket status. current status: '.$ticketStatus.', but expected statys: won or waiting approving');
        }
//        PHPUnit_Framework_Assert::assertTrue(strtolower($ticketStatus) === 'waiting approving', 'wrong ticket status. current status: '.$ticketStatus.', but expected statys: waiting approving');
        PHPUnit_Framework_Assert::assertTrue($expextedWonDiff <= 0.01, "to many difference between calculated and real tiket won sum: $expextedWonDiff");
        PHPUnit_Framework_Assert::assertTrue($expextedWonDiff >= -0.01, "to many difference between calculated and real tiket won sum: $expextedWonDiff");

    }

    private function getTicketData()
    {
        $arBetsQuotas = [];
        $ticket_status = '';
        $won_sum = 0;

        Support_Helper::openMainPage();

//        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#last_user_tickets .ticket-list-item[onclick*="'.Support_Registry::singleton()->ticket.'"]')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#last_user_tickets .ticket-list-item[onclick]')));

//        $this->driver->findElement(WebDriverBy::cssSelector('.ticket-list-item[onclick*="' . Support_Registry::singleton()->ticket . '"]'))->click(); // new ticket num is a hash
        $this->driver->findElement(WebDriverBy::cssSelector('.ticket-list-item[onclick]'))->click();
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content .popup-ticket-details')));

        Support_TicketHelper::waitForTicketDetailWindowLoad();

        // find won bet
        $arBets = $this->driver->findElements(WebDriverBy::cssSelector('.ticketBets .betItem'));
        foreach ($arBets as $item) {
            $arBetsQuotas[] = $item->findElement(WebDriverBy::cssSelector('.betQuota'))->getText();
        }

        $stake_str = $this->driver->findElement(WebDriverBy::cssSelector('.ticketBet span.value'))->getText();
        $per_bet = $this->driver->findElement(WebDriverBy::cssSelector('.ticketBet span.value .amount-per-bet'))->getText();
        $stake_str = str_replace($per_bet, '', $stake_str);
        $stake = preg_replace("/[^0-9,.]/", "", $stake_str);

        $maximumWonAmount = $this->driver->findElement(WebDriverBy::cssSelector('.ticketMaximumPayoff.item span.value'))->getText();
        $maximumWonAmount = (double)str_replace('\'', '', $maximumWonAmount);

        // try to get ticket property
        try {
            $ticket_status = $this->driver->findElement(WebDriverBy::cssSelector('div.ticketStatus'))->getText();
            $won_sum = $this->driver->findElement(WebDriverBy::cssSelector('div.ticketPayoff'))->getText();
        } catch (NoSuchElementException $e) {
        }

        // close popup
        $this->driver->findElement(WebDriverBy::cssSelector('#modal_content .modal_body_close .fa-close'))->click();
        usleep(200000);



        $arResult = [
            'maximumWonAmount' => $maximumWonAmount,
            'stake' => $stake,
            'arBetsQuota' => $arBetsQuotas,
            'ticket_status' => $ticket_status,
            'won_sum' => $won_sum,
            'ticket_quota' => $this->calculateMultiTicketQuota($arBetsQuotas),
        ];
        return $arResult;
    }

    /**
     * @When /^One game lose but other games is win$/
     */
    public function oneGameLoseButOtherGamesIsWin()
    {
        $arGames = Support_Registry::singleton()->arGames[0]->getArGames();
        $first = true;

        foreach ($arGames as $gid => $id) {
            if ($first) {
                Support_AdminHelper::markGame( $gid, 'lose');
                Support_Helper::processTicketQueue();

                $first = false;
            } else {
                Support_AdminHelper::markGame( $gid, 'won');
                Support_Helper::processTicketQueue();
            }
        }
    }
}