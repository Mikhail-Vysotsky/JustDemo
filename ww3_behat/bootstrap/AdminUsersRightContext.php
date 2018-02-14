<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 12.01.16
 * Time: 14:40
 */
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class AdminUsersRightContext implements Context, SnippetAcceptingContext {
    private $username;
    private $password;
    private $driver;
    private $arMenuItemList;
    private $arExcludeMenuItems;

    function __construct()
    {
        $this->driver = Support_Registry::singleton()->driver;
        $this->arMenuItemList = array();
        $this->arExcludeMenuItems = array();
    }

    /**
     * @Given /^I have "([^"]*)" user$/
     */
    public function iHaveUser($user_role)
    {
        switch ($user_role) {
            case 'admin':
                $this->username = Support_Configs::get()->admin_login;
                $this->password = Support_Configs::get()->admin_password;
                break;
            case 'Game manager':
                $this->username = Support_Configs::get()->game_manager;
                $this->password = Support_Configs::get()->game_manager_password;
                break;
            case 'Ticket manager':
                $this->username = Support_Configs::get()->ticket_manager;
                $this->password = Support_Configs::get()->ticket_manager_password;
                break;
            case 'Risk officer':
                $this->username = Support_Configs::get()->risk_officer;
                $this->password = Support_Configs::get()->risk_officer_password;
                break;
            case 'Financial manager':
                $this->username = Support_Configs::get()->financial_manager;
                $this->password = Support_Configs::get()->financial_manager_password;
                break;
            default:
                $this->username = null;
                $this->password = null;
                break;
        }

    }

    /**
     * @Given /^I have full backoffice menu items list$/
     */
    public function iHaveFullBackofficeMenuItemsList()
    {
        $file_list = __DIR__.'/../test_data/admin_menu/menuItemsList.php';
        if (!file_exists($file_list)) {
            Support_AdminHelper::goToBackoffice();
            $this->arMenuItemList = $this->getMenuItemsList();

            file_put_contents($file_list, "<?php \nreturn ".var_export($this->arMenuItemList, true).';');
            Support_AdminHelper::logoutFromBackoffice();
        } else {
            echo "\n\ninclude filelist\n\n";
            $this->arMenuItemList = include($file_list);
        }
    }

    private function getMenuItemsList()
    {
        echo "get menuItems list\n";
        $arMenu = $this->driver->findElements(WebDriverBy::cssSelector('ul.menu-list li.menu-list-item a[href]'));
        $arHrefKeys = array();
        foreach ($arMenu as $item) {
            $key = explode('?', $item->getAttribute('href'));

            if (count($key) > 1) {
                $arHrefKeys[] = 'li.menu-list-item a[href*="'.$key[1].'"]';
            } else {
                continue;
            }
        }
        return $arHrefKeys;
    }

    /**
     * @When /^I login under "([^"]*)" user$/
     */
    public function iLoginUnderUser($user)
    {
        if ($user === 'admin') {
            $login = Support_Configs::get()->admin_login;
            $password = Support_Configs::get()->admin_password;
        } elseif ($user === 'Game manager') {
            $login = Support_Configs::get()->game_manager;
            $password = Support_Configs::get()->game_manager_password;
        } elseif ($user === 'Ticket manager') {
            $login = Support_Configs::get()->ticket_manager;
            $password = Support_Configs::get()->ticket_manager_password;
        } elseif ($user === 'Risk officer') {
            $login = Support_Configs::get()->risk_officer;
            $password = Support_Configs::get()->risk_officer_password;
        } elseif ($user === 'Financial manager') {
            $login = Support_Configs::get()->financial_manager;
            $password = Support_Configs::get()->financial_manager_password;
        } else {
            throw new Exception('No user found in test condition: '.__METHOD__);
        }

        if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.aHead a[href*="ac=admin/auth/logout"]'), false)) {
            $this->driver->findElement(WebDriverBy::cssSelector('.aHead a[href*="ac=admin/auth/logout"]'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('username')));
        }

        Support_AdminHelper::goToBackoffice($login, $password);
    }

    /**
     * @Then /^Available "([^"]*)" menu items$/
     */
    public function availableMenuItems($control)
    {
        $arAvailableItems = $this->arMenuItemList;

        // check menu item count
        $expected_count = count($arAvailableItems);
        $curr_count = count($this->driver->findElements(WebDriverBy::cssSelector('li.menu-list-item a[href*="ac="]')));

        PHPUnit_Framework_Assert::assertEquals($expected_count, $curr_count);
        PHPUnit_Framework_Assert::assertTrue($this->checkAbailableMenuItems($arAvailableItems));
   }

    /**
     * @Given /^Excluded menu items does not available for "([^"]*)"$/
     */
    public function excludedMenuItemsDoesNotAvailableFor($user)
    {
        $arExcludeItems = $this->getUserExcludeList($user);
        foreach ($arExcludeItems as $item) {
            Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector($item));
        }
    }

    /**
     * @Then /^Available only "([^"]*)" menu items$/
     */
    public function availableOnlyMenuItems($user)
    {
        $arExcludeItems = $this->getUserExcludeList($user);

        $this->arExcludeMenuItems = $arExcludeItems;
        $arAvailable = array_diff($this->arMenuItemList, $arExcludeItems);
        PHPUnit_Framework_Assert::assertTrue($this->checkAbailableMenuItems($arAvailable));
    }

    /**
     * @Given /^"([^"]*)" can not open not available for him menu items by prof\-link$/
     */
    public function canNotOpenNotAvailableForHimMenuItemsByProfLink($user)
    {
        $arExcludeItems = $this->getUserExcludeList($user);
        $arLinks = $this->getArLinksFromMenuItems($arExcludeItems);

        foreach ($arLinks as $url) {
            $this->driver->get($url);
            sleep(2);
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('content')));
            $curr_url = str_replace('https', 'http', $this->driver->getCurrentURL());
            PHPUnit_Framework_Assert::assertEquals(Support_Configs::get()->MANAGE_URL.'index.php?ac=admin/welcome', $curr_url);
        }
    }

    /**
     * @param $arAvailableItems
     * @return bool
     * @throws Exception
     */
    private function checkAbailableMenuItems($arAvailableItems)
    {
       // check each menu item
        $page_timestamp = time();
        foreach ($arAvailableItems as $num => $item) {
            try {
                // open each togle item
                if ($num >= 29) {
                    $this->driver->executeScript("$('.toggler_area_toggler.toggler_area_expander').click()");
                    usleep(150000);

                }
                Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector($item));

                $element = $this->driver->findElement(WebDriverBy::cssSelector($item));
                $href = $element->getAttribute('href');
                $element->click();

                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector($item . '.active')));

                if (strpos($href, 'ac=admin/ticket/index')) {
                    $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#ticketList .paginator-info')));
                } elseif (strpos($href, 'ac=admin/unbalanced_games/index')) {
                    $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('Admin_Form_UnbalancedGamesFilter')));
                } elseif (strpos($href, 'ac=admin/statistic/odds')) {
                    $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('Admin_Form_StatisticOddsFilter_area')));
                } elseif (strpos($href, 'ac=admin/sms/form')) { // just wait form
                    $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('body')));
                } elseif (strpos($href, 'ac=admin/knowledgebase/index')) {
                    $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.knowledgebase_wrapper')));
                } elseif (strpos($href, 'ac=admin/system-config/index')) {
                    $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('insurance_min_quota')));
                } elseif (strpos($href, 'ac=admin/lock_screen/pwd')) {
                    $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('delay_time')));
                    $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('password')));
                    $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('password2')));
                } elseif (strpos($href, 'ac=admin/pwd/form')) {
                    $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('old_pwd')));
                    $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('new_pwd')));
                    $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('new_pwd2')));
                } elseif (strpos($href, 'ac=admin/schedules/index')) {
                    $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('select_year')));
                    $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.reports.monthly-reports')));
                } elseif (strpos($href, 'ac=admin/v2_changes/index')) {
                    $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('select_type')));

                } else {
                    $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div.t-page-gen-timestamp[data-page-get]')));
                    $page_timestamp = Support_Wait::forAttributeUpdated(WebDriverBy::cssSelector('div.t-page-gen-timestamp[data-page-get]'), 'data-page-get', $page_timestamp);
                }
            } catch (Exception $e) {
                throw new Exception('Probably bug in menu item #'.$num.' by css selector '.$item."\n Please, check this menu item by manual.\n".$e->getMessage());
//                echo ($num.' => \''.$item."',\n");
            }
        }
        return true;
    }

    private function getArLinksFromMenuItems($arExcludeItems)
    {
        $arUrls = array();
        foreach ($arExcludeItems as $item) {
            // get action from item
            $action = str_replace('"]', '', str_replace('li.menu-list-item a[href*="', '', $item));
            $arUrls[] = Support_Configs::get()->MANAGE_URL.'index.php?'.$action;
        }
        return $arUrls;
    }

    private function getUserExcludeList($user)
    {
        $arExcludeItems = array();

        if ($user === 'Game manager') {
            $arExcludeItems = include( __DIR__.'/../test_data/admin_menu/excGameManagerItems.php');
        } elseif ($user === 'Ticket manager') {
            $arExcludeItems = include( __DIR__.'/../test_data/admin_menu/excTicketManagerItems.php');
        } elseif ($user === 'Risk officer') {
            $arExcludeItems = include( __DIR__.'/../test_data/admin_menu/excRiskOfficerItems.php');
        } elseif ($user === 'Financial manager') {
            $arExcludeItems = include( __DIR__.'/../test_data/admin_menu/excFinancialManagerItems.php');
        }
        return $arExcludeItems;
    }
}