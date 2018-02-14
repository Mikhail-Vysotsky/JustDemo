<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 19.11.15
 * Time: 16:28
 */

use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Facebook\WebDriver\Firefox\FirefoxDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;

class Support_Registry
{
    /**
     * @var BeforeFeatureScope
     */
    public $scope = null;
    private static $singleton;
    /**
     * @var array(Support_MatchClass)
     */
    public $arGames = null;
    /**
     * @var RemoteWebDriver
     */
    public $driver = null;

    public $to_close = null;

    /**
     * @var Support_AccountClass
     */
    public $account = false;
    /**
     * @var Support_AccountClass
     */
    public $account_second = false;

    public $top7_list = false;
    /**
     * @var array(Support_MatchClass)
     */
    public $top7_games = null;
    public $ticket = null;
    public $stake = null;
    public $ticketQuota = null;
    public $main_event = false;
    public $changed_limits = false;
    public $admin_user_created = false;

    public $league_limit = false;

    public $arSelectedOutcomesQuota = array();
    public $current_window = null;
    public $page_timestamp = null;
    public $voucher_id = false;
    public $arVouchers = [];

    public static function singleton() {
        if (!isset(self::$singleton)) {
            self::$singleton = new self;
        }
        return self::$singleton;
    }

    function __construct()
    {
    }

    /**
     *
     */
    public static function startNewBrowserSession()
    {
        $session = Support_Registry::singleton()->driver;

        $wh = $session->getWindowHandles();
        $session->switchTo()->window($wh[0]);
        $session->manage()->deleteAllCookies();
    }

    /**
     * @return $this
     * @throws Support_ConfigsException
     */
    public function create_browser()
    {
        $sel_host = Support_Configs::get()->SELENIUM_HOST;
        $sel_port = Support_Configs::get()->SELENIUM_PORT;
        $host = "http://$sel_host:$sel_port/wd/hub";

        $capabilities = DesiredCapabilities::firefox();
        $capabilities->setCapability(FirefoxDriver::PROFILE, base64_encode(file_get_contents(Support_Configs::get()->PATH_TO_FIREFOX_PROFILE)));

        $this->to_close = $this->driver = RemoteWebDriver::create($host, $capabilities);
        return $this;
    }

    /**
     * @param WebDriverBy $selector
     * @param bool $throw_exception
     * @return bool
     * @throws Exception
     */
    public function elementNotPresent(WebDriverBy $selector, $throw_exception = true)
    {
        $not_present = false;
        try {
            $this->driver->findElement($selector);
        } catch (Exception $e) {
            $not_present = true;
        }

        if ($not_present) return true;


        if ($throw_exception) {
            $val = $selector->getValue();
            $mech = $selector->getMechanism();
            throw new Exception('Element by ' . $mech . ' is present: ' . $val);
        } else {
            return false;
        }
    }

    /**
     * @param WebDriverBy $selector
     * @param bool $throw_exception
     * @return bool
     * @throws Exception
     */
    public function elementPresent(WebDriverBy $selector, $throw_exception = true)
    {
        try {
            $this->driver->findElement($selector);
            return true;
        } catch (Exception $e) {
            if ($throw_exception) {
                $val = $selector->getValue();
                $mech = $selector->getMechanism();
                throw new Exception('Element by ' . $mech . ' is not present: ' . $val);
            } else {
                return false;
            }
        }
    }

    /**
     *
     */
    public function clean_games()
    {

        if (is_array($this->arGames))
        {
            foreach ($this->arGames as $games)
            {
                if ($games instanceof Support_MatchClass)
                {
                    $games->deleteAll();
                }
            }

            $this->arGames = [];
        }
    }

    /**
     *
     */
    public function clean_top7_games()
    {

        if (is_array($this->top7_games))
        {
            foreach ($this->top7_games as $games)
            {
                if ($games instanceof Support_MatchClass)
                {
                    $games->deleteAll();
                }
            }

            $this->top7_games = [];
        }
    }

    /**
     *
     */
    public function clean_account()
    {
        if (!$this->account) {
            if (method_exists($this->account, 'delete')) {
//                $this->account->delete(); //todo: should be implemented
            }
            $this->account = false;
        }

        if (!$this->account_second) {
            if (method_exists($this->account_second, 'delete')) {
//                $this->account_second->delete(); //todo: should be implemented
            }
            $this->account_second = false;
        }
    }

    /**
     *
     */
    public function clean_admin_user() {
        if ($this->admin_user_created) {
            Support_AdminHelper::deleteAdminUser($this->admin_user_created);   // todo should be implemented
            $this->admin_user_created = false;
        }
    }

    /**
     *
     */
    public function clean_limits() {
        if ($this->changed_limits) {
            Support_AdminHelper::setTipLimits('*', 'default');
            $this->changed_limits = false;
        }

    }

    /**
     *
     */
    public function clean_league_limits()
    {
        if ($this->league_limit) {
            Support_AdminHelper::rollbackLeagueLimits('*');
            $this->league_limit = false;
        }

    }

    /**
     *
     */
    public function clean_top7() {
        if ($this->top7_list) {
            Support_AdminHelper::deleteTop7List($this->top7_list);
            $this->top7_list = false;
        }

    }

    /**
     *
     */
    public function clean_main_event() {
        if ($this->main_event) {
            Support_AdminHelper::deleteMainEvent($this->main_event);
            $this->main_event = false;
        }
    }

    /**
     *
     */
    public function clean_instance() {
        // clean variables
        $this->ticket = null;
        $this->ticketQuota = null;
        $this->stake = null;
        $this->arSelectedOutcomesQuota = [];
        $this->current_window = null;
        $this->page_timestamp = null;
        $this->voucher_id = false;
        $this->arVouchers = [];
        $this->scope = null;

        $this->clean_games();
        $this->clean_account();
        $this->clean_admin_user();
        $this->clean_limits();
        $this->clean_league_limits();
//        $this->clean_top7();  // use on Top7Context
        $this->clean_main_event();
    }
}