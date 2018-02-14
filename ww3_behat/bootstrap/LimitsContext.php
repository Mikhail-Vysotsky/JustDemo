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


class LimitsContext implements Context, SnippetAcceptingContext
{
    private $driver;
    private $account;

    function __construct()
    {
        $this->driver  = Support_Registry::singleton()->driver;
        $this->account = Support_Registry::singleton()->account;
    }

    /**
     * @When /^I place bet to "([^"]*)" games$/
     */
    public function iPlaceBetToGames($control)
    {
        if (Support_Helper::isMobile()) {
            Support_Mobile_TicketHelper::selectBetOn($control);
            return true;
        } elseif ($control === 'each') {
            foreach (Support_Registry::singleton()->arGames as $games) {
                Support_TicketHelper::placeBetToEachGames($games);
            }
        } elseif ($control === 'two' || $control === "first two") {
            $this->place_amount_BetToGame(Support_Registry::singleton()->arGames[0], 2);
        } elseif ($control === 'first') {
            $this->place_amount_BetToGame(Support_Registry::singleton()->arGames[0], 1);
        }
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.score.data-ticket-quota')));
        Support_Registry::singleton()->ticketQuota = $this->driver->findElement(WebDriverBy::cssSelector('.score.data-ticket-quota'))->getText();

        sleep(3);
    }

    /**
     * @param Support_MatchClass $games
     * @param $game_amount
     * @param string $outcome
     * @throws Exception
     * @throws Support_WWTimeoutException
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws null
     */
    private function place_amount_BetToGame(Support_MatchClass $games, $game_amount, $outcome = '1')
    {
        $arGames = $games->getArGames();
        if (!is_array($arGames)) throw new Exception('$arGames is not array');

        // open game page
        $gid = key($games->getArGames());
        $this->driver->get($games->getUrlRegular());
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('div[data-match="'.$gid.'"]')
        ));

        $games_selected = 0;
        for ($i = 0; $i < count($arGames); $i++) {
            if ($games_selected == $game_amount) break;


            $game_id = key($arGames);
//            $row_id = current($arGames);
            current($arGames);
            next($arGames);

            $this->driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::id('ticket_tips')
                )
            );
            $ticket_tips = $this->driver->findElement(WebDriverBy::id('ticket_tips'))->getText();

            // select bet if regular game
            if (Support_TicketHelper::isRegularGame($game_id)) {
                if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector(".regular .odds div.outcome.selected[data-match=\"$game_id\"]"), false)) {
                    continue;
                }
                $selector = ".regular .odds div.outcome[data-match=\"$game_id\"][data-outcome=\"$outcome\"]";
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector($selector)));
                $this->driver->findElement(WebDriverBy::cssSelector($selector))->click();

                Support_Wait::forTextUpdated('#ticket_tips', $ticket_tips);

            // select bet if outright game
            } elseif (Support_TicketHelper::isOutrightGame($game_id)) {
                Support_TicketHelper::selectOutrightGame($game_id);
            }
            $games_selected++;
        }
    }

    /**
     * @Given /^Ticket not created and balance "([^"]*)"$/
     */
    public function ticketNotCreatedAndBalance($expected_balance)
    {
        sleep(2);
        if (Support_Helper::isMobile()) {

            // close pop-up if exist
            if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.popup.popup-is-opened .popup_btn_wrap .popup_btn.btn'), false)) {
                $this->driver->findElement(WebDriverBy::cssSelector('.popup.popup-is-opened .popup_btn_wrap .popup_btn.btn'))->click();
                usleep(150000);
            }

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#wwfooter .balance .no-format-balance')));
            $current_balance = $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter .balance .no-format-balance'))->getText();

//            $current_balance = $this->driver->findElement(WebDriverBy::cssSelector('#user_info .user_info_content div.right'))->getText();

            // go to mobile ticket page
            $this->driver->get(Support_Configs::get()->MOBILE_BASE_URL.'#/index.php?ac=mobile/ticket/list');
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/ticket/list"]')));
            usleep(150000);
            PHPUnit_Framework_Assert::assertTrue(Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('.active_page .ticket_list .ticket-item')));
        } else {
            //
            $this->driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#last_user_tickets .ticket-list-items')
                )
            );

            $this->driver->navigate()->refresh();
            $this->driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#last_user_tickets .ticket-list-items')
                )
            );

            // close overlay container if it visible\
            Support_Close::closeOverlayContainer();

            Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('#last_user_tickets div.ticket_list_date'));
            $current_balance = $this->driver->findElement(WebDriverBy::cssSelector('.account-block span.balance'))->getText();
        }

        PHPUnit_Framework_Assert::assertEquals($expected_balance, $current_balance);
    }

    /**
     * @Then /^Ticket is created and balance "([^"]*)"$/
     */
    public function ticketIsCreatedAndBalance($expected_balance)
    {
        sleep(2);
        if (Support_Helper::isMobile()) {
            // wait for any pop-up and close it
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened')));
            $cur_url = $this->driver->getCurrentURL();
            if (strpos($cur_url, 'ac=mobile/ticket/list')) {
                $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .popup_close'))->click();
            } else {
                $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .popup_btn_wrap .popup_btn.btn'))->click();
            }
            usleep(150000);

//            $current_balance = $this->driver->findElement(WebDriverBy::cssSelector('#user_info .user_info_content div.right'))->getText();
            $current_balance = $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter .balance .no-format-balance'))->getText();

            // go to mobile ticket page
            $this->driver->get(Support_Configs::get()->MOBILE_BASE_URL.'#/index.php?ac=mobile/ticket/list');
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/ticket/list"]')));
            usleep(150000);
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .ticket_list .ticket-item')));
            PHPUnit_Framework_Assert::assertTrue(Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.active_page .ticket_list .ticket-item')));

        } else {
            //
            $this->driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#last_user_tickets .ticket-list-items')
                )
            );

            $this->driver->navigate()->refresh();
            $this->driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#last_user_tickets .ticket-list-items')
                )
            );

            Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#last_user_tickets .ticket-list-items'));

            $current_balance = $this->driver->findElement(WebDriverBy::cssSelector('.account-block span.balance'))->getText();
        }
        PHPUnit_Framework_Assert::assertEquals($expected_balance, $current_balance);
    }

    /**
     * @Given /^I place bet to one game with league level = "([^"]*)"$/
     */
    public function iPlaceBetToOneGameWithLeagueLevel($league_level)
    {
        // for mobile
        if (Support_Helper::isMobile()) {
            if ($league_level === '*') {
                // open game page
                $this->driver->get(Support_Registry::singleton()->arGames[0]->getUrlMobile());
                $gid = key(Support_Registry::singleton()->arGames[0]->getArGames());
                // wait for game
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('.active_page .e-item.odds_area[key="'.$gid.'"]')
                ));

                Support_Mobile_TicketHelper::setBetToOutcome($gid, 'rand');
            } elseif ($league_level === '1st') {
                // open game page
                $this->driver->get(Support_Registry::singleton()->arGames[1]->getUrlMobile());
                $gid = key(Support_Registry::singleton()->arGames[1]->getArGames());
                // wait for game
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('.active_page .e-item.odds_area[key="'.$gid.'"]')
                ));

                Support_Mobile_TicketHelper::setBetToOutcome($gid, 'rand');
            }

            // for regular
        } else {
            if ($league_level === '*') {
                $this->place_amount_BetToGame(Support_Registry::singleton()->arGames[0], 1);
            } elseif ($league_level === '1st') {
                $this->place_amount_BetToGame(Support_Registry::singleton()->arGames[1], 1);
            }
        }
    }

    /**
     * @Given /^if i select one more game$/
     */
    public function ifISelectOneMoreGame()
    {
        if (Support_Helper::isMobile()) {
            // open game page
            $this->driver->get(Support_Registry::singleton()->arGames[1]->getUrlMobile());
            $ar_games = Support_Registry::singleton()->arGames[1]->getArGames();
            next($ar_games);
            $gid = key($ar_games);
            // wait for game
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.active_page .e-item.odds_area[key="'.$gid.'"]')
            ));

            Support_Mobile_TicketHelper::setBetToOutcome($gid, 'rand');
        } else {
            $this->place_amount_BetToGame(Support_Registry::singleton()->arGames[1], 1);
            $this->place_amount_BetToGame(Support_Registry::singleton()->arGames[1], 2);
        }
    }

    /**
     * @Given /^League level "([^"]*)" have limit "([^"]*)" is "([^"]*)"$/
     */
    public function leagueLevelHaveLimitIs($league_level, $limit_field, $value)
    {
        $result_limit = Support_AdminHelper::setLeagueLimit($league_level, $limit_field, $value);

        if ($result_limit) {
            Support_Registry::singleton()->league_limit = true;
        }
    }

    /**
     * @Given /^if i select 3 games$/
     */
    public function ifISelectGames()
    {
        if (Support_Helper::isMobile()) {
            // open game page
            $this->driver->get(Support_Registry::singleton()->arGames[0]->getUrlMobile());
            $ar_games = Support_Registry::singleton()->arGames[0]->getArGames();
            end($ar_games);
            $gid = key($ar_games);
            // wait for game
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.active_page .e-item.odds_area[key="'.$gid.'"]')
            ));

            Support_Mobile_TicketHelper::setBetToOutcome($gid, 'rand');
        } else {
            $this->place_amount_BetToGame(Support_Registry::singleton()->arGames[0], 1);
        }
    }

    /**
     * @Given /^If i place bet for ticket quota more 2$/
     */
    public function ifIPlaceBetForTicketQuotaMore()
    {
        if (Support_Helper::isMobile()) {
            // open game page
            $this->driver->get(Support_Registry::singleton()->arGames[1]->getUrlMobile());
            $ar_games = Support_Registry::singleton()->arGames[1]->getArGames();
            end($ar_games);
            $gid = key($ar_games);
            // wait for game
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.active_page .e-item.odds_area[key="'.$gid.'"]')
            ));

            Support_Mobile_TicketHelper::setBetToOutcome($gid, '1');
        } else {
            $this->place_amount_BetToGame(Support_Registry::singleton()->arGames[1], 1, '1');
        }
    }

    /**
     * @Given /^If i set stake "([^"]*)"$/
     */
    public function ifISetStake($stake)
    {
        if (Support_Helper::isMobile()) {
            Support_Mobile_TicketHelper::setStake($stake);
        } else {
           Support_TicketHelper::setStake($stake);
        }
    }

    /**
     * @When /^I have one more account$/
     */
    public function iHaveOneMoreAccount()
    {
        Support_Helper::iGetOneMoreAccount(false, '100', 'EUR');
    }
}