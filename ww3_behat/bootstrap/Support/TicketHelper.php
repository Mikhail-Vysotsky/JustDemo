<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 23.11.15
 * Time: 18:12
 */
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class Support_TicketHelper {
    /**
     * @param $control
     * @param null $type
     * @param string $outcome
     * @throws Support_WWTimeoutException
     * @internal param Support_Registry $
     */
    public static function placeBetToGamesOnPage($control, $type = null, $outcome = "rand")
    {
        $session = Support_Registry::singleton();
        $driver = $session->driver;
        $regular_count = 0;
        $outright_count = 0;
        $arRegular = array();

            for ($sec = 0; $sec < 90; $sec++) {

                if (count($driver->findElements(WebDriverBy::cssSelector('.match-item .regular .odds'))) > 0)
                    break;

                if (count($driver->findElements(WebDriverBy::cssSelector('.match-item .outright .outright-odds'))) > 0)
                    break;

                sleep(1);
            }

        // select all regular games on current page
        if ($control === "each" || $control === "each first") {
            if (strpos($control, 'each first') !== false) $setFirst = true;
            else $setFirst = false;

            if ($type === null || $type === 'mixed') {
                // place bets to regular games
                if (count($driver->findElements(WebDriverBy::cssSelector('.match-item .regular .odds'))) > 0) {
                    $arRegular = $driver->findElements(WebDriverBy::cssSelector('.match-item .regular .odds'));
                    $regular_count = count($arRegular);
                }
                if (count($driver->findElements(WebDriverBy::cssSelector('.match-item .outright .outright-odds'))) > 0) {
                    $arOutright = $driver->findElements(WebDriverBy::cssSelector('.match-item .outright .outright-odds'));
                    $outright_count = count($arOutright);
                }
            } else {
                if ($type === 'regular') {
                    $arRegular = $driver->findElements(WebDriverBy::cssSelector('.match-item .regular .odds'));
                    $regular_count = count($arRegular);
                } elseif ($type === 'outright') {
                    $arOutright = $driver->findElements(WebDriverBy::cssSelector('.match-item .outright .outright-odds'));
                    $outright_count = count($arOutright);
                }
            }

            if ( $regular_count > 0 ) {
                // select odds

                foreach ($arRegular as $reg_item) {
                    usleep(120000);
                    $bets_info = $driver->findElement(WebDriverBy::cssSelector('.ticket-bonus-info .games.data-ticket-full_bets_amount_label'))->getText();

                    if ($outcome === 'last') {
                        $element = $reg_item->findElement(WebDriverBy::cssSelector('div.outcome[data-outcome="2"]'));
                    } else if (!$setFirst) {
                        $arOdds = $reg_item->findElements(WebDriverBy::cssSelector('div.outcome[data-outcome]'));
                        $element = $arOdds[array_rand($arOdds)];
                    } else {
                        $element = $reg_item->findElement(WebDriverBy::cssSelector('div.outcome[data-outcome="1"]'));
                    }

                    // select bet
                    $element->click();
                    Support_Wait::forTextUpdated('.ticket-bonus-info .games.data-ticket-full_bets_amount_label', $bets_info);
                }
            }

            if ( $outright_count > 0 ) {

                // get all outright match
                $outright_matchs = $driver->findElements(WebDriverBy::cssSelector('.outright'));

                foreach ($outright_matchs as $outright) {
                    $outright->findElement(WebDriverBy::cssSelector('.other-outright-odds-toggler a[onclick*="toggler_show"]'))->click();
                    usleep(120000);

                    $arOutrightOdds = $outright->findElements(WebDriverBy::cssSelector('.outright-odds .outright-outcome'));
                    $oddTo = $arOutrightOdds[array_rand($arOutrightOdds)];

                    // store bet info
                    $bets_info = $driver->findElement(WebDriverBy::cssSelector('.ticket-bonus-info .games.data-ticket-full_bets_amount_label'))->getText();

                    // select bet
                    $oddTo->click();
                    Support_Wait::forTextUpdated('.ticket-bonus-info .games.data-ticket-full_bets_amount_label', $bets_info);
                }
            }
        }
    }

    public static function selectBetToGameById($gameId)
    {
        $driver = Support_Registry::singleton()->driver;
        $outcome_css_selector = '.match-item .regular .odds .outcome[data-match="'.$gameId.'"][data-outcome="1"]';

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector($outcome_css_selector)));
        usleep(250000);

        $bets_info = $driver->findElement(WebDriverBy::cssSelector('.ticket-bonus-info .games.data-ticket-full_bets_amount_label'))->getText();
        $driver->findElement(WebDriverBy::cssSelector($outcome_css_selector))->click();
        Support_Wait::forTextUpdated('.ticket-bonus-info .games.data-ticket-full_bets_amount_label', $bets_info);
        usleep(250000);
    }

    /**
     * @param $control
     * @throws Exception
     * @throws NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function selectBetToGame($control)
    {
        $driver = Support_Registry::singleton()->driver;
        $type = null;
        $to_last = false;
        $outcome = 'rand';

        if ($control === "each last") {
            $control = "each";
            $to_last = true;

        }
        if ($control === "each") {
            foreach (Support_Registry::singleton()->arGames as $games) {
                if (!($games instanceof Support_MatchClass)) {
                    continue;
                }

                // open games page if user on other any page
                $curr_url = str_replace('https', 'http', $driver->getCurrentURL());
                if ($curr_url !== $games->getUrlRegular()) {
                    $driver->get($games->getUrlRegular());
                }
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div.quota')));
                sleep(1);
                $type = $driver->wait()->until(function() {
                    for ($sec = 0; $sec < 90; $sec++) {
                        try {
                            $reg = false;
                            $out = false;

                            if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.match-item .regular .odds'), false))
                                $reg = 'regular';

                            if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.match-item .outright .outright-odds'), false))
                                $out = 'outright';

                            if ($reg && $out) {
                                return 'mixed';
                            } elseif ($reg) {
                                return $reg;
                            } elseif ($out) {
                                return $out;
                            }
                        } catch (Exception $e) {}
                        sleep(1);
                    }
                    return false;
                }
                );
                if ($to_last) {
                    $outcome = "last";
                }
                Support_TicketHelper::placeBetToGamesOnPage( 'each', $type, $outcome);
            }
        } elseif ($control === "first outcome in each") {
            foreach (Support_Registry::singleton()->arGames as $games) {
                if (!($games instanceof Support_MatchClass)) {
                    continue;
                }

                // open games page if user on other any page
                $curr_url = str_replace('https', 'http', $driver->getCurrentURL());
                if ($curr_url !== $games->getUrlRegular()) {
                    $driver->get($games->getUrlRegular());
                }

                Support_TicketHelper::placeBetToGamesOnPage( 'each first');
            }
        }
    }

    /**
     * @param $stake
     * @throws Exception
     * @throws NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function setStake($stake)
    {
        $driver = Support_Registry::singleton()->driver;
        if (Support_Helper::isMobile()) {
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #stake')));
            $driver->findElement(WebDriverBy::cssSelector('.active_page #stake'))->clear();
            $driver->findElement(WebDriverBy::cssSelector('.active_page #stake'))->sendKeys($stake);
            Support_Registry::singleton()->stake = $stake;

        } else {
            $driver->findElement(WebDriverBy::id('ticket_stake'))->clear();
            $driver->findElement(WebDriverBy::id('ticket_stake'))->sendKeys($stake);
            Support_Registry::singleton()->stake = $stake;
        }
    }

    /**
     * @param $ticket_type
     * @param bool $waitQuota
     * @throws Exception
     * @throws NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function selectTicketType($ticket_type, $waitQuota = true)
    {
        $driver = Support_Registry::singleton()->driver;

        //        $isSelected = false;
        $quota = $driver->findElement(WebDriverBy::cssSelector('#ticket_block .ticket-bonus-info div.data-ticket-quota'))->getText();
        try {
            $driver->findElement(WebDriverBy::cssSelector("#ticket_tabs div.tab.selected[index=$ticket_type]"));
            $isSelected = true;
        } catch (NoSuchElementException $e) {
            $isSelected = false;
        }


        if (!$isSelected) {
            $driver->findElement(WebDriverBy::cssSelector("#ticket_tabs div.tab[index=$ticket_type]"))->click();

            $driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector("#ticket_tabs div.tab.selected[index=$ticket_type]")
                )
            );

            if ($waitQuota) {
                $driver->wait()->until(
                    WebDriverExpectedCondition::not(
                        WebDriverExpectedCondition::textToBePresentInElement(
                            WebDriverBy::cssSelector("#ticket_block .ticket-bonus-info div.data-ticket-quota"),
                            $quota
                        )
                    )
                );
            }
            sleep(1);
        }
    }

    /**
     * @throws Exception
     * @throws NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function waitForTicketDetailWindowLoad()  //todo move to wait class
    {
        $driver = Support_Registry::singleton()->driver;
        $driver->wait()->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.ticketHead .ticketType')
            )
        );

        $driver->wait()->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('#modal_content .modal_body_close .fa-close')
            )
        );
    }

    /**
     * @return string
     * @throws Exception
     * @throws NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function doClickToCreateTicket()
    {
        $driver = Support_Registry::singleton()->driver;

        $driver->findElement(WebDriverBy::id('ticket_insert_button'))->click();

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('.popup-success-msg.alert-msg')
        ));

        $msg_text = $driver->findElement(WebDriverBy::cssSelector('.popup-success-msg.alert-msg div'))->getText();
        $driver->findElement(WebDriverBy::id('custom_alert_btn'))->click();
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('#last_user_tickets div.ticket-list-item')
        ));

        return $msg_text;
    }

    /**
     * @param Support_MatchClass $__testData
     * @param bool $double
     * @param null $skip_game_id
     * @throws Exception
     * @throws NoSuchElementException
     * @throws Support_WWTimeoutException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function placeBetToEachGames(Support_MatchClass $__testData, $double = false, $skip_game_id = null)
    {
        $driver = Support_Registry::singleton()->driver;

        $arGames = $__testData->getArGames();
        $driver->get($__testData->getUrlRegular());

        foreach ($arGames as $game_id => $row_id) {

            if (!is_null($skip_game_id)) {
                if ($game_id === $skip_game_id) continue;
            }

            if (!$double) {
                // select bet if regular game
                if (Support_TicketHelper::isRegularGame($game_id)) {
                    $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.regular .odds div.outcome[data-match="'.$game_id.'"][data-outcome="1"]')));
                    $driver->findElement(WebDriverBy::cssSelector('.regular .odds div.outcome[data-match="'.$game_id.'"][data-outcome="1"]'))->click();

                    // select bet if outright game
                } elseif (Support_TicketHelper::isOutrightGame($game_id)) {
                    Support_TicketHelper::selectOutrightGame($game_id);
                }

            } elseif ($double) {
                $double_selected_count = count($driver->findElements(WebDriverBy::cssSelector('#ticket_bets_area div.tb[index="' . $game_id . '"] div.selected')));
                if ($double_selected_count === 2) continue;

                // available odds to place bet
                if ($double === 'last') {
                    $double_bet_to = $driver->findElement(WebDriverBy::cssSelector('#ticket_bets_area div.tb[index="' . $game_id . '"] div.clickable[index="2"]'));
                } elseif ($double === 'first') {
                    $double_bet_to = $driver->findElement(WebDriverBy::cssSelector('#ticket_bets_area div.tb[index="' . $game_id . '"] div.clickable[index="1"]'));
                } elseif ($double === 'draw of first') {
                    $double_bet_to = $driver->findElement(WebDriverBy::cssSelector('#ticket_bets_area div.tb[index="' . $game_id . '"] div.clickable[index="X"]'));
                    $double = 'first';
                } else {
                    $arTempOdds = $driver->findElements(WebDriverBy::cssSelector('#ticket_bets_area div.tb[index="' . $game_id . '"] div.clickable[index]'));
                    $arOdds = array();
                    foreach ($arTempOdds as $item) {
                        $class = $item->getAttribute('class');
                        if (strpos($class, 'selected') === false) {
                            $arOdds[] = $item;
                        }
                    }
                    $double_bet_to = $arOdds[array_rand($arOdds)];
                }


                $ticket_rows = $driver->findElement(WebDriverBy::id('ticket_rows'))->getText();
//                sleep(10);
//                if ($double === 'draw of first') $double = 'first';
                $double_bet_to->click();
                Support_Wait::forTextUpdated('#ticket_rows', $ticket_rows);
            }


            // close overlay container if it visible\
            Support_Close::closeOverlayContainer();
        }
    }

    /**
     * @param $game_id
     * @return bool
     * @throws Exception
     */
    public static function isRegularGame($game_id)  //todo move to check class
    {
        try {
            Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.regular .odds div.outcome[data-match="'.$game_id.'"]'), false);
            return true;
        } catch (NoSuchElementException $e) {
            return false;
        }
    }

    /**
     * @param $game_id
     * @return bool
     * @throws Exception
     */
    public static function isOutrightGame($game_id) //move to check class
    {
        try {
            if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.outright-outcome[data-match="'.$game_id.'"]')))
                return true;
            else
                return false;
        } catch (NoSuchElementException $e) {
            return false;
        }
    }

    /**
     * @param $game_id
     * @throws Exception
     * @throws NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function selectOutrightGame($game_id)
    {
        $driver = Support_Registry::singleton()->driver;

        $games_in_ticket = $driver->findElement(WebDriverBy::cssSelector('div.games'))->getText();

        $driver->findElement(WebDriverBy::cssSelector("tr#event$game_id div.select-bet-btn"))->click();

        $driver->wait()->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.betDetail[data-event="' . $game_id . '"] .odds_group div.outcome[onclick*="toggle_select_bet"]')
            )
        );
        $driver->findElement(WebDriverBy::cssSelector('.betDetail[data-event="' . $game_id . '"] .odds_group div.outcome[onclick*="toggle_select_bet"]'))->click();

        // wait until ticket games count increment
        $driver->wait()->until(
            WebDriverExpectedCondition::not(
                WebDriverExpectedCondition::textToBePresentInElement(
                    WebDriverBy::cssSelector('div.games'),
                    $games_in_ticket
                )
            )
        );
    }

    /**
     * @param $ticket_type
     * @throws Exception
     */
    public static function available($ticket_type)
    {
        $driver = Support_Registry::singleton()->driver;
        sleep(2); //todo should wait until tab with ticket type updated but tab can stay same and this is not error
        $driver->findElement(WebDriverBy::cssSelector("#current_ticket div.tab[index=$ticket_type]"));
        for ($sec = 0; $sec <= 60; $sec++) {
            if ($sec===60) {
                throw new Exception('timeout: ticket with type '.$ticket_type.' not available');
            }

            if (Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector("#current_ticket div.not_active[index=$ticket_type]"), false)) break;
            sleep(1);
        }
    }

    /**
     * @return null
     * @throws Exception
     * @throws NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function openTicket() // todo move to gopage class?
    {
        $driver = Support_Registry::singleton()->driver;
        // store window handler and count
        Support_Registry::singleton()->current_window = $driver->getWindowHandle();

        $wd_printTicket = WebDriverBy::cssSelector('.ticket-list-items div[onclick*="open_ticket_details"]');
        sleep(1);

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
            $wd_printTicket
        ));
        sleep(1);
        $driver->findElement($wd_printTicket)->click();
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content .popup-ticket-details')));

        Support_TicketHelper::waitForTicketDetailWindowLoad();


        return Support_Registry::singleton()->current_window;
    }

    /**
     * @param $expected_ticket_type
     * @throws Exception
     * @throws NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function ticketTypeIs($expected_ticket_type) //todo move to check class?
    {
        $driver = Support_Registry::singleton()->driver;

        $wd_ticket = WebDriverBy::cssSelector('#last_user_tickets div.ticket-list-item[onclick*="open_ticket_details"]');
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated($wd_ticket));
        $driver->findElement($wd_ticket)->click();


        // wait for pop-up open
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content .popup-ticket-details')));

            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.ticketDetail .ticketType.item')));

            $ticket_type = strtolower($driver->findElement(WebDriverBy::cssSelector('.ticketDetail .ticketType.item'))->getText());
            $ticket_bet_amount = count($driver->findElements(WebDriverBy::cssSelector('.ticketBets .betItem')));

            // check type
            if ($expected_ticket_type !== "check_mixed" && $expected_ticket_type !== "top 7") {
                PHPUnit_Framework_Assert::assertContains($expected_ticket_type, $ticket_type);
            } else if ($expected_ticket_type === "check_mixed"){
                PHPUnit_Framework_Assert::assertContains("single", $ticket_type);
            } else if ($expected_ticket_type === "top 7"){
                PHPUnit_Framework_Assert::assertContains("top 7", $ticket_type);
            }

            // check other ticket details by ticket type
            if ($expected_ticket_type === "single") {
                PHPUnit_Framework_Assert::assertEquals(3, $ticket_bet_amount);
            } elseif ($expected_ticket_type === "multi") {
                PHPUnit_Framework_Assert::assertEquals(3, $ticket_bet_amount);
            } elseif ($expected_ticket_type === "double") {
                $betTipItems = count($driver->findElements(WebDriverBy::cssSelector('.betTipItem')));
                $variants = count($driver->findElements(WebDriverBy::cssSelector('.ticketVariants-ol .orderNumber')));
                PHPUnit_Framework_Assert::assertEquals(4, $ticket_bet_amount);
                PHPUnit_Framework_Assert::assertEquals(8, $betTipItems);
                PHPUnit_Framework_Assert::assertEquals(16, $variants);
            } elseif ($expected_ticket_type === "system") {
                $ticket_banks = count($driver->findElements(WebDriverBy::cssSelector('.ticketBets .betBanker')));
                PHPUnit_Framework_Assert::assertEquals(4, $ticket_bet_amount);
                PHPUnit_Framework_Assert::assertEquals(2, $ticket_banks);
            } elseif ($expected_ticket_type === "check_mixed") {
                PHPUnit_Framework_Assert::assertEquals(5, $ticket_bet_amount);
            } elseif ($expected_ticket_type === "top 7") {
                PHPUnit_Framework_Assert::assertEquals(7, $ticket_bet_amount);
            }


        // close popup
        $driver->findElement(WebDriverBy::cssSelector('#modal_content .modal_body_close .fa-close'))->click();
        usleep(200000);
    }

    /**
     * @param $amount
     * @throws Support_WWTimeoutException
     */
    public static function selectGames($amount)
    {
        $driver = Support_Registry::singleton()->driver;

        $arGames = $driver->findElements(WebDriverBy::cssSelector('.top7-events-area tr.odd[index]'));
        $selected_count = 0;
        $count_value = '';
        foreach ($arGames as $game) {
            if ($selected_count >= $amount) break;

            // get available odds
            $arOdds = $game->findElements(WebDriverBy::cssSelector('div.select-bet-btn.outcome'));
//            $odd = $arOdds[array_rand($arOdds)];
            $odd = $arOdds[0];
            $odd->click();

            $count_value = Support_Wait::forTextUpdated( '.top7-events-footer span.count', $count_value);
            $selected_count++;
            sleep(1);
        }
    }

    /**
     * @param $ticket_type
     * @param $ticket_status
     */
    public static function checkInPublicInterfaceThatTicketIs($ticket_type, $ticket_status) //todo move to check class?
    {
        Support_Helper::openMainPage();
        $driver = Support_Registry::singleton()->driver;

        Support_TicketHelper::openTicket();

        // check ticket type
        $ticket_head = $driver->findElement(WebDriverBy::cssSelector('.ticketHead .ticketType'))->getText();
        $ticket_head = strtolower($ticket_head);

        PHPUnit_Framework_Assert::assertContains($ticket_type, $ticket_head);

        //check ticket status
        $curr_status = $driver->findElement(WebDriverBy::cssSelector('.ticketStatus'))->getText();
        $curr_status = strtolower($curr_status);

        if ($curr_status === 'waiting approving') $curr_status = 'won';
        if ($ticket_status === 'WAITING APPROVING') $ticket_status = 'won';

        PHPUnit_Framework_Assert::assertEquals(strtolower($ticket_status), $curr_status);

        // close popup
        $driver->findElement(WebDriverBy::cssSelector('#modal_content .modal_body_close .fa-close'))->click();
        usleep(200000);
    }

    public static function setUseRealMoney()
    {
        $driver = Support_Registry::singleton()->driver;

        // click to arrow
        $driver->findElement(WebDriverBy::id('select-money-source-boxSelectBoxItArrowContainer'));
        $arElItems = $driver->findElements(WebDriverBy::cssSelector('#select-money-source-box option[data-text]'));

        foreach ($arElItems as $element) {
            $textLabel = $element->getAttribute('data-text');
            $isSelected = $element->getAttribute('selected');

            if (strpos($textLabel, 'Money') !== false && !$isSelected) {
                $element->click();
                usleep(250000);
            }
        }
        sleep(1); // for any cases
    }

    public static function setUseBonusCard()
    {
        $driver = Support_Registry::singleton()->driver;
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#select-money-source-box option[data-text]')));
        $driver->findElement(WebDriverBy::id('select-money-source-boxSelectBoxIt'))->getLocationOnScreenOnceScrolledIntoView();
        sleep(1);
        // click to arrow
        $driver->findElement(WebDriverBy::id('select-money-source-boxSelectBoxItArrowContainer'))->click();
        $arElItems = $driver->findElements(WebDriverBy::cssSelector('#select-money-source-box option[data-text]'));

        foreach ($arElItems as $element) {
            $textLabel = $element->getAttribute('data-text');
            $isSelected = $element->getAttribute('selected');

            if (strpos($textLabel, 'Card') !== false && !$isSelected) {
                //$this->driver->executeScript('return $(\'#refresh_intervalSelectBoxItOptions li[data-val="'.$refresh_option.'"] .selectboxit-option-anchor\').trigger(\'mousedown\')');
                $driver->executeScript('return $(\'#select-money-source-boxSelectBoxItOptions li[data-text*=" Card "]\').trigger(\'mousedown\') ');

//                usleep(250000);
//                $element->click();
                usleep(250000);
            }
        }
        sleep(1); // for any cases
    }

    public static function getTicketNumber()
    {
        $driver = Support_Registry::singleton()->driver;
        // #last_user_tickets .ticket-list-item(onclick="open_ticket_details('1856')")
        // #modal_content #ticket-details-wrapper  .ticketCoupon span.value (824f13a6)


        sleep(99999);
    }
}