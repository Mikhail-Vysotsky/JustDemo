<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 14.12.15
 * Time: 9:24
 */

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;


class SingleTicketContext implements Context, SnippetAcceptingContext
{
    private $driver;
    private $account;

    function __construct()
    {
        $this->driver = Support_Registry::singleton()->driver;
        $this->account = Support_Registry::singleton()->account;
    }

    /**
     * @Then /^Single ticket quota is arithmetical mean of bets quota$/
     */
    public function singleTicketQuotaIsArithmeticalMeanOfBetsQuota()
    {
        Support_TicketHelper::available('single');
        Support_TicketHelper::selectTicketType('single');

        // get data about quota
        $arBetsQuotas = $this->getEachBetQuota();
        $ticketQuota = (double)$this->driver->findElement(WebDriverBy::cssSelector('#ticket_block .ticket-bonus-info div.data-ticket-quota'))->getText();

//        $quotaCount = count($arBetsQuotas);
        $sum = 0;
        foreach ($arBetsQuotas as $betQuota) {
            str_replace('team away', '', $betQuota);
            str_replace(' ', '', $betQuota);
            var_dump($betQuota);
            $sum += $betQuota;
        }

        $meanQuota = $sum / count($arBetsQuotas);
//        var_dump($meanQuota, $sum, count($arBetsQuotas));

        $quotaDiff = (double)$ticketQuota - (double)$meanQuota;

        PHPUnit_Framework_Assert::assertTrue($quotaDiff <= 0.01);
        PHPUnit_Framework_Assert::assertTrue($quotaDiff >= -0.01);
    }

    private function getEachBetQuota()
    {
        $quota_css_selector = '.regular .odds .outcome.selected';
        $arQuota = [];
        $arElements = $this->driver->findElements(WebDriverBy::cssSelector($quota_css_selector));

        foreach ($arElements as $element) {
            $arQuota[] = trim(str_replace(array('team away', 'team home'), '', $element->getText()));
        }

        if (count($arQuota) === 0) {
            throw new \WebDriver\Exception\NoSuchElement($quota_css_selector);
        } else {
            return $arQuota;
        }
    }

    /**
     * @Then /^Ticket quota is equal to bet quota$/
     */
    public function ticketQuotaIsEqualToBetQuota()
    {
        $this->singleTicketQuotaIsArithmeticalMeanOfBetsQuota();
    }

    /**
     * @Then /^Won amount is equal to won bet multiply to 1\/3 of ticket stake$/
     */
    public function wonAmountIsEqualToWonBetMultiplyTo1OfTicketStake()
    {
        $this->checkWinTicket('one_bet');
    }

    /**
     * @Then /^Win by ticket is equal to sum of each win bets$/
     */
    public function winByTicketIsEqualToSumOfEachWinBets()
    {
        $this->checkWinTicket('all_bets');
    }

    private function checkWinTicket($wins_fork)
    {
        $bet_count = 0;
        $won_bets_count = 0;
        $bet_quota = [];

        Support_Helper::openMainPage();

        $this->driver->wait()->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
//                WebDriverBy::cssSelector('div.ticket-list-item[onclick*="' . Support_Registry::singleton()->ticket . '"]')
                WebDriverBy::cssSelector('div.ticket-list-item[onclick]')
            )
        );

//        $this->driver->findElement(WebDriverBy::cssSelector('div.ticket-list-item[onclick*="' . Support_Registry::singleton()->ticket . '"]'))->click();
        $this->driver->findElement(WebDriverBy::cssSelector('div.ticket-list-item[onclick]'))->click();
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content .popup-ticket-details')));

        Support_TicketHelper::waitForTicketDetailWindowLoad();

        // find won bet
        $arBets = $this->driver->findElements(WebDriverBy::cssSelector('.ticketBets .betItem'));
        foreach ($arBets as $item) {
            $betStatus = $item->findElement(WebDriverBy::cssSelector('.betStatus'))->getText();
            if (strtolower($betStatus) === 'won') {
                $bet_quota[] = $item->findElement(WebDriverBy::cssSelector('.betQuota'))->getText();
                $won_bets_count++;
            }
            $bet_count++;
        }

        $ticket_status = $this->driver->findElement(WebDriverBy::cssSelector('div.ticketStatus'))->getText();
        $won_sum = $this->driver->findElement(WebDriverBy::cssSelector('div.ticketPayoff'))->getText();

        // close popup
        $this->driver->findElement(WebDriverBy::cssSelector('#modal_content .modal_body_close .fa-close'))->click();
        usleep(200000);

        PHPUnit_Framework_Assert::assertEquals(1, $won_bets_count);
        $expected_won = '';
        if ($wins_fork === 'one_bet') {
            // calculate expected won sum
            $expected_won = (Support_Registry::singleton()->stake / $bet_count) * $bet_quota[0];
        }

        if ($wins_fork === 'all_bets') {
            foreach ($bet_quota as $quota) {
                $expected_won = +(Support_Registry::singleton()->stake / $bet_count) * $quota;
            }
        }

        // check ticket status
        PHPUnit_Framework_Assert::assertContains('won', strtolower($ticket_status), "ticket Status is '$ticket_status', but excpected 'won'");

        // check win amount
        $expected_diff = $won_sum - $expected_won;
        PHPUnit_Framework_Assert::assertTrue($expected_diff <= 0.01, "to many difference between calculated and real won amount: $expected_diff");
        PHPUnit_Framework_Assert::assertTrue($expected_diff >= -0.01, "to many difference between calculated and real won amount: $expected_diff");
    }
}