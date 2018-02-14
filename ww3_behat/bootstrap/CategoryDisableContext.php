<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 21.10.15
 * Time: 14:36
 */
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;


class CategoryDisableContext implements Context, SnippetAcceptingContext
{
    private $sport_category_id;
    private $region_category_id;
    private $public_category_id;

    private $sport_url;
    private $region_url;
    private $public_url;
    private $mobile_sport_url;
    private $mobile_region_url;
    private $mobile_tournament_url;
    private $game_id;
    private $odd_ext_id;
    private $game2_id;
    private $odd2_ext_id;

    private $select_game_url_1;
    private $select_game_url_2;
    private $mobile_select_game_url_1;
    private $mobile_select_game_url_2;
    private $driver;
//    private $arGames;
    /**
     * @var Support_MatchClass
     */
    private $mc;

    function __construct()
    {
        $this->driver = Support_Registry::singleton()->driver;
    }
    /**
     * @Given /^I check that games is available in public side$/
     */
    public function iCheckThatGamesIsAvailableInPublicSide()    // use this method as constructor to collect all needable test data
    {
        $this->mc = Support_Registry::singleton()->arGames[0];
        if (!$this->mc instanceof Support_MatchClass) throw new Exception ('$mc variable is not instance of MatchClass');

        // store data
        $this->sport_category_id = $this->mc->getCategorySportId();             // sport category id
        $this->region_category_id = $this->mc->getCategoryRegionId();           // region category id
        $this->public_category_id = $this->mc->getPublicCategoryId();           // public category ID
        $arGames = $this->mc->getArGames();
        $this->game_id = key($arGames);
        next($arGames);
        $this->game2_id = key($arGames);

        // build categories url
        $this->sport_url  = Support_Configs::get()->BASE_URL . 'index.php?ac=v3/sports/index#in='.$this->public_category_id.'&exp=' . $this->sport_category_id;
        $this->region_url = Support_Configs::get()->BASE_URL . 'index.php?ac=v3/sports/index#in='.$this->public_category_id.'&exp=' . $this->sport_category_id.','.$this->region_category_id;
        $this->public_url = Support_Configs::get()->BASE_URL . 'index.php?ac=v3/sports/index#in='.$this->public_category_id;
        $this->mobile_sport_url  = Support_Configs::get()->MOBILE_BASE_URL . '#/index.php?ac=mobile/sport/index#pid='.$this->public_category_id;
        $this->mobile_region_url = Support_Configs::get()->MOBILE_BASE_URL . '#/index.php?ac=mobile/category/index&pid='.$this->sport_category_id;
        $this->mobile_tournament_url = Support_Configs::get()->MOBILE_BASE_URL . '#/index.php?ac=mobile/tournament/index&pid='.$this->region_category_id;

        if (Support_Helper::isMobile()) {
            // open main page
            Support_GoPage::openMainPageOfMobileSite();

            // open sport page
            $this->driver->get($this->mobile_sport_url);
            $this->driver->wait(60)->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/sport/index#pid='.$this->public_category_id.'"]')
            ));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.active_page div[onclick*="ac=mobile/category/index&pid='.$this->sport_category_id.'"]')
            ));

            // open region page
            $this->driver->get($this->mobile_region_url);
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/category/index&pid='.$this->sport_category_id.'"]')
            ));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.active_page div[onclick*="ac=mobile/tournament/index&pid='.$this->region_category_id.'"]')
            ));

            // open tournament page
            $this->driver->get($this->mobile_tournament_url);
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/tournament/index&pid='.$this->region_category_id.'"]')
            ));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.active_page li.e-item[key="'.$this->game_id.'"]')
            ));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.active_page li.e-item[key="'.$this->game2_id.'"]')
            ));

            // get odds ext id
            $this->odd_ext_id  = $this->driver->findElement(WebDriverBy::cssSelector('.active_page .e-item[key="'.$this->game_id.'"] .e-odd' ))->getAttribute('key');
            $this->odd2_ext_id = $this->driver->findElement(WebDriverBy::cssSelector('.active_page .e-item[key="'.$this->game2_id.'"] .e-odd'))->getAttribute('key');

        } else {
            // open main page
            Support_Helper::openMainPage();

            // open sport
            $this->tryOpenPageWithGame($this->sport_url, $this->game_id);
            // open region
            $this->tryOpenPageWithGame($this->region_url, $this->game_id);
            // open public
            $this->tryOpenPageWithGame($this->public_url, $this->game_id);

            // check games is available
            $this->driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('.match-item .outcome[data-match="'.$this->game_id.'"]')
                )
            );
            $this->driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('.match-item .outcome[data-match="'.$this->game2_id.'"]')
                )
            );

            // get odds ext id
            $this->odd_ext_id = $this->driver->findElement(WebDriverBy::cssSelector('.match-item .outcome[data-match="'.$this->game_id.'"]'))->getAttribute('data-odds');
            $this->odd2_ext_id = $this->driver->findElement(WebDriverBy::cssSelector('.match-item .outcome[data-match="'.$this->game2_id.'"]'))->getAttribute('data-odds');
        }

        // build url to select game
        $this->select_game_url_1 = Support_Configs::get()->BASE_URL . 'index.php?ac=user/lb/select-bet&f_add=1&id=' . $this->game_id . '&odd_ext_id=' . $this->odd_ext_id .  '&outcome_index=1&f_exclude=1&f_remove_all_event=0';
        $this->select_game_url_2 = Support_Configs::get()->BASE_URL . 'index.php?ac=user/lb/select-bet&f_add=1&id=' . $this->game2_id .'&odd_ext_id=' . $this->odd2_ext_id . '&outcome_index=1&f_exclude=1&f_remove_all_event=0';
        $this->mobile_select_game_url_1 = Support_Configs::get()->MOBILE_BASE_URL . 'index.php?ac=mobile/ticket/select-bet&f_add=1&id=' . $this->game_id . '&odd_ext_id=' . $this->odd_ext_id .  '&outcome_index=1&f_exclude=0';
        $this->mobile_select_game_url_2 = Support_Configs::get()->MOBILE_BASE_URL . 'index.php?ac=mobile/ticket/select-bet&f_add=1&id=' . $this->game2_id .'&odd_ext_id=' . $this->odd2_ext_id . '&outcome_index=1&f_exclude=0';
    }


    /**
     * @Given /^I unpublish first game$/
     */
    public function iUnpublishFirstGame()
    {
        Support_AdminHelper::goToBackoffice();

        $this->driver->findElement(WebDriverBy::cssSelector('li.menu-list-item a[href*="ac=admin/lb/public-category/index"]'))->click();

        $this->driver->wait(120000)->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('div.t-page-gen-timestamp-LB_PublicCategory_Pager')
        ));

        // go to game
        Support_GoPage::sport($this->mc);
        Support_GoPage::country($this->mc);
        Support_GoPage::tournament($this->mc, $this->game_id);

        // unpublish game
        $this->driver->findElement(WebDriverBy::cssSelector('#r'.$this->game_id.' div.toggler'))->click();
        $this->driver->wait(120001)->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('.v3-admin-popup-area button.save-button')
        ));

        // confirm disable
        $this->driver->findElement(WebDriverBy::cssSelector('.v3-admin-popup-area button.save-button'))->click();
        sleep(2);

        // flush cache
        Support_AdminHelper::clearCache();
    }

    /**
     * @Then /^I can not see unpublished game$/
     */
    public function iCanNotSeeUnpublishedGame()
    {
        if (Support_Helper::isMobile()) {
            $url = Support_Registry::singleton()->arGames[0]->getUrlMobile();
            $this->driver->get($url);
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/tournament/index&pid='.$this->region_category_id.'"]')));
            $this->driver->navigate()->refresh();
            sleep(2);
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/tournament/index&pid='.$this->region_category_id.'"]')));


            // check that second game present
            Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('.active_page .e-item[key="' . $this->game_id . '"]'));
            Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.active_page .e-item[key="' . $this->game2_id . '"]'));

        } else {
            $this->driver->get(Support_Registry::singleton()->arGames[0]->getUrlRegular());
sleep(2);
            // check that second game present
            Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('.match-item .outcome[data-match="' . $this->game_id . '"]'));
            Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.match-item .outcome[data-match="' . $this->game2_id . '"]'));
        }
    }

    /**
     * @Given /^User can not set bet by direct link$/
     */
    public function userCanNotSetBetByDirectLink()
    {
        if (Support_Helper::isMobile()) {
            $this->driver->get($this->mobile_select_game_url_1);
            $this->driver->get($this->mobile_select_game_url_2);
//            $this->driver->get(Support_Registry::singleton()->arGames[0]->getUrlMobile());
            $this->driver->get(Support_Configs::get()->MOBILE_BASE_URL.'#/index.php?ac=mobile/ticket/detail');
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/ticket/detail"]')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #btn_place_bet')));
            // selected games
//            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .e-odd-outcome.ui-btn-active')));

            // store selected games
//            $select_bet_amount = $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter .selected_bets_amount'));
            sleep(2);

            // go to create betslip form
//            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #go_to_betslip div[onclick*="detail_ticket"]'))->click();
//            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/ticket/detail"]')));
//            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #btn_place_bet')));

            // click to place bet button
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page #btn_place_bet'))->click();

            // wait for pop-up with error message
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup_error.popup-is-opened .msg_success')));
            $message = $this->driver->findElement(WebDriverBy::cssSelector('.popup_error.popup-is-opened .msg_success'))->getText();
            PHPUnit_Framework_Assert::assertContains('deactivated', $message);

            // close popup
            $this->driver->findElement(WebDriverBy::cssSelector('.popup_error.popup-is-opened .popup_btn_wrap .popup_btn.btn'))->click();
            usleep(120000);
        } else {
            $this->driver->get($this->select_game_url_1);
            $this->driver->get($this->select_game_url_2);
            $this->driver->get(Support_Registry::singleton()->arGames[0]->getUrlRegular());

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('ticket_stake')));
            $stake_field = $this->driver->findElement(WebDriverBy::id('ticket_stake'));
            $stake_field->clear();
            $stake_field->sendKeys('100');

            $this->driver->findElement(WebDriverBy::id('ticket_insert_button'))->click();
            sleep(1);
            // check message
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content .popup-success-msg')));

            $message = $this->driver->findElement(WebDriverBy::cssSelector('#modal_content .popup-success-msg'))->getText();
            PHPUnit_Framework_Assert::assertContains('deactivated', $message);

            // close message
            $this->driver->findElement(WebDriverBy::id('custom_alert_btn'))->click();
            usleep(120000);
        }
    }

    /**
     * @Given /^All categories page and second game is available$/
     */
    public function allCategoriesPageAndSecondGameIsAvailable()
    {
        if (Support_Helper::isMobile()) {
            Support_GoPage::openMainPageOfMobileSite();
            sleep(1);

            // open sport
            $this->driver->get($this->mobile_sport_url);
            $this->driver->wait(60)->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/sport/index#pid='.$this->public_category_id.'"]')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.active_page div[onclick*="ac=mobile/category/index&pid='.$this->sport_category_id.'"]')
            ));

            // open region
            $this->driver->get($this->mobile_region_url);
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/category/index&pid='.$this->sport_category_id.'"]')
            ));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.active_page div[onclick*="ac=mobile/tournament/index&pid='.$this->region_category_id.'"]')
            ));

            // open public
            $this->driver->get($this->mobile_tournament_url);
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/tournament/index&pid='.$this->region_category_id.'"]')
            ));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page li.e-item')));
            usleep(250000);

            Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('.active_page li.e-item[key="'.$this->game_id.'"]'));
            Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.active_page li.e-item[key="'.$this->game2_id.'"]'));

        } else {
            // open main page
            $this->driver->get(Support_Configs::get()->BASE_URL);
            sleep(1);

            // open sport
            $this->driver->get($this->sport_url);
            sleep(1);
            $this->driver->navigate()->refresh();

            $this->driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('.match-item .outcome[data-match="' . $this->game2_id . '"]')
                )
            );

            // open region
            $this->driver->get($this->region_url);
            sleep(1);
            $this->driver->navigate()->refresh();

            $this->driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('.match-item .outcome[data-match="' . $this->game2_id . '"]')
                )
            );

            // open public
            $this->driver->get($this->public_url);
            sleep(1);
            $this->driver->navigate()->refresh();

            $this->driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('.match-item .outcome[data-match="' . $this->game2_id . '"]')
                )
            );


            $this->driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('.match-item .outcome[data-match="' . $this->game2_id . '"]')
                )
            );
            Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('.match-item .outcome[data-match="' . $this->game_id . '"]'));
        }
    }

    /**
     * @When /^I disable "([^"]*)" category$/
     */
    public function iDisableCategory($category_name)
    {
        Support_AdminHelper::goToBackoffice();
        $this->driver->findElement(WebDriverBy::cssSelector('li.menu-list-item a[href*="ac=admin/lb/public-category/index"]'))->click();

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_PublicCategory_Pager')
        ));
        sleep(2);

        if ($category_name === 'tournament') {
            Support_GoPage::sport($this->mc);
            Support_GoPage::country($this->mc);
            sleep(1);
            $this->driver->findElement(WebDriverBy::cssSelector('#r' . $this->public_category_id . ' td.f_active.active'))->click();
            sleep(2);
        } elseif ($category_name === 'country') {
            Support_GoPage::sport($this->mc);
            sleep(1);
            $this->driver->findElement(WebDriverBy::cssSelector('#r' . $this->region_category_id . ' td.f_active.active'))->click();
            sleep(2);

        } elseif ($category_name === 'sport') {
            $page_timestamp = $this->driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp'))->getAttribute('data-page-get');
            $this->driver->findElement(WebDriverBy::id('reset'))->click();
            $page_timestamp = Support_Wait::forPageTimestampUpdated($page_timestamp, WebDriverBy::cssSelector('.t-page-gen-timestamp'));
            $this->driver->findElement(WebDriverBy::id('f_active-0'))->click();

            $this->driver->findElement(WebDriverBy::id('simple_search_keyword'))->sendKeys($this->sport_category_id);
            usleep(120000);
            $this->driver->findElement(WebDriverBy::cssSelector('.detail-search-buttons button[type="submit"]'))->click();

            Support_Wait::forPageTimestampUpdated($page_timestamp, WebDriverBy::cssSelector('.t-page-gen-timestamp'));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('r' . $this->sport_category_id)));
            sleep(1);
            $this->driver->findElement(WebDriverBy::cssSelector('#r' . $this->sport_category_id . ' td.f_active.active'))->click();
            sleep(2);
        }

        Support_AdminHelper::clearCache();
    }

    /**
     * @Then /^"([^"]*)" page is not available$/
     */
    public function pageIsNotAvailable($page_name)
    {
        if (Support_Helper::isMobile()) {
            if ($page_name === 'tournament') {
                $this->driver->get($this->mobile_tournament_url);
                
                //http://m.ww3m.111.rsdemo.ru/#/index.php?ac=mobile/tournament/index&pid=26912&filter=0
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/tournament/index&pid=' . $this->region_category_id . '"]')
                ));

//                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .listview a[onclick*="wwmobile.gopage"]')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .empty a[onclick*="wwmobile.gopage"]')));
            } elseif ($page_name === "country") {
                $this->driver->get($this->mobile_region_url);

                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/category/index&pid='.$this->sport_category_id.'"]')
                ));


                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .listview a[onclick*="wwmobile.gopage"]')));
            } else {
                throw new Exception('not implemented');
            }
        } else {
            // prepare date
            $url = '';
//            $id = '';
//            $page_action = 'v3/sports/index';
            if ($page_name == 'tournament') {
                $url = $this->public_url;
//                $id = '' . $this->public_category_id;
            } elseif ($page_name == 'country') {
                $url = $this->region_url;
//                $id = '' . $this->region_category_id;
            } elseif ($page_name == 'sport') {
                $url = $this->sport_url;
//                $id = '' . $this->sport_category_id;
            }

            // check open url
            $this->driver->get($url);
//            $current_url = $this->driver->getCurrentURL();

//        PHPUnit_Framework_Assert::assertNotContains($url, $current_url);
//        PHPUnit_Framework_Assert::assertNotContains($id, $current_url);
//        PHPUnit_Framework_Assert::assertNotContains($page_action, $current_url);
            Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('div.tb[index="' . $this->game_id . '"]'));
            Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('div.tb[index="' . $this->game2_id . '"]'));
        }
    }

    /**
     * @Given /^"([^"]*)" page is available$/
     */
    public function pageIsAvailable($page_name)
    {
        if (Support_Helper::isMobile()) {
            if ($page_name === "sport") {
                // open sport page
                $this->driver->get($this->mobile_sport_url);
                $this->driver->wait(60)->until(WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/sport/index#pid='.$this->public_category_id.'"]')
                ));
//                sleep(999);
//                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
//                    WebDriverBy::cssSelector('.active_page div[onclick*="ac=mobile/category/index&pid='.$this->sport_category_id.'"]')
//                ));
            } elseif ($page_name === "country"){
                // open region page
                $this->driver->get($this->mobile_region_url);
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/category/index&pid='.$this->sport_category_id.'"]')
                ));
//                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
//                    WebDriverBy::cssSelector('.active_page div[onclick*="ac=mobile/tournament/index&pid='.$this->region_category_id.'"]')
//                ));
            } elseif ($page_name === "tournament") {
                // open tournament page
                $this->driver->get($this->mobile_tournament_url);
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/tournament/index&pid='.$this->region_category_id.'"]')
                ));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('.active_page li.e-item[key="'.$this->game_id.'"]')
                ));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('.active_page li.e-item[key="'.$this->game2_id.'"]')
                ));
            } else {
                throw new Exception('not implemented');
            }
        } else {
            // prepare date
            $url = '';
            $id = '';
            $page_action = 'v3/sports/index';
            if ($page_name == 'tournament') {
                $url = $this->public_url;
                $id = '' . $this->public_category_id;
            } elseif ($page_name == 'country') {
                $url = $this->region_url;
                $id = '' . $this->region_category_id;
            } elseif ($page_name == 'sport') {
                $url = $this->sport_url;
                $id = '' . $this->sport_category_id;
            }

            // check open url
            $this->driver->get($url);
            $current_url = $this->driver->getCurrentURL();

            PHPUnit_Framework_Assert::assertContains($id, $current_url);
            PHPUnit_Framework_Assert::assertContains($page_action, $current_url);
        }
    }

    /**
     * @Then /^All game categories is available$/
     */
    public function allGameCategoriesIsAvailable()
    {
        $this->pageIsAvailable('sport');
        $this->pageIsAvailable('country');
        $this->pageIsAvailable('tournament');

        if (Support_Helper::isMobile()) {
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.active_page li.e-item[key="'.$this->game_id.'"]')
            ));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.active_page li.e-item[key="'.$this->game2_id.'"]')
            ));
        } else {
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('.match-item .outcome[data-match="' . $this->game_id . '"]')
            ));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('.match-item .outcome[data-match="' . $this->game2_id . '"]')
            ));
        }
    }


    /**
     * @Given /^I check what second game are selected but disabled game is not selected$/
     */
    public function iCheckWhatSecondGameAreSelectedButDisabledGameIsNotSelected()
    {
        if (Support_Helper::isMobile()) {
            $arGames = $this->driver->findElements(WebDriverBy::cssSelector('.active_page .ticket-bet-item'));
            PHPUnit_Framework_Assert::assertEquals(1, count($arGames));
        } else {
            Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('div.tb[index="' . $this->game_id . '"]'));
            Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('div.tb[index="' . $this->game2_id . '"]'));
        }
    }

    /**
     * @Given /^if "([^"]*)" category page enabled in backoffice$/
     */
    public function ifCategoryPageEnabledInBackoffice($category_name)
    {
        Support_AdminHelper::goToBackoffice();
        $this->driver->findElement(WebDriverBy::cssSelector('li.menu-list-item a[href*="ac=admin/lb/public-category/index"]'))->click();

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('.t-page-gen-timestamp')
        ));
        sleep(2);

        if ($category_name === 'tournament') {
//            exit;
            Support_GoPage::sport($this->mc);
            Support_GoPage::country($this->mc);

            $this->driver->findElement(WebDriverBy::id('f_active-1'))->click();

            usleep(120000);
            $this->driver->findElement(WebDriverBy::cssSelector('.detail-search-buttons button[type=submit]'))->click();

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#r' . $this->public_category_id)));
sleep(3);
            $this->driver->findElement(WebDriverBy::cssSelector('#r' . $this->public_category_id . ' td.f_active'))->click();
            sleep(2);
        } elseif ($category_name === 'country') {
            Support_GoPage::sport($this->mc);
            sleep(1);
            $this->driver->findElement(WebDriverBy::id('f_active-1'))->click();
            usleep(120000);
            $this->driver->findElement(WebDriverBy::cssSelector('.detail-search-buttons button[type=submit]'))->click();

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#r' . $this->region_category_id.' td.f_active')));
            usleep(150000);
            $this->driver->findElement(WebDriverBy::cssSelector('#r' . $this->region_category_id . ' td.f_active'))->click();
            sleep(2);
        } elseif ($category_name === 'sport') {
            $page_timestamp = $this->driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp'))->getAttribute('data-page-get');
            usleep(150000);
            $this->driver->findElement(WebDriverBy::id('reset'))->click();
            $page_timestamp = Support_Wait::forPageTimestampUpdated($page_timestamp, WebDriverBy::cssSelector('.t-page-gen-timestamp'), 'data-page-get');

            usleep(150000);
            $this->driver->findElement(WebDriverBy::id('simple_search_keyword'))->clear();
            usleep(150000);
            $this->driver->findElement(WebDriverBy::id('simple_search_keyword'))->sendKeys($this->sport_category_id);
            usleep(150000);
            $this->driver->findElement(WebDriverBy::id('f_active-0'))->click();
            usleep(120000);
            $this->driver->findElement(WebDriverBy::cssSelector('.detail-search-buttons button[type="submit"]'))->click();
            usleep(150000);
            Support_Wait::forPageTimestampUpdated($page_timestamp, WebDriverBy::cssSelector('.t-page-gen-timestamp'), 'data-page-get');

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('r' . $this->sport_category_id)));
            $this->driver->findElement(WebDriverBy::cssSelector('#r' . $this->sport_category_id . ' td.f_active'))->click();
            sleep(2);
        }

        Support_AdminHelper::clearCache();
    }

    private function tryOpenPageWithGame($page_url, $game_id)
    {
        $this->driver->get($page_url, $game_id);
        try {
            $this->driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('.match-item .outcome[data-match="'.$game_id.'"]')
                )
            );
        } catch (Exception $e) {
            $this->driver->navigate()->refresh();
            $this->driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('.match-item .outcome[data-match="'.$game_id.'"]')
                )
            );
        }


    }
}