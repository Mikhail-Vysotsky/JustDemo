<?php
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver;

/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 25.11.15
 * Time: 16:56
 */

class Support_AdminHelper
{
    /**
     * @param $ticket
     * @param $status
     * @throws Exception
     * @throws NoSuchElementException
     * @throws Support_WWTimeoutException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function setTicketStatus($ticket, $status)
    {
        $session = Support_Registry::singleton();
        $driver = $session->driver;

        Support_AdminHelper::goToBackoffice();
        $page_timestamp = Support_AdminHelper::goToTicketsPage();

        // find ticket
//        $driver->findElement(WebDriverBy::id('id'))->clear();
//        $driver->findElement(WebDriverBy::id('id'))->sendKeys($ticket);
//        $driver->findElement(WebDriverBy::cssSelector('.detail-search-buttons button[type="submit"]'))->click();

        $driver->findElement(WebDriverBy::id('serial_num'))->clear();
        $driver->findElement(WebDriverBy::id('serial_num'))->sendKeys($ticket);
        $driver->findElement(WebDriverBy::cssSelector('.detail-search-buttons button[type="submit"]'))->click();

        self::waitForPageTimestampUpdated(WebDriverBy::cssSelector('#ticketList[data-page-timestamp]'), $page_timestamp, 'data-page-timestamp');

//        $ticket_row = $driver->findElement(WebDriverBy::id('t' . $ticket));
        $ticket_row = $driver->findElement(WebDriverBy::cssSelector('#ticketList .ticketItem'));

        $current_ticket_status = $ticket_row->findElement(WebDriverBy::cssSelector('.ticketStatus'))->getText();
        $maximum_payoff = $ticket_row->findElement(WebDriverBy::cssSelector('.ticketMaximumPayoff span.value'))->getText();
        usleep(120000);
        $ticket_row->findElement(WebDriverBy::cssSelector('.buttonEdit a[onclick*="edit"]'))->click();

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector(
            '.ui-dialog #LB_Ticket_EditForm input[name="id"]'//[value="' . $ticket . '"]'
        )));

        // change status
        if ($status === "won") {
            $driver->findElement(WebDriverBy::id('status-won'))->click();
            $driver->findElement(WebDriverBy::id('won_amount'))->clear();
            $driver->findElement(WebDriverBy::id('won_amount'))->sendKeys($maximum_payoff);
        } elseif ($status === "lost") {
            $driver->findElement(WebDriverBy::id('status-lost'))->click();
        } elseif ($status === "canceled") {
            $driver->findElement(WebDriverBy::id('status-canceled'))->click();
        }

        $driver->findElement(WebDriverBy::cssSelector('.form-row-buttons button#submit'))->click();

        // wait for ticket updated
        Support_Wait::forTextUpdated('#ticketList .ticketItem .ticketStatus', $current_ticket_status);
//        Support_Wait::forTextUpdated('#t' . $ticket . ' .ticketStatus', $current_ticket_status);
        usleep(120000);
    }

    /**
     * @param $username
     * @param $password
     * @throws Exception
     * @throws NoSuchElementException
     * @throws Support_ConfigsException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function loginToBackofficeAs($username, $password)
    {
        $session = Support_Registry::singleton();

        $driver = $session->driver;

        $driver->get(Support_Configs::get()->MANAGE_URL.'index.php?ac=admin/login');
        $driver->wait()->until(function() {
            if (Support_Registry::singleton()->elementPresent(WebDriverBy::id('inputEmail'), false)) {
                return true;
            }
            $curr_url = str_replace('https', 'http', Support_Registry::singleton()->driver->getCurrentURL());
            if (strpos($curr_url, Support_Configs::get()->MANAGE_URL) !== false) {
                return true;
            }
            sleep(1);
        });

        sleep(1);
        try {
            if (Support_Registry::singleton()->elementPresent((WebDriverBy::cssSelector('a.t-flush-cache')), false)) {
                $isBackoffice = true;
            } else {
                $isBackoffice = false;
            }
        } catch (NoSuchElementException $e) {
            $isBackoffice = false;
        }

        if (!$isBackoffice) {
            Support_AdminHelper::openLoginToBackofficePageAndTryLogin($username, $password);

            $driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::id('switch_lang_block_toggler')
                )
            );

            $driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('.aHead a[href*="ac=admin/auth/logout"]')
                )
            );


            $driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('li.menu-list-item a[href*="ac=admin/pwd/form"]')
                )
            );
        }
    }

    /**
     * @param $username
     * @param $password
     * @throws Exception
     * @throws NoSuchElementException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function canNotLoginToBackofficeAs($username, $password)
    {
        $session = Support_Registry::singleton();
        $driver = $session->driver;

        try {
            $driver->findElement(WebDriverBy::cssSelector('a.t-flush-cache'));
            $isBackoffice = true;
        } catch (NoSuchElementException $e) {
            $isBackoffice = false;
        }

        if (!$isBackoffice) {
            Support_AdminHelper::openLoginToBackofficePageAndTryLogin($username, $password);
        }
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.ui-state-error ul li')));
        sleep(3);
        $session->elementNotPresent(WebDriverBy::cssSelector('a.t-flush-cache'));
    }

    /**
     * @param bool $login
     * @param bool $password
     * @throws Support_ConfigsException
     */
    public static function goToBackoffice($login = false, $password = false)
    {
        // set default value
        if (!$login) {
            $login = Support_Configs::get()->admin_login;
            $password = Support_Configs::get()->admin_password;
        }

        Support_AdminHelper::loginToBackofficeAs($login, $password);
    }

    /**
     * @return bool|null|string
     * @throws Exception
     * @throws NoSuchElementException
     * @throws Support_WWTimeoutException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function goToTop7Page()
    {
        $session = Support_Registry::singleton();
        $driver = $session->driver;

        $driver->findElement(WebDriverBy::cssSelector('li.menu-list-item a[href*="ac=admin/lb/top7/list"]'))->click();

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Top7Match_Pager')));
        $page_timestamp = $driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Top7Match_Pager[data-page-get]'))->getAttribute('data-page-get');

        if ($page_timestamp == 0) {
            $page_timestamp = self::waitForPageTimestampUpdated(WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Top7Match_Pager[data-page-get]'), $page_timestamp);
        }

        $driver->findElement(WebDriverBy::cssSelector('#reset'))->click();

        $page_timestamp = self::waitForPageTimestampUpdated(WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Top7Match_Pager[data-page-get]'), $page_timestamp);
        return $page_timestamp;
    }

    /**
     * @return bool|null|string
     * @throws Exception
     * @throws NoSuchElementException
     * @throws Support_WWTimeoutException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function goToTicketsPage()
    {
        $session = Support_Registry::singleton();
        $driver = $session->driver;

        $driver->findElement(WebDriverBy::cssSelector('li.menu-list-item a[href*="ac=admin/ticket/index"]'))->click();
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#ticketList[data-page-timestamp]')));
        $page_timestamp = $driver->findElement(WebDriverBy::cssSelector('#ticketList[data-page-timestamp]'))->getAttribute('data-page-timestamp');

        if ($page_timestamp == 0) {
            $page_timestamp = self::waitForPageTimestampUpdated(WebDriverBy::cssSelector('#ticketList[data-page-timestamp]'), $page_timestamp, 'data-page-timestamp');
        }

        $driver->findElement(WebDriverBy::cssSelector('#ticketsFilter .reset[onclick]'))->click();

        $page_timestamp = self::waitForPageTimestampUpdated(WebDriverBy::cssSelector('#ticketList[data-page-timestamp]'), $page_timestamp, 'data-page-timestamp');
        return $page_timestamp;
    }

    /**
     * @param WebDriverBy $cssSelector
     * @param $page_timestamp
     * @param string $timestamp_attribute
     * @param int $wait_timeout
     * @return bool|null|string
     * @throws Support_WWTimeoutException
     */
    private static function waitForPageTimestampUpdated(WebDriverBy $cssSelector, $page_timestamp, $timestamp_attribute = "data-page-get", $wait_timeout = 90)  //todo: move to Support_Wait class
    {
        $session = Support_Registry::singleton();
        for ($second = 0; $second < 90; $second++) {
            if ($second == $wait_timeout) {
                throw new Support_WWTimeoutException('Timeout: can\'t reset search filter');
            }
            try {

                $new_page_timestamp = $session->driver->findElement($cssSelector)->getAttribute($timestamp_attribute);
                if ($page_timestamp !== $new_page_timestamp) {
                    return $new_page_timestamp;
                }
            } catch (Exception $e) {
            }
            sleep(1);
        }
        return false;
    }

    /**
     * @throws Exception
     * @throws NoSuchElementException
     * @throws Support_WWTimeoutException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function createTop7List()
    {
        $session = Support_Registry::singleton();
        $driver = $session->driver;

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('.t-new-top7 button[onclick*="admin/lb/top7/edit"]')
        ));

        // count exist top7 lists
        $list_count = $driver->findElement(WebDriverBy::cssSelector('.rows_amount'))->getText();

        // open edit top7 page
        $driver->findElement(WebDriverBy::cssSelector('.t-new-top7 button[onclick*="admin/lb/top7/edit"]'))->click();

        // wait until page loaded
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Top7Match_AssignedMatchPager')));
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Top7Match_MatchPager')));

        // reset search filter
        $page_timestamp = $driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Top7Match_MatchPager'))->getAttribute('data-page-get');

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('reset')));
        $driver->findElement(WebDriverBy::cssSelector('#list__LB_Top7Match_MatchPager_0 button#reset'))->click();

        // add games to top7 list
        self::waitForPageTimestampUpdated(WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Top7Match_MatchPager'), $page_timestamp);

        try { //todo: hueraga
            $arGames = null;

            if (!is_null($session->top7_games) && $session->top7_games) {
                $arGames = $session->top7_games[0];
            } else {
                $arGames = $session->arGames[0];
            }

            if ($arGames instanceof Support_MatchClass) {
                foreach ($arGames->getArGames() as $gid => $row_id) {
                    self::addGameToTop7($gid, $row_id);
                }
            }
        } catch (Exception $e) {
            echo "Exception in ".__METHOD__.' then run test: '.$e->getMessage();
        }
        // save list
        $driver->findElement(WebDriverBy::cssSelector('.assigned_match button[onclick*="save_top7_set"]'))->click();

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('#content div.t-new-top7')
        ));

        // find new top7 list
        $new_list_count = $driver->findElement(WebDriverBy::cssSelector('.rows_amount'))->getText();
        PHPUnit_Framework_Assert::assertNotEquals($new_list_count, $list_count);

        $session->top7_list = $driver->findElement(WebDriverBy::cssSelector('.pager_rows tr.h[id]'))->getAttribute('key');
    }

    /**
     * @param $top7_list
     * @throws Exception
     * @throws NoSuchElementException
     * @throws Support_WWTimeoutException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function deleteTop7List($top7_list)
    {
        $session = Support_Registry::singleton();
        $driver = $session->driver;

        Support_AdminHelper::goToBackoffice();
        Support_AdminHelper::goToTop7Page();

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Top7Match_Pager')));
        $page_timestamp = $driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Top7Match_Pager[data-page-get]'))->getAttribute('data-page-get');
        $current_window = $driver->getWindowHandle();

        // delete top7
        $elTop7 = $driver->findElement(WebDriverBy::id('r'.$top7_list));
        if ($session->elementPresent(WebDriverBy::cssSelector('a[onclick*="delete_top7_match_set"]'), false)) {

            $elTop7->findElement(WebDriverBy::cssSelector('a[onclick*="delete_top7_match_set"]'))->click();
            $driver->wait()->until(WebDriverExpectedCondition::alertIsPresent());
            $driver->switchTo()->alert()->accept();

            $driver->switchTo()->window($current_window);

            self::waitForPageTimestampUpdated( WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Top7Match_Pager[data-page-get]'), $page_timestamp);
        } else {
            $elTop7->findElement(WebDriverBy::cssSelector('.visible-toggler'))->click();

            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector(
                '.v3-admin-popup-area button.save-button'
            )));

            sleep(1);
            $driver->findElement(WebDriverBy::cssSelector('.v3-admin-popup-area button.save-button'))->click();

            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('notify_message')));
            sleep(3);
        }

        Support_AdminHelper::logoutFromBackoffice();
    }

    /**
     * @param $gid
     * @param $row_id
     * @return bool
     * @throws Exception
     * @throws NoSuchElementException
     * @throws Support_WWTimeoutException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    private static function addGameToTop7($gid, $row_id)
    {
        $session = Support_Registry::singleton();
        $driver = $session->driver;
        $pref = '#list__LB_Top7Match_MatchPager_0';
        $page_timestamp = $driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Top7Match_MatchPager'))->getAttribute('data-page-get');

        // find game
        $driver->findElement(WebDriverBy::cssSelector($pref.' #minimum_quota'))->clear();
        $driver->findElement(WebDriverBy::cssSelector($pref.' #id'))->clear();
        $driver->findElement(WebDriverBy::cssSelector($pref.' #id'))->sendKeys($gid);

        $driver->findElement(WebDriverBy::cssSelector($pref.' .detail-search-buttons button[type="submit"]'))->click();
        $page_timestamp = self::waitForPageTimestampUpdated(WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Top7Match_MatchPager'), $page_timestamp);
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector($pref.' #'.$row_id)));


        sleep(3);
        $driver->findElement(WebDriverBy::cssSelector($pref.' #'.$row_id.' td input.rc'))->click();

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('button.add_top7_matches_button')));
        sleep(3);

        $driver->findElement(WebDriverBy::cssSelector('button.add_top7_matches_button'))->click();

        $m_pref = '#list__LB_Top7Match_AssignedMatchPager_0';
        self::waitForPageTimestampUpdated(WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Top7Match_AssignedMatchPager'), $page_timestamp);
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector($m_pref.' #'.$row_id)
        ));

        return true;

    }

    /**
     *
     */
    public static function disableAllTop7Lists()
    {
        $session = Support_Registry::singleton();
        $driver = $session->driver;
        $arLists = $driver->findElements(WebDriverBy::cssSelector('.pager_rows tr.h[id][key]'));

        foreach ($arLists as $item) {
            try {
                $item->findElement(WebDriverBy::cssSelector('td.f_active.active'))->click();
            } catch (Exception $e) {}
        }
    }

    /**
     * @return bool|null|string
     * @throws Exception
     * @throws NoSuchElementException
     * @throws Support_WWTimeoutException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function goToMainEventsPage()
    {
        $session = Support_Registry::singleton();
        $driver = $session->driver;

        $driver->findElement(WebDriverBy::cssSelector('li.menu-list-item a[href*="ac=admin/mainevent/index"]'))->click();

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_MainEvent_Pager')));
        $page_timestamp = $driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_MainEvent_Pager[data-page-get]'))->getAttribute('data-page-get');

        if ($page_timestamp == 0) {
            $page_timestamp = self::waitForPageTimestampUpdated(WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_MainEvent_Pager[data-page-get]'), $page_timestamp);
        }

        $driver->findElement(WebDriverBy::cssSelector('#reset'))->click();

        $page_timestamp = self::waitForPageTimestampUpdated( WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_MainEvent_Pager[data-page-get]'), $page_timestamp);
        return $page_timestamp;

    }

    /**
     *
     */
    public static function disableAllMainEvents()
    {
        $session = Support_Registry::singleton();
        $driver = $session->driver;
        $arLists = $driver->findElements(WebDriverBy::cssSelector('.pager_rows tr.h[id][key]'));

        foreach ($arLists as $item) {
            try {
                if ($item->findElement(WebDriverBy::cssSelector('img[active="1"]'))) {
                    $item->findElement(WebDriverBy::cssSelector('img[active="1"]'))->click();
                }
                usleep(120000);
            } catch (Exception $e) {
                continue;
            }
        }

        sleep(2); //todo
    }

    /**
     * @param $tournament_title
     * @throws Exception
     * @throws NoSuchElementException
     * @throws Support_ConfigsException
     * @throws Support_WWTimeoutException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function createMainEvent($tournament_title)
    {
        $session = Support_Registry::singleton();
        $driver = $session->driver;
//        $total_events_amount = $driver->findElement(WebDriverBy::cssSelector('span.rows_amount'))->getText();

        $driver->findElement(WebDriverBy::cssSelector('button.t-add-main-event'))->click();
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('LB_MainEvent_Form')));

        // set active
        $driver->findElement(WebDriverBy::cssSelector('#f_active option[value="1"]'))->click();
        // upload file
        $input_file = $driver->findElement(WebDriverBy::id('fileupload_icon'));

        $input_file->sendKeys(Support_Configs::get()->UPLOAD_FILES_DIR.'120x120_fc.png');
        $driver->findElement(WebDriverBy::id('title_de'))->sendKeys('selenium_main '.time());
        $driver->findElement(WebDriverBy::id('title_en'))->sendKeys('selenium_main '.time());
        $driver->findElement(WebDriverBy::id('tournament_keywords'))->sendKeys($tournament_title);
        $driver->findElement(WebDriverBy::id('banner_url'))->sendKeys('http://just_test.link');

        $driver->findElement(WebDriverBy::id('submit'))->click();


        Support_Wait::forTextUpdated('span.rows_amount', $tournament_title);
        sleep(2);
        usleep(120000);
//        sleep(10);
        $session->main_event = $driver->findElement(WebDriverBy::cssSelector('tr.h[id]'))->getAttribute('key');
    }

    /**
     * @param $event_id
     * @return bool|null|string
     * @throws Exception
     * @throws NoSuchElementException
     * @throws Support_WWTimeoutException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function deleteMainEvent($event_id)
    {
        $session = Support_Registry::singleton();

        Support_AdminHelper::goToBackoffice();
        Support_AdminHelper::goToMainEventsPage();

        $driver = $session->driver;

        $driver->findElement(WebDriverBy::id('simple_search_keyword'))->sendKeys($event_id);
        $driver->findElement(WebDriverBy::cssSelector('.detail-search-buttons button[type="submit"]'))->click();

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('r'.$event_id)));
//        $current_window = $driver->getWindowHandle();

        $page_timestamp = $driver->findElement(WebDriverBy::cssSelector('div.t-page-gen-timestamp-LB_MainEvent_Pager'))->getAttribute('data-page-get');
        $driver->findElement(WebDriverBy::cssSelector('#r'.$event_id.' .visible-toggler'))->click();

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector(
            '.v3-admin-popup-area button.save-button'
        )));
        sleep(1);
        $driver->findElement(WebDriverBy::cssSelector('.v3-admin-popup-area button.save-button'))->click();
        usleep(120000);
        $page_timestamp = self::waitForPageTimestampUpdated( WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_MainEvent_Pager[data-page-get]'), $page_timestamp);

        sleep(1);

        return $page_timestamp;

    }

    /**
     * @throws Exception
     * @throws NoSuchElementException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function logoutFromBackoffice()
    {
        $session = Support_Registry::singleton();
        $driver = $session->driver;
        sleep(2);
        $driver->findElement(WebDriverBy::cssSelector('.aHead a[href*="ac=admin/auth/logout"]'))->click();
        sleep(1);
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('ticket_insert_button')));
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('username')));
    }

    /**
     * @return bool|null|string
     * @throws Exception
     * @throws NoSuchElementException
     * @throws Support_WWTimeoutException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function goToPlayerPage()
    {
        $session = Support_Registry::singleton();
        $driver = $session->driver;

        $driver->findElement(WebDriverBy::cssSelector('li.menu-list-item a[href*="ac=admin/players/list"]'))->click();

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Player_Admin_Pager')));
        $page_timestamp = $driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Player_Admin_Pager[data-page-get]'))->getAttribute('data-page-get');

        if ($page_timestamp == 0) {
            $page_timestamp = self::waitForPageTimestampUpdated( WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Player_Admin_Pager[data-page-get]'), $page_timestamp);
        }

        $driver->findElement(WebDriverBy::cssSelector('#reset'))->click();

        $page_timestamp = self::waitForPageTimestampUpdated( WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Player_Admin_Pager[data-page-get]'), $page_timestamp);

        $driver->findElement(WebDriverBy::id('f_has_new_documents-1'))->click();
        $page_timestamp = self::waitForPageTimestampUpdated( WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Player_Admin_Pager[data-page-get]'), $page_timestamp);

        $driver->findElement(WebDriverBy::id('f_has_new_documents-0'))->click();
        $page_timestamp = self::waitForPageTimestampUpdated( WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Player_Admin_Pager[data-page-get]'), $page_timestamp);

        return $page_timestamp;
    }

    /**
     * @param $email
     * @return bool|null|string
     * @throws Exception
     * @throws NoSuchElementException
     * @throws Support_WWTimeoutException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function enablePlayer($email)
    {
        $session = Support_Registry::singleton();
        $driver = $session->driver;

        $page_timestamp = $driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Player_Admin_Pager[data-page-get]'))->getAttribute('data-page-get');
        if ($page_timestamp == 0) {
            $page_timestamp = self::waitForPageTimestampUpdated( WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Player_Admin_Pager[data-page-get]'), $page_timestamp);
        }

        // find player
        $driver->findElement(WebDriverBy::id('f_deleted-1'))->click(); // show closed accounts
        $driver->findElement(WebDriverBy::id('simple_search_keyword'))->sendKeys($email);
        $driver->findElement(WebDriverBy::cssSelector('#LB_Player_Admin_PagerFilter button[type="submit"]'))->click();
        $page_timestamp = self::waitForPageTimestampUpdated( WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Player_Admin_Pager[data-page-get]'), $page_timestamp);

        $row_id = $driver->findElement(WebDriverBy::cssSelector('.pager_rows tr.h[key]'))->getAttribute('id');

        // store current windows data
        $current_window = $driver->getWindowHandle();
        $curr_window_count = count($driver->getWindowHandles());

        // open player details
        $driver->findElement(WebDriverBy::cssSelector('#'.$row_id.' .t-email a[href*="playerDetails"]'))->getLocationOnScreenOnceScrolledIntoView();
        sleep(1);
        $driver->findElement(WebDriverBy::cssSelector('#'.$row_id.' .t-email a[href*="playerDetails"]'))->click();

        // wait for popup opened
        for ($i = 0; $i < 60; $i++) {
            $arWnd = $driver->getWindowHandles();

            if (count($arWnd) > $curr_window_count)
                break;

            sleep(1);
        }

        // select popUp window
        $new_window = end($arWnd);
        $driver->switchTo()->window($new_window);
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.player-overview-account button[onclick*="toggle_close_account"]')));
                $driver->findElement(WebDriverBy::cssSelector('.player-overview-account button[onclick*="toggle_close_account"]'))->click();
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector(
                    '.v3-admin-popup-area button.save-button'
                )));
                usleep(250000);
                $driver->findElement(WebDriverBy::cssSelector('.v3-admin-popup-area button.save-button'))->click();
                //wait for success message
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.sys_msg.sys_ok')));
                usleep(250000);

        // close player details popup
        $driver->findElement(WebDriverBy::cssSelector('#admin_popup_content button[onclick*="window.close()"]'))->click();

        $driver->switchTo()->window($current_window);

        // wait for pop up close
        for ($i = 0; $i < 60; $i++) {
            $arWnd = $driver->getWindowHandles();

            if (count($arWnd) === $curr_window_count)
                break;

            sleep(1);
        }
        return true;
//        return $page_timestamp;
    }

    /**
     * @param $email
     * @throws Support_WWTimeoutException
     */
    public static function removeBlocksFromPlayer($email)
    {
        $session = Support_Registry::singleton();
        $driver = $session->driver;

        $page_timestamp = $driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Player_Admin_Pager[data-page-get]'))->getAttribute('data-page-get');
        if ($page_timestamp == 0) {
            $page_timestamp = self::waitForPageTimestampUpdated( WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Player_Admin_Pager[data-page-get]'), $page_timestamp);
        }

        // find player
        $driver->findElement(WebDriverBy::id('simple_search_keyword'))->sendKeys($email);
        $driver->findElement(WebDriverBy::cssSelector('#LB_Player_Admin_PagerFilter button[type="submit"]'))->click();
        $page_timestamp = self::waitForPageTimestampUpdated( WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Player_Admin_Pager[data-page-get]'), $page_timestamp);

        $row_id = $driver->findElement(WebDriverBy::cssSelector('.pager_rows tr.h[key]'))->getAttribute('id');

        //a[href*="remove_player_blocking"]
        $remove_block_selector = '#'.$row_id.' a[onclick*="remove_player_blocking"]';
        $remove_count = count($driver->findElements(WebDriverBy::cssSelector($remove_block_selector)));

        for ($i = 0; $i < $remove_count; $i++) {
            $handler = $driver->getWindowHandle();
            $driver->findElement(WebDriverBy::cssSelector($remove_block_selector))->click();
            $driver->switchTo()->alert()->accept();
            sleep(1);
            $driver->switchTo()->window($handler);

            $page_timestamp = self::waitForPageTimestampUpdated( WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Player_Admin_Pager[data-page-get]'), $page_timestamp);
        }

        self::logoutFromBackoffice();
    }

    /**
     * @throws Exception
     * @throws NoSuchElementException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function clearCache()
    {
        $session = Support_Registry::singleton();
        $session->driver->findElement(WebDriverBy::cssSelector('a.t-flush-cache'))->click();
        $session->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('custom_alert_dialog')));
        sleep(2);
    }

    /**
     * @param $league_level
     * @param $limit
     * @return bool
     * @throws Exception
     * @throws NoSuchElementException
     * @throws Support_WWTimeoutException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function setTipLimits($league_level, $limit)
    {
        $session = Support_Registry::singleton();
        $driver = $session->driver;
        $row_id = null;

        // default values for '*' league level
        $default_risk_per_odd = '1300';
        $default_risk_per_match = '5000';

        if ($limit === "default") {
            $limit_risk_per_odd = $default_risk_per_odd;
            $limit_risk_per_match = $default_risk_per_match;
        } else {
            $limit_risk_per_odd = $limit;
            $limit_risk_per_match = $limit * 10;
        }
        if ($league_level === '*') {
            $row_id = 'r1';
        }

        Support_AdminHelper::goToBackoffice();
        $page_timestamp = Support_AdminHelper::goToLeagueLevelsPage();

        // open league levels page
        $driver->findElement(WebDriverBy::cssSelector('#'.$row_id.' .edit a[onclick*="editLeagueLevel"]'))->click();
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('Admin_Form_LeagueLevel')));
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('max_risk_per_match-CHF')));
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('max_risk_per_match-EUR')));

        $driver->findElement(WebDriverBy::id('max_risk_per_odds_tip-CHF'))->clear();
        $driver->findElement(WebDriverBy::id('max_risk_per_odds_tip-CHF'))->sendKeys($limit_risk_per_odd);

        $driver->findElement(WebDriverBy::id('max_risk_per_odds_tip-EUR'))->clear();
        $driver->findElement(WebDriverBy::id('max_risk_per_odds_tip-EUR'))->sendKeys($limit_risk_per_odd);

        $driver->findElement(WebDriverBy::id('max_risk_per_match-CHF'))->clear();
        $driver->findElement(WebDriverBy::id('max_risk_per_match-CHF'))->sendKeys($limit_risk_per_match);

        $driver->findElement(WebDriverBy::id('max_risk_per_match-EUR'))->clear();
        $driver->findElement(WebDriverBy::id('max_risk_per_match-EUR'))->sendKeys($limit_risk_per_match);

        $driver->findElement(WebDriverBy::cssSelector('#Admin_Form_LeagueLevel #submit'))->click();

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('notify_message')));
        self::waitForPageTimestampUpdated( WebDriverBy::cssSelector('.t-page-gen-timestamp-Admin_Pager_LeagueLevel[data-page-get]'), $page_timestamp);

        Support_AdminHelper::logoutFromBackoffice();
        return true;
    }

    /**
     * @return bool|null|string
     * @throws Exception
     * @throws NoSuchElementException
     * @throws Support_WWTimeoutException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    private static function goToLeagueLevelsPage()
    {
        $session = Support_Registry::singleton();
        $driver = $session->driver;

        $driver->findElement(WebDriverBy::cssSelector('li.menu-list-item a[href*="ac=admin/league_level/list"]'))->click();

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.t-page-gen-timestamp-Admin_Pager_LeagueLevel')));
        $page_timestamp = $driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp-Admin_Pager_LeagueLevel[data-page-get]'))->getAttribute('data-page-get');

        if ($page_timestamp == 0) {
            $page_timestamp = self::waitForPageTimestampUpdated( WebDriverBy::cssSelector('..t-page-gen-timestamp-Admin_Pager_LeagueLevel[data-page-get]'), $page_timestamp);
        }

        $driver->findElement(WebDriverBy::cssSelector('#reset'))->click();

        $page_timestamp = self::waitForPageTimestampUpdated( WebDriverBy::cssSelector('.t-page-gen-timestamp-Admin_Pager_LeagueLevel[data-page-get]'), $page_timestamp);
        return $page_timestamp;

    }

    /**
     * @param $gid
     * @param $status
     * @throws Exception
     * @throws NoSuchElementException
     * @throws Support_WWTimeoutException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function markGame($gid, $status)
    {
        $session = Support_Registry::singleton();

        Support_AdminHelper::goToBackoffice();
        $page_timestamp = Support_AdminHelper::goToGameResultPage();

        $driver = $session->driver;

        $driver->wait()->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::id('simple_search_keyword')
            )
        );

        // find game
        $driver->findElement(WebDriverBy::cssSelector('input#id'))->sendKeys($gid);
        usleep(120000);
        $driver->findElement(WebDriverBy::cssSelector('.detail-search-buttons button[type=submit]'))->click();
        sleep(1);

        // wait for pager update
        self::waitForPageTimestampUpdated( WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Match_Pager[data-page-get]'), $page_timestamp);

        // wait for game present
        $driver->wait()->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('r' . $gid))
        );

        sleep(2);

        // store window handles
        $current_window = $driver->getWindowHandle();
        $curr_window_count = count($driver->getWindowHandles());

            // open game odds
            sleep(2); //$(this).match_tickets('2532')
            $wd_match = WebDriverBy::cssSelector('#r' . $gid . ' a[onclick*="match_tickets"]');
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated($wd_match));
            $driver->findElement($wd_match)->click();

        // wait for new window
        $timeout = microtime(true) + 60 * 1000;
        while ($timeout > microtime(true)) {
            $arWnd = $driver->getWindowHandles();

            if (count($arWnd) > $curr_window_count)
                break;
        }

        // select popUp window
        $new_window = end($arWnd);
        $driver->switchTo()->window($new_window);

        $driver->wait()->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.edit-odds-button')
            )
        );

        // switch to edit odds tab
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.odds-edit-outcomes-form')));

        // set new status
        if ($status === 'won') {
            $driver->findElement(WebDriverBy::cssSelector('.odds-edit-outcomes-row[data-index="1"] input.odds-won-checkbox'))->click();
        } elseif ($status === 'draw') {
            $driver->findElement(WebDriverBy::cssSelector('.odds-edit-outcomes-row[data-index="X"] input.odds-won-checkbox'))->click();
        } elseif ($status === 'lose') {
            $driver->findElement(WebDriverBy::cssSelector('.odds-edit-outcomes-row[data-index="2"] input.odds-won-checkbox'))->click();
        }

        usleep(120000);
        $driver->wait()->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('.odds-edit-outcomes-buttons .save-button')));
        $driver->findElement(WebDriverBy::cssSelector('.odds-edit-outcomes-buttons .save-button'))->click();
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('notify_message')));


        $driver->findElement(WebDriverBy::cssSelector('button[onclick*="window.close"]'))->click();

        // close popUp
        $driver->switchTo()->window($current_window);

        // wait for pop up close
        $timeout = microtime(true) + 60 * 1000;
        while ($timeout > microtime(true)) {
            $arWnd = $driver->getWindowHandles();
            if (count($arWnd) === $curr_window_count)
                break;

            sleep(1);
        }

        // check that popUp is close
        PHPUnit_Framework_Assert::assertTrue($curr_window_count === count($arWnd));
    }

    /**
     * @return bool|null|string
     * @throws Exception
     * @throws NoSuchElementException
     * @throws Support_ConfigsException
     * @throws Support_WWTimeoutException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    private static function goToGameResultPage() //todo move to GoPage method
    {
        $session = Support_Registry::singleton();
        $driver = $session->driver;

        $driver->get(Support_Configs::get()->BASE_URL.'index.php?ac=admin/lb/match/wrap&select_tab=0');
        $driver->wait()->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Match_Pager')
            )
        );

        $page_timestamp = $driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Match_Pager'))->getAttribute('data-page-get');
        $driver->findElement(WebDriverBy::cssSelector('button#reset'))->click();

        return self::waitForPageTimestampUpdated( WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Match_Pager[data-page-get]'), $page_timestamp);
    }

    /**
     * @param $league_level
     */
    public static function rollbackLeagueLimits($league_level)
    {
        Support_AdminHelper::setLeagueLimit($league_level, 'default', 'default');
    }

    /**
     * @param $league_level
     * @param string $limit_field
     * @param string $value
     * @return bool
     * @throws Exception
     * @throws NoSuchElementException
     * @throws Support_WWTimeoutException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function setLeagueLimit($league_level, $limit_field = 'default', $value = 'default')
    {
        $session = Support_Registry::singleton();

        $row_id = $league_level;
        $driver = $session->driver;

        if ($league_level === '*') {
            $row_id = 'r1';
        }

        // default limits value
        $arLimits = array(
            'min_quota' => '1.1',
            'min_games_amount' => '1',
            'max_quota' => '10000',
            'min_stake-CHF' => '5',
            'min_stake-EUR' => '1',
            'max_stake-CHF' => '500',
            'max_stake-EUR' => '500',
            'max_payoff-CHF' => '50000',
            'max_payoff-EUR' => '50000',
            'max_risk_per_odds_tip-CHF' => '1300',
            'max_risk_per_odds_tip-EUR' => '1300',
            'max_risk_per_match-CHF' => '5000',
            'max_risk_per_match-EUR' => '5000',
        );

        if ($limit_field === 'Min quota') {
            $limit_field = "min_quota";
        } elseif ($limit_field === 'Min games amount') {
            $limit_field = "min_games_amount";
        } elseif ($limit_field === 'Max quota') {
            $limit_field = 'max_quota';
        } elseif ($limit_field === "Min stake CHF") {
            $limit_field = 'min_stake-CHF';
        } elseif ($limit_field === "Min stake EUR") {
                $limit_field = 'min_stake-EUR';
        } elseif ($limit_field === "Max stake EUR") {
            $limit_field = 'max_stake-EUR';
        } elseif ($limit_field === "Max stake CHF") {
            $limit_field = 'max_stake-CHF';
        } elseif ($limit_field === "Max payoff CHF") {
            $limit_field = 'max_payoff-CHF';
        } elseif ($limit_field === "Max payoff EUR") {
            $limit_field = 'max_payoff-EUR';
        } elseif ($limit_field === "Maximum risk per odds tip CHF") {
            $limit_field = 'max_risk_per_odds_tip-CHF';
        } elseif ($limit_field === 'Maximum risk per match CHF') {
            $limit_field = 'max_risk_per_match-CHF';
        } elseif ($limit_field === "Maximum risk per odds tip EUR") {
            $limit_field = 'max_risk_per_odds_tip-EUR';
        } elseif ($limit_field === 'Maximum risk per match EUR') {
            $limit_field = 'max_risk_per_match-EUR';
        }

        Support_AdminHelper::goToBackoffice();
        $page_timestamp = Support_AdminHelper::goToLeagueLevelsPage();

        // change league
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id($row_id)));

        // open league_level
        $driver->findElement(WebDriverBy::cssSelector('#'.$row_id.' .edit a[onclick*="editLeagueLevel"]'))->click();
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('Admin_Form_LeagueLevel')));
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('max_risk_per_match-CHF')));
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('max_risk_per_match-EUR')));

        // set default limits
        if ($limit_field === 'default') {
            foreach ($arLimits as $key => $val) {
                $field = $driver->findElement(WebDriverBy::id($key));
                $field->clear();
                $field->sendKeys($val);
            }
        // set limits
        } else {
            $field = $driver->findElement(WebDriverBy::id($limit_field));
            $field->clear();
            $field->sendKeys($value);
        }

        // close league level
        $driver->findElement(WebDriverBy::cssSelector('#Admin_Form_LeagueLevel #submit'))->click();
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('notify_message')));
        self::waitForPageTimestampUpdated( WebDriverBy::cssSelector('.t-page-gen-timestamp-Admin_Pager_LeagueLevel[data-page-get]'), $page_timestamp);
        
        Support_AdminHelper::logoutFromBackoffice();

        return true;
    }

    /**
     * @param $email
     * @throws Exception
     * @throws NoSuchElementException
     * @throws Support_WWTimeoutException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function markUserAsAdvanced($email)
    {
        $session = Support_Registry::singleton();
        $driver = $session->driver;

        Support_AdminHelper::goToBackoffice();
        $page_timestamp = Support_AdminHelper::goToPlayerPage();

        $driver->findElement(WebDriverBy::id('simple_search_keyword'))->sendKeys($email);
        $driver->findElement(WebDriverBy::cssSelector('.detail-search-buttons button[type=submit]'))->click();
        $page_timestamp = self::waitForPageTimestampUpdated(WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Player_Admin_Pager'), $page_timestamp);

        // check that user found
        $row = $driver->findElement(WebDriverBy::cssSelector('.pager_rows tr.h[key]'));
        $check_email = $row->findElement(WebDriverBy::cssSelector('.t-email a'))->getText();
        PHPUnit_Framework_Assert::assertEquals($email, $check_email);

        // store current windows data
        $current_window = $driver->getWindowHandle();
        $curr_window_count = count($driver->getWindowHandles());

        // open player details
        $driver->findElement(WebDriverBy::cssSelector('tr.h .t-email a[href*="playerDetails"]'))->click();

        // wait for popup opened
        for ($i = 0; $i < 60; $i++) {
            $arWnd = $driver->getWindowHandles();

            if (count($arWnd) > $curr_window_count)
                break;

            sleep(1);
        }

        // select popUp window
        $new_window = end($arWnd);
        $driver->switchTo()->window($new_window);
                // wait confitm button
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.status-info .account_status_button button[onclick*="confirm_account"][type="button"]')));
                usleep(250000);
                $driver->findElement(WebDriverBy::cssSelector('.status-info .account_status_button button[onclick*="confirm_account"][type="button"]'))->click();
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.v3-admin-popup-area button.save-button')));
                usleep(250000);
                $driver->findElement(WebDriverBy::cssSelector('.v3-admin-popup-area button.save-button'))->click();
                //wait for success message
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.sys_msg.sys_ok')));
                usleep(150000);

                // mark advanced
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.status-info .account_status_button button[onclick*="toggle_account_advanced"][type="button"]')));
                $driver->findElement(WebDriverBy::cssSelector('.status-info .account_status_button button[onclick*="toggle_account_advanced"][type="button"]'))->click();
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.v3-admin-popup-area button.save-button')));
                usleep(250000);
                $driver->findElement(WebDriverBy::cssSelector('.v3-admin-popup-area button.save-button'))->click();
                //wait for success message
                sleep(1);
                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.sys_msg.sys_ok')));

                // close player details popup
                $driver->findElement(WebDriverBy::cssSelector('#admin_popup_content button[onclick*="window.close()"]'))->click();

        $driver->switchTo()->window($current_window);

        // wait for pop up close
        for ($i = 0; $i < 60; $i++) {
            $arWnd = $driver->getWindowHandles();

            if (count($arWnd) === $curr_window_count)
                break;

            sleep(1);
        }


//        self::waitForPageTimestampUpdated( WebDriverBy::cssSelector('.t-page-gen-timestamp-LB_Player_Admin_Pager'), $page_timestamp);

        Support_AdminHelper::logoutFromBackoffice();
    }

    /**
     * @return bool|null|string
     * @throws Exception
     * @throws NoSuchElementException
     * @throws Support_WWTimeoutException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function goToAdminUsersPage()
    {
        $session = Support_Registry::singleton();
        $driver = $session->driver;

        $driver->findElement(WebDriverBy::cssSelector('li.menu-list-item a[href*="ac=admin/auth_entity/list"]'))->click();

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div.t-page-gen-timestamp-LB_AuthEntity_Admin_Pager')));
        $page_timestamp = $driver->findElement(WebDriverBy::cssSelector('div.t-page-gen-timestamp-LB_AuthEntity_Admin_Pager'))->getAttribute('data-page-get');

        if ($page_timestamp == 0) {
            $page_timestamp = self::waitForPageTimestampUpdated(WebDriverBy::cssSelector('div.t-page-gen-timestamp-LB_AuthEntity_Admin_Pager'), $page_timestamp, 'data-page-get');
        }

        $driver->findElement(WebDriverBy::cssSelector('#list__LB_AuthEntity_Admin_Pager #reset'))->click();

        $page_timestamp = self::waitForPageTimestampUpdated(WebDriverBy::cssSelector('div.t-page-gen-timestamp-LB_AuthEntity_Admin_Pager'), $page_timestamp, 'data-page-get');
        return $page_timestamp;
    }

    /**
     * @param $admin_user_created
     * @throws Exception
     * @throws NoSuchElementException
     * @throws Support_WWTimeoutException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function deleteAdminUser($admin_user_created)
    {
        $session = Support_Registry::singleton();
        $driver = $session->driver;

        // go to admin users page
        Support_AdminHelper::logoutFromBackoffice();
        Support_AdminHelper::goToBackoffice();
        $page_timestamp = Support_AdminHelper::goToAdminUsersPage();

        // find admin user
        $driver->findElement(WebDriverBy::id('simple_search_keyword'))->clear();
        $driver->findElement(WebDriverBy::id('simple_search_keyword'))->sendKeys($admin_user_created);
        $driver->findElement(WebDriverBy::cssSelector('.detail-search-buttons button[type=submit]'))->click();

        for ($sec = 0; $sec < 60; $sec++) {
            $new_timestamp = $driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp'))->getAttribute('data-page-get');
            if ($page_timestamp !== $new_timestamp) {
                $page_timestamp = $new_timestamp;
                break;
            }
            sleep(1);
        }
        PHPUnit_Framework_Assert::assertEquals('1', $driver->findElement(WebDriverBy::cssSelector('.rows_amount'))->getText());

        // check search filter result
        $row_id = $driver->findElement(WebDriverBy::cssSelector('.pager_rows tr.h[key]'))->getAttribute('id');
        $email_to_disable = $driver->findElement(WebDriverBy::cssSelector('#'.$row_id.' .t-email span'))->getText();
        PHPUnit_Framework_Assert::assertEquals($admin_user_created, $email_to_disable);

        // disable admin user
        $driver->findElement(WebDriverBy::cssSelector('#'.$row_id.' .auth-user-delete-toggler.has-active_toggler_block'))->click();

        sleep(1);
        $wd_confirm = WebDriverBy::cssSelector('.v3-admin-popup-area button.save-button');
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated($wd_confirm));
        $driver->findElement($wd_confirm)->click();

        for ($sec = 0; $sec < 60; $sec++) {
            $new_timestamp = $driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp'))->getAttribute('data-page-get');
            if ($page_timestamp !== $new_timestamp) {
                break;
            }
            sleep(1);
        }

        Support_Wait::forTextUpdated('.rows_amount', '1');
    }

    /**
     * @throws Exception
     * @throws NoSuchElementException
     * @throws Support_ConfigsException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function loginAsAdmin()   //todo: move all login method to specified class
    {
        $driver = Support_Registry::singleton()->driver;
        if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.logout-link[href*="ac=v3/sign-out"]'), false)) {
            $driver->findElement(WebDriverBy::cssSelector('.logout-link[href*="ac=v3/sign-out"]'))->click();

            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('username')));
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('password')));
            sleep(1);
        }

        if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.account-block a.logout-link'), false)) {
            $driver->findElement(WebDriverBy::cssSelector('.account-block a.logout-link'))->click();
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('username')));
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('password')));
            sleep(1);
        }

        if (Support_Registry::singleton()->elementNotPresent(WebDriverBy::id('inputEmail'), false)) {
            $driver->get(Support_Configs::get()->BASE_URL.'admin/');

            $driver->wait()->until(function(){
                if (Support_Registry::singleton()->elementPresent(WebDriverBy::id('inputEmail'), false)) {
                    return true;
                }

                if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('a.t-flush-cache'))) {
                    return true;
                }
            });
        }

        // fill the form
        if (Support_Registry::singleton()->elementPresent(WebDriverBy::id('inputEmail'), false)) {
            $driver->findElement(WebDriverBy::id('inputEmail'))->sendKeys(Support_Configs::get()->admin_login);
            $driver->findElement(WebDriverBy::id('inputPassword'))->sendKeys(Support_Configs::get()->admin_password);
            $driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
        }

        // wait for admin page to load
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('a.t-flush-cache')));
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.aHead a[href*="ac=admin/auth/logout"]')));
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.menu-list-item a[href*="ac=admin/pwd/form"]')));
    }

    /**
     * @param string $amount
     * @param string $nominal
     * @param string $currency
     * @param string $comment
     * @return string
     */
    public static function fillVoucherForm($amount = '1', $nominal = '50', $currency = 'EUR', $comment = '')
    {
        if (strlen($comment) == 0) {
            $comment = 'Behat test vouchers '.time();
        }

        $driver = Support_Registry::singleton()->driver;
        $driver->findElement(WebDriverBy::cssSelector('#agg_voucher_currency option[value="'.$currency.'"]'))->click();
        $driver->findElement(WebDriverBy::id('agg_voucher_amount'))->sendKeys($amount);
        $driver->findElement(WebDriverBy::id('agg_voucher_nominal'))->sendKeys($nominal);
        $driver->findElement(WebDriverBy::id('comment'))->sendKeys($comment);
        usleep(150000);

        return $comment;
    }

    /**
     * @param $username
     * @param $password
     * @return bool
     * @throws Exception
     * @throws NoSuchElementException
     * @throws Support_ConfigsException
     * @throws WebDriver\Exception\TimeOutException
     * @throws null
     */
    private static function openLoginToBackofficePageAndTryLogin($username, $password)
    {
        $session = Support_Registry::singleton();
        $driver = $session->driver;

        // open backoffice page
        $driver->get(Support_Configs::get()->BASE_URL . 'admin/');

        if ( $session->elementPresent(WebDriverBy::cssSelector('a.t-flush-cache'), false)) {
            return true;
        }

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('inputEmail')));

        // login to backoffice
        $driver->findElement(WebDriverBy::id('inputEmail'))->clear();
        $driver->findElement(WebDriverBy::id('inputEmail'))->sendKeys($username);
        $driver->findElement(WebDriverBy::id('inputPassword'))->clear();
        $driver->findElement(WebDriverBy::id('inputPassword'))->sendKeys($password);

        $driver->findElement(WebDriverBy::cssSelector('.form-signin button[type="submit"]'))->click();
    }

    public static function goToV2ChangesPage()
    {
        $driver = Support_Registry::singleton()->driver;

        $driver->findElement(WebDriverBy::cssSelector('.menuItem a[href*="admin/v2_changes/index"]'))->click();
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#info_container table tr td')));
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('input[name="select_date_start"]')));
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('input[name="select_date_end"]')));
        sleep(1); // for any cases
        $driver->findElement(WebDriverBy::cssSelector('a[onclick*="reset_filter"]'))->click();
        sleep(2); // for any cases

        // set now date in filter
        $now_date = date('d.m.Y');
        $el_start = $driver->findElement(WebDriverBy::cssSelector('input[name="select_date_start"]'));
//        $el_start->clear();
        $driver->executeScript('$(\'input[name="select_date_start"]\').val(\'\')');
//        var_dump($now_date); exit;
        $el_start->sendKeys($now_date);
//        $driver->findElement(WebDriverBy::cssSelector('input[name="select_date_end"]'))->clear();
//        $driver->findElement(WebDriverBy::cssSelector('input[name="select_date_end"]'))->sendKeys($now_date);
        sleep(1);
    }

    public static function setV2ChangesType($option)
    {
        $driver = Support_Registry::singleton()->driver;
        $driver->findElement(WebDriverBy::cssSelector('#select_type option[value="'.$option.'"]'))->click();
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#select_type option[selected][value="'.$option.'"]')));
        sleep(1);
    }
}