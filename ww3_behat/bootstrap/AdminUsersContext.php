<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 11.01.16
 * Time: 12:02
 */
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class AdminUsersContext implements Context, SnippetAcceptingContext
{
    private $username;
    private $email;
    private $phone;
    private $role;
    private $password;
    private $row_id;
    private $driver;
    private $admin_user_created;

    function __construct()
    {
        $this->driver = Support_Registry::singleton()->driver;
    }


    /**
     * @Given /^I login as admin and open admin users page$/
     */
    public function iLoginAsAdminAndOpenAdminUsersPage()
    {
        Support_AdminHelper::goToBackoffice();
        Support_AdminHelper::goToAdminUsersPage();
    }

    /**
     * @When /^I click to add user button$/
     */
    public function iClickToAddUserButton()
    {
        $this->driver->findElement(WebDriverBy::cssSelector('button.t-add-user'))->click();

        // wait for form open
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('username')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('modifier_comment')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('submit')));
    }

    /**
     * @Given /^I fill add user form$/
     */
    public function iFillAddUserForm()
    {
        $time = time();
        $this->username = "behat_$time";
        $this->email = str_replace('{suff}', $time, Support_Configs::get()->MAIL_PREF);
        $this->phone = '0123456789';
        $this->role = 'game_manager';
        $this->password = '123';

        $this->driver->findElement(WebDriverBy::id('username'))->sendKeys($this->username);
        $this->driver->findElement(WebDriverBy::id('email'))->sendKeys($this->email);
        $this->driver->findElement(WebDriverBy::id('phone'))->sendKeys($this->phone);
        $this->driver->findElement(WebDriverBy::cssSelector('#role option[value="game_manager"]'))->click();
        $this->driver->findElement(WebDriverBy::id('password'))->sendKeys($this->password);
    }

    /**
     * @Given /^I submit add user form$/
     */
    public function iSubmitAddUserForm()
    {
        $row_amount = $this->driver->findElement(WebDriverBy::cssSelector('.rows_amount'))->getText();
        $page_timestamp = $this->driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp'))->getAttribute('data-page-get');

        $this->driver->findElement(WebDriverBy::cssSelector('.form-row-buttons #submit'))->click();

        Support_Wait::forTextUpdated('.rows_amount', $row_amount);
        // wait for timestamp updated
        $page_timestamp = $this->waitForTimestampUpdated($page_timestamp);

        $this->driver->findElement(WebDriverBy::id('simple_search_keyword'))->sendKeys($this->email);
        $this->driver->findElement(WebDriverBy::cssSelector('.detail-search-buttons button[type=submit]'));

        $this->waitForTimestampUpdated($page_timestamp);

        $this->row_id = $this->driver->findElement(WebDriverBy::cssSelector('.pager_rows tr.h[key]'))->getAttribute('id');
        $this->admin_user_created = $this->email;
    }

    /**
     * @param $page_timestamp
     * @return null|string
     */
    private function waitForTimestampUpdated($page_timestamp)   //todo move to wait method
    {
        for ($sec = 0; $sec < 60; $sec++) {
            $new_page_timestamp = $this->driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp'))->getAttribute('data-page-get');

            if (is_null($new_page_timestamp)) {
                sleep(1);
                continue;
            }

            if ($new_page_timestamp !== $page_timestamp) {
                return $new_page_timestamp;
            }
            sleep(1);
        }
    }

    /**
     * @Then /^New user created$/
     */
    public function newUserCreated()
    {
        $page_timestamp = Support_AdminHelper::goToAdminUsersPage();

        $this->driver->findElement(WebDriverBy::id('simple_search_keyword'))->sendKeys($this->email);
        $this->driver->findElement(WebDriverBy::cssSelector('.detail-search-buttons button[type=submit]'));

        $this->waitForTimestampUpdated($page_timestamp);

        PHPUnit_Framework_Assert::assertEquals('1', $this->driver->findElement(WebDriverBy::cssSelector('.rows_amount'))->getText());

        $found_email = $this->driver->findElement(WebDriverBy::cssSelector('#'.$this->row_id.' .t-email span'))->getText();
        PHPUnit_Framework_Assert::assertEquals($this->email, $found_email);
    }

    /**
     * @Given /^New user can login into backoffice$/
     */
    public function newUserCanLoginIntoBackoffice()
    {
        Support_AdminHelper::logoutFromBackoffice();
        Support_AdminHelper::loginToBackofficeAs($this->username, $this->password);
    }

    /**
     * @Given /^I have any admin user$/
     */
    public function iHaveAnyAdminUser()
    {
        $this->iClickToAddUserButton();
        $this->iFillAddUserForm();
        $this->iSubmitAddUserForm();
        $this->newUserCreated();
    }

    /**
     * @When /^I open admin user$/
     */
    public function iOpenAdminUser()
    {
        Support_AdminHelper::goToAdminUsersPage();
    }

    /**
     * @Given /^I edit and save user details$/
     */
    public function iEditAndSaveUserDetails()
    {
        // wait timeout variables
        $current_time = microtime(true);
        $timeout = 60 * 1000;
        $driver = $this->driver;

        $page_timestamp = $this->driver->findElement(WebDriverBy::cssSelector('div.t-page-gen-timestamp'))->getAttribute('data-page-get');

        // find test user
        $this->driver->findElement(WebDriverBy::id('simple_search_keyword'))->clear();
        $this->driver->findElement(WebDriverBy::id('simple_search_keyword'))->sendKeys($this->email);
        $this->driver->findElement(WebDriverBy::cssSelector('.detail-search-buttons button[type=submit]'));

        $this->waitForTimestampUpdated($page_timestamp);
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id($this->row_id)));

        // store current windows data
        $current_window = $this->driver->getWindowHandle();
        $curr_window_count = count($this->driver->getWindowHandles());

        // open user details form
        $this->driver->findElement(WebDriverBy::cssSelector('#'.$this->row_id.' .t-email a[href*="adminDetails"]'))->click();

        // wait for new window
        while ($current_time + $timeout > microtime(true)) {
            $arWnd = $driver->getWindowHandles();

            if (count($arWnd) > $curr_window_count)
                break;
        }

        // select popUp window
        $new_window = end($arWnd);
        $driver->switchTo()->window($new_window);

            // wait for form open
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('username')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('modifier_comment')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('submit')));

            $this->username = 'new_'.$this->username;
            $this->driver->findElement(WebDriverBy::id('username'))->clear();
            $this->driver->findElement(WebDriverBy::id('username'))->sendKeys($this->username);

            $this->email = 'new_'.$this->email;
            $this->driver->findElement(WebDriverBy::id('email'))->clear();
            $this->driver->findElement(WebDriverBy::id('email'))->sendKeys($this->email);

            $this->phone = '0101'.$this->phone;
            $this->driver->findElement(WebDriverBy::id('phone'))->clear();
            $this->driver->findElement(WebDriverBy::id('phone'))->sendKeys($this->phone);

            $this->role = 'Ticket manager';
            $this->driver->findElement(WebDriverBy::cssSelector('#role option[value="ticket_manager"]'))->click();

            $this->password = '321';
            $this->driver->findElement(WebDriverBy::id('password'))->clear();
            $this->driver->findElement(WebDriverBy::id('password'))->sendKeys($this->password);

            $this->driver->findElement(WebDriverBy::id('submit'))->click();

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector(
                '.form-row-buttons .t-success'
            )));

        // close popup
        $driver->findElement(WebDriverBy::cssSelector('.hide_on_print button[onclick*="window.close"]'))->click();
        $driver->switchTo()->window($current_window);

        // wait for pop up close
        while ($current_time + $timeout > microtime(true)) {
            $arWnd = $this->driver->getWindowHandles();
            if (count($arWnd) === $curr_window_count)
                break;
        }

        // check that popUp is close
        PHPUnit_Framework_Assert::assertTrue($curr_window_count === count($arWnd));
    }

    /**
     * @Then /^User details changed$/
     */
    public function userDetailsChanged()
    {
        // wait timeout variables
        $current_time = microtime(true);
        $timeout = 60 * 1000;
        $driver = $this->driver;

        $page_timestamp = $this->driver->findElement(WebDriverBy::cssSelector('div.t-page-gen-timestamp'))->getAttribute('data-page-get');

            // find test user
            $this->driver->findElement(WebDriverBy::id('simple_search_keyword'))->clear();
            $this->driver->findElement(WebDriverBy::id('simple_search_keyword'))->sendKeys($this->email);
            $this->driver->findElement(WebDriverBy::cssSelector('.detail-search-buttons button[type=submit]'));

            $this->waitForTimestampUpdated($page_timestamp);
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id($this->row_id)));

            // check in page list
            $list_email = $this->driver->findElement(WebDriverBy::cssSelector('#'.$this->row_id.' .t-email a'))->getText();
            $list_phone = $this->driver->findElement(WebDriverBy::cssSelector('#'.$this->row_id.' .t-phone'))->getText();
            $list_role = $this->driver->findElement(WebDriverBy::cssSelector('#'.$this->row_id.' .t-role'))->getText();
            PHPUnit_Framework_Assert::assertEquals($this->email, $list_email);
            PHPUnit_Framework_Assert::assertEquals($this->phone, $list_phone);
            PHPUnit_Framework_Assert::assertEquals($this->role, $list_role);


        // store current windows data
        $current_window = $this->driver->getWindowHandle();
        $curr_window_count = count($this->driver->getWindowHandles());

        // open user details form
        $this->driver->findElement(WebDriverBy::cssSelector('#'.$this->row_id.' .t-email a[href*="adminDetails"]'))->click();

        // wait for new window
        while ($current_time + $timeout > microtime(true)) {
            $arWnd = $driver->getWindowHandles();

            if (count($arWnd) > $curr_window_count)
                break;
        }

        // select popUp window
        $new_window = end($arWnd);
        $driver->switchTo()->window($new_window);

            // wait for form open
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('username')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('modifier_comment')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('submit')));

            $username = $this->driver->findElement(WebDriverBy::id('username'))->getAttribute('value');
            $email = $this->driver->findElement(WebDriverBy::id('email'))->getAttribute('value');
            $phone = $this->driver->findElement(WebDriverBy::id('phone'))->getAttribute('value');

            // do assertions
            PHPUnit_Framework_Assert::assertEquals($this->username, $username);
            PHPUnit_Framework_Assert::assertEquals($this->email, $email);
            PHPUnit_Framework_Assert::assertEquals($this->phone, $phone);

        // close popup
        $driver->findElement(WebDriverBy::cssSelector('.hide_on_print button[onclick*="window.close"]'))->click();
        $driver->switchTo()->window($current_window);

        // wait for pop up close
        while ($current_time + $timeout > microtime(true)) {
            $arWnd = $this->driver->getWindowHandles();
            if (count($arWnd) === $curr_window_count)
                break;
        }

        // check that popUp is close
        PHPUnit_Framework_Assert::assertTrue($curr_window_count === count($arWnd));
    }

    /**
     * @Given /^User can login after detail changed$/
     */
    public function userCanLoginAfterDetailChanged()
    {
        $this->newUserCanLoginIntoBackoffice();
    }

    /**
     * @When /^I find and deactivate this admin user$/
     */
    public function iFindAndDeactivateThisAdminUser()
    {
        // check that element exist
        $this->driver->findElement(WebDriverBy::id($this->row_id));
        $page_timestamp = $this->driver->findElement(WebDriverBy::cssSelector('div.t-page-gen-timestamp'))->getAttribute('data-page-get');

        // disable user
        // disable admin user
        $this->driver->findElement(WebDriverBy::cssSelector('#'.$this->row_id.' .auth-user-delete-toggler.has-active_toggler_block'))->click();

        sleep(1);
        $wd_confirm = WebDriverBy::cssSelector('.v3-admin-popup-area button.save-button');
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated($wd_confirm));
        $this->driver->findElement($wd_confirm)->click();

        for ($sec = 0; $sec < 60; $sec++) {
            $new_timestamp = $this->driver->findElement(WebDriverBy::cssSelector('div.t-page-gen-timestamp'))->getAttribute('data-page-get');
            if ($page_timestamp !== $new_timestamp) {
                break;
            }
            sleep(1);
        }

        $this->admin_user_created = false; // disable deactivate user after test
        sleep(1);
    }

    /**
     * @Then /^User deactivated$/
     */
    public function userDeactivated()
    {
        $page_timestamp = $this->driver->findElement(WebDriverBy::cssSelector('div.t-page-gen-timestamp'))->getAttribute('data-page-get');

        // check user status in user list
        $this->driver->findElement(WebDriverBy::id('simple_search_keyword'))->clear();
        $this->driver->findElement(WebDriverBy::id('simple_search_keyword'))->sendKeys($this->email);
        $this->driver->findElement(WebDriverBy::id('f_deleted-0'))->click();

        $this->driver->findElement(WebDriverBy::cssSelector('.detail-search-buttons button[type=submit]'));

        $this->waitForTimestampUpdated($page_timestamp);
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id($this->row_id)));


        // check that user found
        $this->driver->findElement(WebDriverBy::id($this->row_id));

        // check user status
        PHPUnit_Framework_Assert::assertTrue(Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('#'.$this->row_id.' .auth-user-delete-toggler.active-1'), false), 'user is active!!!! but should be not active');
        Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#'.$this->row_id.' .auth-user-delete-toggler.active-0'));
    }

    /**
     * @Given /^User can not login to backoffice$/
     */
    public function userCanNotLoginToBackoffice()
    {
        // check that user can't login to backoffice
        Support_AdminHelper::logoutFromBackoffice();

        // open main page
        Support_AdminHelper::canNotLoginToBackofficeAs($this->username, $this->password);

    }
}