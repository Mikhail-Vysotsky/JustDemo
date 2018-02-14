<?php
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 12.02.16
 * Time: 11:31
 */

class Support_Mobile_TicketHelper {

    /**
     * @param $control
     * @throws Exception
     */
    public static function selectBetOn($control)    //todo: can't place bet more then 5 games
    {
        $arGames = Support_Registry::singleton()->arGames;

        if ($control === "each" || $control === "each first") {
            $__i = 0;
            foreach ($arGames as $games) {
                if (!$games instanceof Support_MatchClass) throw new Exception ('arGames element is not instance of Support_MatchClass');
                Support_GoPage::game_mobile($__i);

                foreach ($games->getArGames() as $gid => $__id) {
                    if ($control === "each first")
                        Support_Mobile_TicketHelper::setBetToOutcome($gid, 'first');
                    if ($control === "each")
                        Support_Mobile_TicketHelper::setBetToOutcome($gid, 'rand');
                }

                $__i++;
            }
        } elseif ($control === "two") {
            Support_GoPage::game_mobile(0);

            $t_i = 0;
            foreach ($arGames[0]->getArGames() as $gid => $__id) {
                Support_Mobile_TicketHelper::setBetToOutcome($gid, '1');
                $t_i++;

                if ($t_i == 2) return true;
            }

        } elseif ($control === "outright") {
            $__y = 0;
            foreach ($arGames as $games) {
                if (!$games instanceof Support_MatchClass) throw new Exception ('arGames element is not instance of Support_MatchClass');
                Support_GoPage::game_mobile($__y);

                foreach ($games->getArGames() as $gid => $__id) {
                    Support_Mobile_TicketHelper::setBetToOutcome($gid, $control);
                }

                $__y++;
            }
        } else if ($control === "special") {
            $__z = 0;
            foreach ($arGames as $games) {
                if (!$games instanceof Support_MatchClass) throw new Exception ('arGames element is not instance of Support_MatchClass');
                Support_GoPage::game_mobile($__z);

                foreach ($games->getArGames() as $gid => $__id) {
                    Support_Mobile_TicketHelper::setBetToOutcome($gid, $control);
                }

                $__z++;
            }

        } else {
            throw new Exception('Control '.$control.' not found');
        }
    }

    /**
     * @param $gid
     * @param $outcome
     * @return bool
     * @throws Exception
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function setBetToOutcome($gid, $outcome)
    {
        $driver = Support_Registry::singleton()->driver;
        $s_registry = Support_Registry::singleton();
        $isOutright = false;
        if ($outcome !== "outright" && $outcome !== "special") {
            $arKeys = array();

            // just for choose random outcome
            if ($s_registry->elementPresent(WebDriverBy::cssSelector('.active_page .e-item[key="'.$gid.'"] div[key="1"]'), false))
                $arKeys[] = 'div[key="1"]';
            if ($s_registry->elementPresent(WebDriverBy::cssSelector('.active_page .e-item[key="'.$gid.'"] div[key="X"]'), false))
                $arKeys[] = 'div[key="X"]';
            if ($s_registry->elementPresent(WebDriverBy::cssSelector('.active_page .e-item[key="'.$gid.'"] div[key="2"]'), false))
                $arKeys[] = 'div[key="2"]';
            if ($s_registry->elementPresent(WebDriverBy::cssSelector('.active_page .e-item.e-item-outright'), false)) {
                $isOutright = true;
                $outcome = 'rand';
                $arOutcomes = $driver->findElements(WebDriverBy::cssSelector('.active_page .e-item .e-odd-outcome'));
                foreach ($arOutcomes as $outcome) {
                    $arKeys[] = $outcome->getAttribute('key');
                }
//                var_dump($arKeys);
//                exit;
            }

            if ($outcome === 'rand') {
                $useKey = '.active_page .e-item[key="'.$gid.'"] '. $arKeys[array_rand($arKeys)];
            } elseif ($outcome === "first" || $outcome === "1") {
                $useKey = '.active_page .e-item[key="'.$gid.'"] div[key="1"]';
            } elseif ($outcome === "2") {
                $useKey = '.active_page .e-item[key="' . $gid . '"] div[key="2"]';
            } else {
                if ($isOutright) {
                    //e-odd-outcome
                    $key = $arKeys[array_rand($arKeys)];
                    $useKey = '.active_page .e-item[key="'.$gid.'"] div[key="'.$key.'"]';
                } else {
                    $useKey = '.active_page .e-item[key="'.$gid.'"] div[key="'.$outcome.'"]';
                }
            }
            // return if can't place bet (for outrights)
            if (count($arKeys)==0) throw new Exception('Can\'t find odds. Probably is outright game or something broken:)');

//            $bets_Cnt = (int)$driver->findElement(WebDriverBy::cssSelector(".notify_amount.selected_bets_amount"))->getText();
            $bets_Cnt = (int)$driver->findElement(WebDriverBy::cssSelector(".head-btn-betslip .selected_bets_amount"))->getText();

            $driver->manage()->window()->maximize();
            usleep(120000);
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector($useKey)));
            usleep(250000);
//            $driver->findElement(WebDriverBy::cssSelector($useKey))->getLocation();
            $driver->findElement(WebDriverBy::cssSelector($useKey))->getLocationOnScreenOnceScrolledIntoView();
            sleep(1);
            $driver->findElement(WebDriverBy::cssSelector($useKey))->click();
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector($useKey.'.ui-btn-active')));

            for ($sec=0; ;$sec++) {
                if ($sec >= 60) PHPUnit_Framework_Assert::fail("timeout: bets are not updated");

                $new_Cnt = (int)$driver->findElement(WebDriverBy::cssSelector(".head-btn-betslip .selected_bets_amount"))->getText();
                if ($bets_Cnt < $new_Cnt) {
//                    $bets_Cnt = $new_Cnt;
                    break;
                }
                sleep(1);
            }
            return true;
        }

        if ($outcome === "outright") {
            // check that game present on current page
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .e-item[key="'.$gid.'"] .more_bets.go_matchdetails')));
            $driver->findElement(WebDriverBy::cssSelector('.active_page .e-item[key="'.$gid.'"] .more_bets.go_matchdetails'))->click();

            // wait for game details opened
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="'.$gid.'"]')));
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .e-item-outright[key*="'.$gid.'"] .e-odd-outcome')));
            usleep(150000);
            $arOdds = $driver->findElements(WebDriverBy::cssSelector('.active_page .e-item-outright[key*="'.$gid.'"] .e-odd-outcome'));

            // select random outcome
            $place_bet_to = $arOdds[array_rand($arOdds)];
            $driver->manage()->window()->maximize();

            // select bet
//            $bets_Cnt = (int)$driver->findElement(WebDriverBy::cssSelector(".notify_amount.selected_bets_amount"))->getText();
            $bets_Cnt = (int)$driver->findElement(WebDriverBy::cssSelector(".head-btn-betslip .selected_bets_amount"))->getText();
            $place_bet_to->click();

            // wait for bet selected
            for ($sec=0; ;$sec++) {
                if ($sec >= 60) PHPUnit_Framework_Assert::fail("timeout: bets are not updated");

                $new_Cnt = (int)$driver->findElement(WebDriverBy::cssSelector(".head-btn-betslip .selected_bets_amount"))->getText();
                if ($bets_Cnt < $new_Cnt) {
//                    $bets_Cnt = $new_Cnt;
                    break;
                }
                sleep(1);
            }
            return true;
        }

        if ($outcome === "special") {
            // check that game present on current page
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .e-item[key="'.$gid.'"] .more_bets.go_matchdetails')));
            $driver->findElement(WebDriverBy::cssSelector('.active_page .e-item[key="'.$gid.'"] .more_bets.go_matchdetails'))->click();

            // wait for game details opened
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="'.$gid.'"]')));
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .e-item[key*="'.$gid.'"] .e-odd .e-odd-outcome')));
            usleep(150000);
            $arOdds = $driver->findElements(WebDriverBy::cssSelector('.active_page .e-item[key*="'.$gid.'"] .e-odd .e-odd-outcome'));

            // select random outcome
            $place_bet_to = $arOdds[array_rand($arOdds)];
            $driver->manage()->window()->maximize();

            // select bet
            $bets_Cnt = (int)$driver->findElement(WebDriverBy::cssSelector(".head-btn-betslip .selected_bets_amount"))->getText();
            $place_bet_to->click();

            // wait for bet selected
            for ($sec=0; ;$sec++) {
                if ($sec >= 60) PHPUnit_Framework_Assert::fail("timeout: bets are not updated");

                $new_Cnt = (int)$driver->findElement(WebDriverBy::cssSelector(".head-btn-betslip .selected_bets_amount"))->getText();
                if ($bets_Cnt < $new_Cnt) {
//                    $bets_Cnt = $new_Cnt;
                    break;
                }
                sleep(1);
            }
            return true;

        }
    }

    /**
     * @param $stake
     * @throws Exception
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function setStake($stake)
    {
        $driver = Support_Registry::singleton()->driver;
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #stake')));
        usleep(150000);
        $driver->findElement(WebDriverBy::cssSelector('.active_page #stake'))->clear();
        usleep(150000);
        $driver->findElement(WebDriverBy::cssSelector('.active_page #stake'))->sendKeys($stake);
        $driver->executeScript('return $(\'.active_page #stake\').focusout()');
        sleep(1);
        Support_Registry::singleton()->stake = $stake;
    }
}