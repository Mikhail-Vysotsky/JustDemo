<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 23.12.15
 * Time: 9:24
 */

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class SystemTicketContext implements Context, SnippetAcceptingContext
{
    private $arExistingVariantsQuota;
    private $arExistingVariantsPayoff;
    private $maxPayoff;

    private $driver;
    private $account;

    function __construct()
    {
        $this->driver = Support_Registry::singleton()->driver;
        $this->account = Support_Registry::singleton()->account;
    }

    /**
     * @Given /^I select bet to "([^"]*)" games$/
     */
    public function iSelectBetToGames($control)
    {
        $type = null;
        if ($control === "each" || $control === "each first") {
            foreach (Support_Registry::singleton()->arGames as $games) {
                if (!($games instanceof Support_MatchClass)) {
                    continue;
                }

                // open games page if user on other any page
                $curr_url = str_replace('https', 'http', $this->driver->getCurrentURL());
                if ($curr_url !== $games->getUrlRegular()) {
                    $this->driver->get($games->getUrlRegular());
                }

                $this->waitQuota();

                Support_TicketHelper::placeBetToGamesOnPage( $control, $type);
            }
        } elseif ($control === 'last') {
            $this->placeBetToGames($control);
        } elseif ($control === "eleventh") {
                $this->placeBetToGames(11);
        } elseif ($control < 11) {
            $this->placeBetToGames((int)$control);
        }
    }

    private function placeBetToGames($count)
    {
        if ($count === 'last') $count = 9999;

        $arGames = Support_Registry::singleton()->arGames[0]->getArGames();
        $this->driver->get(Support_Registry::singleton()->arGames[0]->getUrlRegular());
        if ($count <= 10) {
            $cnt = 0;
            foreach ($arGames as $game_id => $row_id) {
                $cnt++;
                if ($count == 10 && $cnt > 10) {
                    return true;
                } elseif ($count < 10 && $cnt > $count) {
                    return true;
                }


                $ticket_tips = $this->driver->findElement(WebDriverBy::id('ticket_rows'))->getText();
                $wd_game = WebDriverBy::cssSelector('.regular .odds div.outcome[data-match="'.$game_id.'"][data-outcome="1"]');
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated($wd_game));

                $this->driver->findElement($wd_game)->click();
                Support_Wait::forTextUpdated( '#ticket_tips', $ticket_tips);
            }
        } elseif ($count === 11) {
            $cnt = 0;
            foreach ($arGames as $game_id => $row_id) {
                $cnt++;
                if ($cnt !== 11) continue;

                $ticket_tips = $this->driver->findElement(WebDriverBy::id('ticket_rows'))->getText();
                $wd_game = WebDriverBy::cssSelector('.regular .odds div.outcome[data-match="'.$game_id.'"][data-outcome="1"]');
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated($wd_game));

                $this->driver->findElement($wd_game)->click();
                Support_Wait::forTextUpdated( '#ticket_tips', $ticket_tips);
            }

        } elseif ($count === 9999 /* count === last */) {
            $cnt = 0;
            $last = count($arGames);
            foreach ($arGames as $game_id => $row_id) {
                $cnt++;
                if ($cnt < $last) continue;

                $ticket_tips = $this->driver->findElement(WebDriverBy::id('ticket_rows'))->getText();
                $wd_game = WebDriverBy::cssSelector('.regular .odds div.outcome[data-match="'.$game_id.'"][data-outcome="1"]');
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated($wd_game));

                $this->driver->findElement($wd_game)->click();
                Support_Wait::forTextUpdated( '#ticket_tips', $ticket_tips);
            }
        }
    }

    private function waitQuota()
    {
        try {
            $this->driver->wait(10)->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div.quota')));
        } catch (Exception $e) {
            $this->driver->navigate()->refresh();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div.quota')));
        }

        sleep(1);
        $this->driver->wait()->until(function() {
            for ($sec = 0; $sec < 90; $sec++) {
                try {
                    if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.match-item .regular .odds'), false))
                        return 'regular';

                    if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.match-item .outright .outright-odds'), false))
                        return 'outright';
                } catch (Exception $e) {}
                sleep(1);
            }
            return false;
        }
        );
    }

    /**
     * @Given /^If i place bet to "([^"]*)" game$/
     */
    public function ifIPlaceBetToGame($control)
    {
        $this->iSelectBetToGames($control);
    }

    /**
     * @Given /^If i remove "([^"]*)" game from ticket$/
     */
    public function ifIRemoveGameFromTicket($ticket_type)
    {
        if ($ticket_type === 'outright') {

//            $elOutright = $this->driver->findElement(WebDriverBy::cssSelector('#ticket_bets div.tb[unique_id*="outright"]'));
            $ticket_tips = $this->driver->findElement(WebDriverBy::id('ticket_tips'))->getText();
            $this->driver->findElement(WebDriverBy::cssSelector('.outright-outcome.outcome.selected'))->click();
//            $elOutright->findElement(WebDriverBy::cssSelector('div.close[onclick*="remove_bet"]'))->click();
            Support_Wait::forTextUpdated( '#ticket_tips', $ticket_tips);
        } else {
            PHPUnit_Framework_Assert::assertTrue(false); // just for fail test;
        }
    }

    /**
     * @Given /^I select "([^"]*)" banks$/
     */
    public function iSelectBanks($amount)
    {
        $arBanks = $this->driver->findElements(WebDriverBy::cssSelector('#ticket_bets div.b-bank'));

        for ($i = 0; $i < $amount; $i++) {
            $ticket_rows = $this->driver->findElement(WebDriverBy::id("ticket_rows"))->getText();
            $arBanks[$i]->click();

            Support_Wait::forTextUpdated( '#ticket_rows', $ticket_rows);
        }
    }

    /**
     * @When /^All outcomes with banks win but other lose$/
     */
    public function allOutcomesWithBanksWinButOtherLose()
    {
        $this->setOutcomeResults(3);
    }

    /**
     * @When /^All outcomes with banks win and stated win is true$/
     */
    public function allOutcomesWithBanksWinAndStatedWinIsTrue()
    {
        $arGames = Support_Registry::singleton()->arGames[0]->getArGames();
        $bank_count = 2;
        $stated_count = 3;
        $cnt = 0;
        foreach ($arGames as $gid => $id) {
            if ($cnt < $bank_count || $cnt < $stated_count) {
                Support_AdminHelper::markGame($gid, 'won');
                Support_Helper::processTicketQueue();
            } else {
                Support_AdminHelper::markGame($gid, 'lose');
                Support_Helper::processTicketQueue();
            }
            $cnt++;
        }
    }

    /**
     * @Given /^I set ticket special value "([^"]*)"$/
     */
    public function iSetTicketSpecialValue($spec_val)
    {
        if ($spec_val === "3/5") {
            usleep(123000);
            $this->driver->findElement(WebDriverBy::cssSelector('.change_special_value.l'))->click();
            sleep(2);
            PHPUnit_Framework_Assert::assertEquals(2, $this->driver->findElement(WebDriverBy::id('ticket_bets_area'))->getAttribute('max_banked_amount'));
        } elseif ($spec_val === "4/5") {
            return true;
        }
    }

    /**
     * @When /^All banks and one more outcome win but other lose$/
     */
    public function allBanksAndOneMoreOutcomeWinButOtherLose()
    {
        $this->setOutcomeResults(4);
    }

    /**
     * @When /^One bank lose but other outcomes win$/
     */
    public function oneBankLoseButOtherOutcomesWin()
    {
        $first = true;
        $arGames = Support_Registry::singleton()->arGames[0]->getArGames();

        foreach ($arGames as $gid => $id) {
            if ($first) {
                Support_AdminHelper::markGame($gid, 'lose');
                Support_Helper::processTicketQueue();
                $first = false;
            } else {
                Support_AdminHelper::markGame($gid, 'won');
                Support_Helper::processTicketQueue();
            }
        }
    }

    /**
     * @Given /^I not select bankers$/
     */
    public function iNotSelectBankers()
    {
        PHPUnit_Framework_Assert::assertTrue(true);
    }

    /**
     * @When /^First three games win but other lose$/
     */
    public function firstThreeGamesWinButOtherLose()
    {
        $this->setOutcomeResults(3);
    }

    /**
     * @When /^First two game win but other lose$/
     */
    public function firstTwoGameWinButOtherLose()
    {
        $this->setOutcomeResults(2);
    }

    /**
     * @Given /^I store quota of each ticket outcome$/
     */
    public function iStoreQuotaOfEachTicketOutcome()
    {
        // get all games id
        $arGames = array_keys(Support_Registry::singleton()->arGames[0]->getArGames());

//        // store quota for each game
        foreach ($arGames as $gid) {
            $elSelectedGame = $this->driver->findElement(WebDriverBy::cssSelector('.outcome.selected[data-match="' . $gid . '"]'));

            Support_Registry::singleton()->arSelectedOutcomesQuota[$gid] = $elSelectedGame->findElement(WebDriverBy::cssSelector('div.quota'))->getText();
        }

        return true;
    }

    /**
     * @Then /^I see table where exist "([^"]*)" variant of ticket outcomes$/
     */
    public function iSeeTableWhereExistVariantOfTicketOutcomes($amount)
    {
        $driver = Support_Registry::singleton()->driver;

            $arVarQuotas = $this->driver->findElements(WebDriverBy::cssSelector('tr.variant td.quota'));
            $arVarPayoffs = $this->driver->findElements(WebDriverBy::cssSelector('tr.variant td.payoff'));
            foreach ($arVarQuotas as $quota) {
                $this->arExistingVariantsQuota[] = round($quota->getText(), 2);
            }
            foreach ($arVarPayoffs as $payoff) {
                $this->arExistingVariantsPayoff[] = round($payoff->getText(), 2);
            }

            PHPUnit_Framework_Assert::assertEquals((int)$amount, count($this->arExistingVariantsQuota));
            $this->maxPayoff = (double)str_replace('\'', '', $this->driver->findElement(WebDriverBy::cssSelector('.ticketMaximumPayoff .value'))->getText());




        // close popup
        $driver->findElement(WebDriverBy::cssSelector('#modal_content .modal_body_close .fa-close'))->click();
        usleep(200000);
    }

    private function getOutcomeQuotaVariants()
    {
        $arQuotas = array_values(Support_Registry::singleton()->arSelectedOutcomesQuota);

        $arResult[] =  round($arQuotas[0] * $arQuotas[1] * $arQuotas[2], 2);
        $arResult[] =  round($arQuotas[0] * $arQuotas[1] * $arQuotas[3], 2);
        $arResult[] =  round($arQuotas[0] * $arQuotas[1] * $arQuotas[4], 2);
        $arResult[] =  round($arQuotas[0] * $arQuotas[2] * $arQuotas[3], 2);
        $arResult[] =  round($arQuotas[0] * $arQuotas[2] * $arQuotas[4], 2);
        $arResult[] =  round($arQuotas[0] * $arQuotas[3] * $arQuotas[4], 2);
        $arResult[] =  round($arQuotas[1] * $arQuotas[2] * $arQuotas[3], 2);
        $arResult[] =  round($arQuotas[1] * $arQuotas[2] * $arQuotas[4], 2);
        $arResult[] =  round($arQuotas[1] * $arQuotas[3] * $arQuotas[4], 2);
        $arResult[] =  round($arQuotas[2] * $arQuotas[3] * $arQuotas[4], 2);

        return $arResult;
    }

    /**
     * @Given /^For each variant quota is product of each win outcome quota$/
     */
    public function forEachVariantQuotaIsProductOfEachWinOutcomeQuota()
    {
        $arVarsQuota = $this->getOutcomeQuotaVariants();

        PHPUnit_Framework_Assert::assertEquals(0, count(array_diff($arVarsQuota, $this->arExistingVariantsQuota)));
    }

    /**
     * @Given /^Maximum ticket win is a sum of all variants$/
     */
    public function maximumTicketWinIsASumOfAllVariants()
    {
        $existingPayoffSum = 0;
        foreach ($this->arExistingVariantsPayoff as $payoff) {
            $existingPayoffSum += $payoff;
        }

        $diff = $existingPayoffSum - $this->maxPayoff;
        PHPUnit_Framework_Assert::assertTrue(($diff < 0.05), 'different between expected payoff sum and real: '.$diff);
        PHPUnit_Framework_Assert::assertTrue((-0.05 < $diff), 'different between expected payoff sum and real: '.$diff);
    }

    /**
     * @Given /^Each variant contain banking events and quota of each variant is product of win outcome quota$/
     */
    public function eachVariantContainBankingEventsAndQuotaOfEachVariantIsProductOfWinOutcomeQuota()
    {
        $arVarsQuota = $this->getBankingOutcomeQuotaVariants();

        PHPUnit_Framework_Assert::assertEquals(0, count(array_diff($arVarsQuota, $this->arExistingVariantsQuota)));
    }

    private function getBankingOutcomeQuotaVariants()
    {
        $arQuotas = array_values(Support_Registry::singleton()->arSelectedOutcomesQuota);

        $arResult[] =  round($arQuotas[0] * $arQuotas[1] * $arQuotas[2], 2);
        $arResult[] =  round($arQuotas[0] * $arQuotas[1] * $arQuotas[3], 2);
        $arResult[] =  round($arQuotas[0] * $arQuotas[1] * $arQuotas[4], 2);

        return $arResult;
    }

    /**
     * @When /^First three games win and other games lose$/
     */
    public function firstThreeGamesWinAndOtherGamesLose()
    {
        $this->setOutcomeResults(3);
    }

    /**
     * @Then /^Check that ticket is "([^"]*)"$/
     */
    public function checkThatTicketIs($expected_status)
    {
        Support_Helper::openMainPage();

        Support_TicketHelper::openTicket();

        // check ticket type
        $ticket_head = $this->driver->findElement(WebDriverBy::cssSelector('.ticketHead .ticketType'))->getText();
        $ticket_head = strtolower($ticket_head);

        PHPUnit_Framework_Assert::assertContains("system", $ticket_head);

        //check ticket status
        $curr_status = $this->driver->findElement(WebDriverBy::cssSelector('.ticketStatus'))->getText();
        $curr_status = strtolower($curr_status);

            if ($curr_status === 'waiting approving') $curr_status = 'won';
            PHPUnit_Framework_Assert::assertEquals(strtolower($expected_status), $curr_status);
    }

    /**
     * @Given /^Highlight first row where first three games win in ticket$/
     */
    public function highlightFirstRowWhereFirstThreeGamesWinInTicket()
    {
            $won_row = $this->driver->findElement(WebDriverBy::cssSelector('.variant.won'));

            $won_row_payoff = (double) $won_row->findElement(WebDriverBy::cssSelector('.payoff'))->getText();
            $ticket_payoff = (double) str_replace('\'', '', $this->driver->findElement(WebDriverBy::cssSelector('.ticketPayoff'))->getText());
            $arVarPayoffs = $this->driver->findElements(WebDriverBy::cssSelector('.variant .payoff'));

            foreach ($arVarPayoffs as $payoff) {
                $this->arExistingVariantsPayoff[] = round(str_replace('\'', '', $payoff->getText()), 2);
            }

            $b_payoff = false;
            foreach ($this->arExistingVariantsPayoff as $payoff) {
//                echo "$payoff\n";
                if ($payoff == $won_row_payoff) {
                    $b_payoff = $payoff;
                    break;
                }
            }

            PHPUnit_Framework_Assert::assertNotFalse($b_payoff, 'wrong payoff sum');
            PHPUnit_Framework_Assert::assertEquals($b_payoff, $ticket_payoff, 'ticket quota is not equal to one of variants');

        // close popup
        $this->driver->findElement(WebDriverBy::cssSelector('#modal_content .modal_body_close .fa-close'))->click();
        usleep(200000);
    }

    /**
     * @When /^First four games win and other games lose$/
     */
    public function firstFourGamesWinAndOtherGamesLose()
    {
        $this->setOutcomeResults(4);
    }

    /**
     * @Given /^Highlight "([^"]*)" won row and ticket won amount is equal to sum of payoff won rows$/
     */
    public function highlightWonRowAndTicketWonAmountIsEqualToSumOfPayoffWonRows($won_rows)
    {
            $won_rows_payoff = $this->driver->findElements(WebDriverBy::cssSelector('.variant.won .payoff'));
            $row_sum_payoff = 0;

            foreach ($won_rows_payoff as $to_payoff) {
                $row_sum_payoff += (double) $to_payoff->getText();
            }

            $ticket_payoff = (double) str_replace('\'', '', $this->driver->findElement(WebDriverBy::cssSelector('.ticketPayoff'))->getText());

            $toCheck_won_diff = count($won_rows_payoff) - (int)$won_rows;
            PHPUnit_Framework_Assert::assertTrue( $toCheck_won_diff >= -1);
            PHPUnit_Framework_Assert::assertTrue( $toCheck_won_diff <= 1);

            PHPUnit_Framework_Assert::assertEquals((int)$won_rows, count($won_rows_payoff));

            $diff = $row_sum_payoff - $ticket_payoff;

            PHPUnit_Framework_Assert::assertTrue($diff < 1, 'ticket quota is not equal to one of variants. Different is: '.$diff);
            PHPUnit_Framework_Assert::assertTrue($diff > -1, 'ticket quota is not equal to one of variants. Different is: '.$diff);

        // close popup
        $this->driver->findElement(WebDriverBy::cssSelector('#modal_content .modal_body_close .fa-close'))->click();
        usleep(200000);
    }

    private function setOutcomeResults($win_amount)
    {
        $arGames = Support_Registry::singleton()->arGames[0]->getArGames();
        $cnt = 0;
        foreach ($arGames as $gid => $id) {
            if ($cnt < $win_amount) {
                Support_AdminHelper::markGame($gid, 'won');
                Support_Helper::processTicketQueue();
            } else {
                Support_AdminHelper::markGame($gid, 'lose');
                Support_Helper::processTicketQueue();
            }
            $cnt++;
        }

    }
}