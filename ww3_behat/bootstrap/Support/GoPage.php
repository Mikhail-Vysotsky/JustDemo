<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 09.02.16
 * Time: 18:15
 */
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver;

class Support_GoPage {
    /**
     * @var Support_MatchClass
     */
    private static $mc;

    /**
     * @param Support_MatchClass $mc
     * @return null|string
     * @throws Exception
     * @throws WebDriver\Exception\NoSuchElementException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function sport(Support_MatchClass $mc)
    {
        $driver = Support_Registry::singleton()->driver;
        self::$mc = $mc;

        // reset search filter
        $driver->wait(120000)->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('reset')));
        $driver->wait(120000)->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div.t-page-gen-timestamp-LB_PublicCategory_Pager')));

        // store page timestamp
        $page_timestamp = $driver->findElement(WebDriverBy::cssSelector('div.t-page-gen-timestamp-LB_PublicCategory_Pager'))->getAttribute('data-page-get');

        $driver->findElement(WebDriverBy::id('reset'))->click();
        for ($sec = 0; $sec <= 60; $sec++) {
            $new_page_timestamp = $driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_PublicCategory_Pager'))->getAttribute('data-page-get');
            if ($new_page_timestamp !== $page_timestamp) {
                $page_timestamp = $new_page_timestamp;
                break;
            }
            sleep(1);
        }

        if (Support_Registry::singleton()->singleton()->elementPresent(WebDriverBy::className('.detail-search-cell-content #f_active-1[checked=checked]'), false)) {
            $driver->findElement(WebDriverBy::id('f_active-1'))->click();

            for ($sec = 0; $sec <= 60; $sec++) {
                $new_page_timestamp = $driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_PublicCategory_Pager'))->getAttribute('data-page-get');
                if ($new_page_timestamp !== $page_timestamp) {
                    $page_timestamp = $new_page_timestamp;
                    break;
                }
                sleep(1);
            }
        }

        // find category
        $driver->findElement(WebDriverBy::id('simple_search_keyword'))->sendKeys($mc->getCategorySportId());
        $driver->findElement(WebDriverBy::cssSelector('.detail-search-buttons button[type=submit]'))->click();

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::id('r' . $mc->getCategorySportId())
        ));

        // open sport
        sleep(2);
        $driver->findElement(WebDriverBy::cssSelector('#r' . $mc->getCategorySportId() . ' .t-name a[href]'))->click();

        $driver->wait()->until(function(){
            for ($sec = 0; $sec < 60; $sec++) {
                if (Support_Registry::singleton()->elementNotPresent(WebDriverBy::id('r'.self::$mc->getCategorySportId()), false))
                    return true;
            }
        });
        for ($sec = 0; $sec <= 60; $sec++) {
            $driver->wait(120000)->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.t-page-gen-timestamp')));
            $new_page_timestamp = $driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp'))->getAttribute('data-page-get');
            if ($new_page_timestamp !== $page_timestamp) {
                $page_timestamp = $new_page_timestamp;
                break;
            }
            sleep(1);
        }

        sleep(3);

        $driver->findElement(WebDriverBy::id('reset'))->click();
        sleep(1);
        $driver->findElement(WebDriverBy::id('f_active-0'))->click();
        $driver->findElement(WebDriverBy::id('simple_search_keyword'))->clear();
        usleep(120000);
        $driver->findElement(WebDriverBy::cssSelector('.detail-search-buttons button[type=submit]'));
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('r' . $mc->getCategoryRegionId())));

        return $page_timestamp;
    }

    /**
     * @param Support_MatchClass $mc
     * @return bool|null|string
     * @throws Exception
     * @throws WebDriver\Exception\NoSuchElementException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function country(Support_MatchClass $mc)
    {
        $driver = Support_Registry::singleton()->driver;
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.t-page-gen-timestamp')));
        $page_timestamp = $driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp'))->getAttribute('data-page-get');

//        sleep(2);
        // open country
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#r' . $mc->getCategoryRegionId() . ' .t-name a[href]')));
        sleep(2);
        $driver->findElement(WebDriverBy::cssSelector('#r' . $mc->getCategoryRegionId() . ' .t-name a[href]'))->click();

        for ($sec = 0; $sec <= 60; $sec++) {
            $driver->wait(120000)->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.t-page-gen-timestamp')));
            $new_page_timestamp = $driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp'))->getAttribute('data-page-get');
            if ($new_page_timestamp !== $page_timestamp) {
                $page_timestamp = $new_page_timestamp;
                return $page_timestamp;
//                break;
            }
            sleep(1);
        }
        return false;
    }

    /**
     * @param Support_MatchClass $mc
     * @param $game_id
     * @throws Exception
     * @throws WebDriver\Exception\NoSuchElementException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function tournament(Support_MatchClass $mc, $game_id)
    {
        $driver = Support_Registry::singleton()->driver;
        // open tournament
        $driver->findElement(WebDriverBy::cssSelector('#r' . $mc->getPublicCategoryId() . ' .t-name a[href]'))->click();
        $driver->wait(120000)->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('r' . $game_id)));
    }

    /**
     * @throws Exception
     * @throws WebDriver\Exception\NoSuchElementException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function game() {
        $driver = Support_Registry::singleton()->driver;

        $gid = key(Support_Registry::singleton()->arGames[0]->getArGames());
        $driver->get(Support_Registry::singleton()->arGames[0]->getUrlRegular());
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('div[data-match="'.$gid.'"]')
        ));
    }

    /**
     * @param $control
     * @throws Exception
     * @throws WebDriver\Exception\NoSuchElementException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function openTab($control)
    {
        $driver = Support_Registry::singleton()->driver;
        if ($control === 'bets') {
            $driver->findElement(WebDriverBy::cssSelector('#head-menu a[href*="ac=v3/sports/index"]'))->click();
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#head-menu .active a[href*="ac=v3/sports/index')));
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('filtered-matches')));
        } elseif ($control === 'live') {
            $driver->findElement(WebDriverBy::cssSelector('#head-menu a[href*="ac=user/lb/index"]'))->click();
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.lb_area .matchdetails')));
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('match_paginator')));
        }
    }

    /**
     * @throws Exception
     * @throws Support_ConfigsException
     * @throws WebDriver\Exception\NoSuchElementException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function openMainPageOfMobileSite()
    {
        $url = Support_Configs::get()->MOBILE_BASE_URL;
//        $expected_url = 'ac=mobile/index/index';
        $expected_url = $url;
        $driver = Support_Registry::singleton()->driver;
        $driver->get($url);
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="'.$expected_url.'"]')));
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="'.$expected_url.'"] .t-about')));
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="'.$expected_url.'"] .language')));
//        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="'.$expected_url.'"] .t-account')));

    }

    /**
     * @throws Exception
     * @throws WebDriver\Exception\NoSuchElementException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function paymentMethodsPage_mobile()
    {
        $driver = Support_Registry::singleton()->driver;

        $driver->findElement(WebDriverBy::cssSelector('#wwfooter .username a.footer-link.logged-in'))->click();
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/player/index"]')));
        usleep(120000);

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .listview .linked[onclick*="urls.account_deposit"]')));
        $driver->findElement(WebDriverBy::cssSelector('.active_page .listview .linked[onclick*="urls.account_deposit"]'))->click();

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .t-visa')));
    }

    /**
     * @throws Exception
     * @throws WebDriver\Exception\NoSuchElementException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function openTop7List()
    {
        $driver = Support_Registry::singleton()->driver;

        $driver->findElement(WebDriverBy::cssSelector('#head-menu a[href*="ac=v3/top7/index"]'))->click();
        $driver->wait()->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('#head-menu .active a[href*="ac=v3/top7/index"]')
            )
        );
    }

    /**
     * @param int $c
     * @throws Exception
     * @throws WebDriver\Exception\NoSuchElementException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function game_mobile($c = 0)
    {
        $driver = Support_Registry::singleton()->driver;

        $driver->get(Support_Registry::singleton()->arGames[$c]->getUrlMobile());
        $gid = key(Support_Registry::singleton()->arGames[$c]->getArGames());

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('.active_page .e-item.odds_area[key="'.$gid.'"]')
        ));

        usleep(120000);
    }

    /**
     * @param $page_timestamp
     * @return null|string
     */
    public static function waitForLivescorePageRefresh($page_timestamp)
    {
        $driver = Support_Registry::singleton()->driver;

        // wait for page refresh
        for ($i=0; $i<60; $i++) {
            $new_page_timestamp = $driver->findElement(WebDriverBy::cssSelector('.t-item-gentime'))->getAttribute('data-gen-time');
            if ($new_page_timestamp !== $page_timestamp) {
                Support_Registry::singleton()->page_timestamp = $new_page_timestamp;
                return $new_page_timestamp;
            }
            sleep(1);
        }
    }

    /**
     * @param $target_page
     * @throws Exception
     * @throws Support_ConfigsException
     */
    public static function openAdminPage($target_page)
    {
        $url = Support_Configs::get()->MANAGE_URL;
        $driver = Support_Registry::singleton()->driver;

        if ($target_page === 'countries') {
            $url .= 'index.php?ac=admin/country_code/list';
        } elseif ($target_page === 'player vouchers') {
            $url .= "index.php?ac=admin/players/vouchers_generation/list";
        } elseif ($target_page === 'payout claims') {
            $url .= "index.php?ac=admin/players/payout_claims/list";
        } elseif ($target_page === 'main events') {
            $url .= "index.php?ac=admin/mainevent/index";
        } else {
            throw new Exception('no target page found in tests');
        }

        $driver->get($url);

        $driver->wait()->until(function() {
            if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#inputEmail'), false)) {
                return true;
            }
            if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.t-page-gen-timestamp'), false)) {
                return true;
            }
        });
        
        if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#inputEmail'), false)) {
            Support_AdminHelper::loginAsAdmin();
            $driver->get($url);
        }
        Support_Registry::singleton()->page_timestamp = Support_Wait::forAdminPageLoaded('.t-page-gen-timestamp', 'data-page-get', Support_Registry::singleton()->page_timestamp);
    }

    /**
     * @throws Exception
     * @throws Support_ConfigsException
     * @throws WebDriver\Exception\NoSuchElementException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function pageAccountSettings()
    {
        $driver = Support_Registry::singleton()->driver;

        $driver->get(Support_Configs::get()->BASE_URL.'index.php?ac=user/player/index');
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.account-menu-item.active')));

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active a[href*="user/player/index"]')));
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.main-info-block a[href*="close_account"]')));
        usleep(150000);
    }

    /**
     * @throws Exception
     * @throws Support_ConfigsException
     * @throws WebDriver\Exception\NoSuchElementException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function openAccountSettingsPage()
    {
        $driver = Support_Registry::singleton()->driver;

        $driver->get(Support_Configs::get()->BASE_URL.'/index.php?ac=user/player/index');
        $driver->wait()->until(function(){
            if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.account-buttons a[href*="player_add_payment_method"]'), false)) {
                return true;
            }

            if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.account-buttons a[href*="fund_account"]'), false)) {
                return true;
            }

        });
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.main-info-block a[href*="close_account"]')));

    }
}