<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 15.06.16
 * Time: 10:59
 */

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class V2ChangesContext implements Context, SnippetAcceptingContext {

    private $driver;
    private $category_name = '';
    private $category_id = '';

    function __construct()
    {
        $this->driver = Support_Registry::singleton()->driver;
        $this->category_name = '';
        $this->category_id = '';
    }

    /**
     * @Given /^I login as admin to v2 manage$/
     */
    public function iLoginAsAdminToV2manage()
    {
        // login to v2 backoffice
        $this->driver->get(Support_Configs::get()->v2_host.'admin/');
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('inputEmail')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('inputPassword')));

        $this->driver->findElement(WebDriverBy::id('inputEmail'))->sendKeys(Support_Configs::get()->v2_admin);
        $this->driver->findElement(WebDriverBy::id('inputPassword'))->sendKeys(Support_Configs::get()->v2_password);

        // wait for admin page to load
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('doQuickSearchMenu')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#quickSearchMenu_area .quickSearchMenu')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('a.menuItem[href*="ac=admin/pwd/form"]')));
        
        // enter secret word
        $this->driver->findElement(WebDriverBy::cssSelector('#quickSearchMenu_area .quickSearchMenu'))->sendKeys(Support_Configs::get()->v2_secret_word);
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('a.menuItem[href*="/index.php?ac=admin/representative/list"]')));
    }

    /**
     * @Given /^I open category page$/
     */
    public function iOpenCategoryPage()
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('a.menuItem[href*="ac=admin/lb/public-category/index"]')));
        $this->driver->findElement(WebDriverBy::cssSelector('a.menuItem[href*="ac=admin/lb/public-category/index"]'))->click();

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('button.t-create-category')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.t-page-gen-timestamp.t-page-gen-timestamp-LB_PublicCategory_Pager')));
        usleep(500000);
    }

    /**
     * @Given /^I found "([^"]*)" category$/
     */
    public function iFoundCategory($search_keyword)
    {
        $page_timestamp = $this->driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp'))->getAttribute('data-page-get');

        // reset search filter
        $this->driver->findElement(WebDriverBy::id('reset'))->click();
        $page_timestamp = Support_Wait::forPageTimestampUpdated($page_timestamp, WebDriverBy::cssSelector('.t-page-gen-timestamp'));

        $this->driver->findElement(WebDriverBy::id('simple_search_keyword'))->sendKeys($search_keyword);
        $this->driver->findElement(WebDriverBy::cssSelector('.detail-search-buttons button[type="submit"]'))->click();
        Support_Wait::forPageTimestampUpdated($page_timestamp, WebDriverBy::cssSelector('.t-page-gen-timestamp'));

        //story category name and ID
        $this->category_id = $this->driver->findElement(WebDriverBy::cssSelector('.pager_rows tr.h'))->getAttribute('id');
        $this->category_name = $this->driver->findElement(WebDriverBy::cssSelector('#'.$this->category_id .' .t-name a' ))->getText();
    }

    /**
     * @When /^I disable category$/
     */
    public function iDisableCategory()
    {
        $el_category = $this->driver->findElement(WebDriverBy::id($this->category_id));
        $el_category->findElement(WebDriverBy::cssSelector('.f_active'))->click();
        
        sleep(1);
    }

    /**
     * @When /^I delete category$/
     */
    public function iDeleteCategory()
    {
        $page_timestamp = $this->driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp'))->getAttribute('data-page-get');

        $el_category = $this->driver->findElement(WebDriverBy::id($this->category_id));
        $el_category->findElement(WebDriverBy::cssSelector('a[onclick*="delete"]'))->click();

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#custom_confirm_dialog button.button')));
        $this->driver->findElement(WebDriverBy::cssSelector('#custom_confirm_dialog button.button'))->click();

        Support_Wait::forPageTimestampUpdated($page_timestamp, WebDriverBy::cssSelector('.t-page-gen-timestamp'));
    }

    /**
     * @When /^I rename category$/
     */
    public function iRenameCategory()
    {
        $page_timestamp = $this->driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp'))->getAttribute('data-page-get');

        $el_category = $this->driver->findElement(WebDriverBy::id($this->category_id));
        $el_category->findElement(WebDriverBy::cssSelector('a[onclick*="edit_model"]'))->click();

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#edit_model #name_de')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#edit_model #name_en')));

        $this->driver->findElement(WebDriverBy::cssSelector('#edit_model #name_de'))->clear();
        $this->driver->findElement(WebDriverBy::cssSelector('#edit_model #name_de'))->sendKeys('renamed '.$this->category_name);
        $this->driver->findElement(WebDriverBy::cssSelector('#edit_model #name_en'))->clear();
        $this->driver->findElement(WebDriverBy::cssSelector('#edit_model #name_en'))->sendKeys('renamed '.$this->category_name);

        $this->driver->findElement(WebDriverBy::cssSelector('#edit_model .form-row-buttons #submit'))->click();

        $page_timestamp = Support_Wait::forPageTimestampUpdated($page_timestamp, WebDriverBy::cssSelector('.t-page-gen-timestamp'));

        // story new category name
        $this->category_name = 'renamed '.$this->category_name;
    }


    /**
     * @When /^I disable subcategory$/
     */
    public function iDisableSubcategory()
    {
        $page_timestamp = $this->driver->findElement(WebDriverBy::cssSelector('.t-page-gen-timestamp'))->getAttribute('data-page-get');

        $el_category = $this->driver->findElement(WebDriverBy::id($this->category_id));
        $el_category->findElement(WebDriverBy::cssSelector('.t-name a.binded'))->click();

        $page_timestamp = Support_Wait::forPageTimestampUpdated($page_timestamp, WebDriverBy::cssSelector('.t-page-gen-timestamp'));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('tr.h .f_active.active')));
        $this->driver->findElement(WebDriverBy::cssSelector('tr.h .f_active.active'))->click();
        
        sleep(1);
    }

    /**
     * @Then /^User can see that record about change "([^"]*)" in v3 backoffice$/
     */
    public function userCanSeeThatRecordAboutChangeInVbackoffice($change)
    {
        $isFoundEntries = false;
        Support_AdminHelper::loginAsAdmin();
        Support_AdminHelper::goToV2ChangesPage();
        switch ($change) {
            case 'disable category':
            case 'disable subcategory':
                Support_AdminHelper::setV2ChangesType('category');
                $isFoundEntries = Support_Helper::checkArrayEntryInTableRows(['f_active', 'active', 'not active', $this->category_name, date('d.m.Y')], $this->driver->findElements(WebDriverBy::cssSelector("#info_container table tr")));
                break;
            case 'delete category':
                Support_AdminHelper::setV2ChangesType('category');
                $isFoundEntries = Support_Helper::checkArrayEntryInTableRows(['delete', $this->category_name, date('d.m.Y')], $this->driver->findElements(WebDriverBy::cssSelector("#info_container table tr")));
                break;
            case 'rename category':
                Support_AdminHelper::setV2ChangesType('category');
                $isFoundEntries = Support_Helper::checkArrayEntryInTableRows(['name', $this->category_name, date('d.m.Y')], $this->driver->findElements(WebDriverBy::cssSelector("#info_container table tr")));
                break;

            default:
                break;
        }
        
        PHPUnit_Framework_Assert::assertTrue($isFoundEntries, 'can\'t found row with record about changes. '.__METHOD__);
    }
}