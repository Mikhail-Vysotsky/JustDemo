<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 08.02.16
 * Time: 16:26
 */
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class MobileTicketContext implements Context, SnippetAcceptingContext {

    private $driver;

    function __construct()
    {
        $this->driver = Support_Registry::singleton()->driver;
    }
    /**
     * @Given /^I go to game page$/
     */
    public function iGoToGamePage()
    {
        Support_GoPage::game_mobile();
    }

    /**
     * @When /^I select odd in "([^"]*)" game$/
     */
    public function iSelectOddInGame($control)
    {
            Support_Mobile_TicketHelper::selectBetOn($control);
    }

    /**
     * @Then /^I see that "([^"]*)" available$/
     */
    public function iSeeThatAvailable($betslip_type)
    {
        $wd_selector = WebDriverBy::cssSelector('.active_page #public_available_types .btn[onclick*="'.$betslip_type.'"]');
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated($wd_selector));
        $this->driver->findElement($wd_selector)->click();
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #public_available_types .btn.ui-btn-active[onclick*="'.$betslip_type.'"]')));
    }

    /**
     * @Given /^I can create "([^"]*)" type ticket$/
     */
    public function iCanCreateTypeTicket($control)
    {
        $mixed = false;
        $clean_control = $control;
        $control = strtolower($control);
        if ($control === 'mixed single') {
            $control = 'single';
            $mixed = true;
        }
        sleep(1);
        $this->driver->findElement(WebDriverBy::cssSelector('.active_page #btn_place_bet'))->click();

        // wait for success
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup.popup-is-opened .msg_success')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup.popup-is-opened .popup_btn_wrap .popup_btn.btn')));

        $this->driver->findElement(WebDriverBy::cssSelector('.popup.popup-is-opened .popup_btn_wrap .popup_btn.btn'))->click();
        sleep(1);

        // click to account icon
        $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter .username .logged-in'))->click();
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="mobile/player/index"]')));

        // click to "tickets" button
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div[onclick*="account_tickets)"]')));
        $this->driver->findElement(WebDriverBy::cssSelector('div[onclick*="account_tickets)"]'))->click();

        // click to ticket
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="mobile/ticket/list"]')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.ticket_list div.ticket-item[onclick]')));
        $this->driver->findElement(WebDriverBy::cssSelector('.ticket_list div.ticket-item[onclick]'))->click();

        // check ticket
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened .ticketType')));
        sleep(1);

        PHPUnit_Framework_Assert::assertContains($control, trim(strtolower($this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .ticketType'))->getText())));
        $games_count = count($this->driver->findElements(WebDriverBy::cssSelector('.popup-is-opened .ticketBets .betItem')));

        Support_Registry::singleton()->ticket = str_replace('\'', '', $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .ticketCoupon.item span.value'))->getText());

        // hardcode checks
        if ($control === "single") {
            if ($mixed) {
                PHPUnit_Framework_Assert::assertEquals(2, $games_count);
            } else {
                PHPUnit_Framework_Assert::assertEquals(1, $games_count);
            }
        }
        if ($clean_control === "multi") {
            PHPUnit_Framework_Assert::assertEquals(3, $games_count);
        }
        if ($clean_control === "Multi") {
            PHPUnit_Framework_Assert::assertEquals(4, $games_count);
        }
        if ($control === "system") {
            $bankers_count = count($this->driver->findElements(WebDriverBy::cssSelector('.popup-is-opened .ticketBets .betBanker')));
            PHPUnit_Framework_Assert::assertEquals(3, $bankers_count);
            PHPUnit_Framework_Assert::assertEquals(5, $games_count);
        }
    }

    /**
     * @Given /^I click to "([^"]*)" button for switch ticket to system$/
     */
    public function iClickToButtonForSwitchTicketToSystem($system_ticket_type)
    {
        if ($system_ticket_type === "4/5") {
            $click_to = "t-multi-4";
        } else {
            throw new Exception('no css selector found for ticket type');
        }

        $wd_selector = WebDriverBy::cssSelector('.active_page #public_available_types .btn.'.$click_to);
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated($wd_selector));
        $this->driver->findElement($wd_selector)->click();
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #public_available_types .btn.ui-btn-active.'.$click_to)));
    }

    /**
     * @Given /^I choose "([^"]*)" banks$/
     */
    public function iChooseBanks($bank_amount)
    {
        $el_banks = $this->driver->findElements(WebDriverBy::cssSelector('.ticket-bet-item .b-bank'));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page span#rows')));

        for ($i = 0; $i <= $bank_amount; $i++) {
            $store_rows = $this->driver->findElement(WebDriverBy::cssSelector('.active_page span#rows'))->getText();
            $el_banks[$i]->click();
            Support_Wait::forTextUpdated('.active_page span#rows', $store_rows);
        }
    }

    /**
     * @Given /^I create "([^"]*)" type ticket$/
     */
    public function iCreateTypeTicket($type)
    {
        $this->iCanCreateTypeTicket($type);
    }

    /**
     * @When /^My ticket is "([^"]*)"/
     */
    public function myTicketIsWon($status)
    {
        $arGames = Support_Registry::singleton()->arGames[0]->getArGames();

        $gid = key($arGames);

        if ($status === 'won') {
            Support_AdminHelper::markGame($gid, 'won');
            Support_Helper::processTicketQueue();
        } elseif ($status === 'lose') {
            Support_AdminHelper::markGame($gid, 'lose');
            Support_Helper::processTicketQueue();
        } elseif ($status === 'canceled') {
            Support_AdminHelper::setTicketStatus(Support_Registry::singleton()->ticket, 'canceled');
            Support_Helper::processTicketQueue();
        }



        Support_AdminHelper::logoutFromBackoffice();
    }

    /**
     * @Then /^I see that ticket "([^"]*)" in public interface$/
     */
    public function iSeeThatTicketInPublicInterface($expected_ticket_status)
    {
        Support_GoPage::openMainPageOfMobileSite();
        Support_Helper::loginUnderAccountOnMobileSite();


        // click to account icon
        $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter .username .logged-in'))->click();
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="mobile/player/index"]')));

        // click to "tickets" button
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div[onclick*="account_tickets)"]')));
        $this->driver->findElement(WebDriverBy::cssSelector('div[onclick*="account_tickets)"]'))->click();

        // click to ticket
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="mobile/ticket/list"]')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.ticket_list div.ticket-item[onclick]')));
        $this->driver->findElement(WebDriverBy::cssSelector('.ticket_list div.ticket-item[onclick]'))->click();

        // check ticket
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened .ticketType')));

        $curr_status = strtolower($this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .ticketStatus'))->getText());

        PHPUnit_Framework_Assert::assertEquals($expected_ticket_status, $curr_status);

//        $curr_balance = str_replace(array(' CHF', ' EUR', ' USD', '\''), '', $this->driver->findElement(WebDriverBy::cssSelector('.user_info_content .right'))->getText());
        $curr_balance = $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter .balance .no-format-balance'))->getText();

        if ($expected_ticket_status === "won") {
            PHPUnit_Framework_Assert::assertGreaterThan(100, $curr_balance);
        } elseif ($expected_ticket_status === "lost") {
            PHPUnit_Framework_Assert::assertLessThan(100, $curr_balance);
        } elseif ($expected_ticket_status === 'canceled') {
            PHPUnit_Framework_Assert::assertEquals('100', $curr_balance);
        }

    }

    /**
     * @Given /^I click to bet button$/
     */
    public function iClickToBetButton()
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page #btn_place_bet')));
        usleep(250000);
        $this->driver->findElement(WebDriverBy::cssSelector('.active_page #btn_place_bet'))->click();
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened')));
    }

    /**
     * @Given /^I open mobile livebet page$/
     */
    public function iOpenMobileLivebetPage()
    {
        $this->driver->findElement(WebDriverBy::cssSelector('#head .head-btn[onclick*="show_sidebar"]'))->click();
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.side-bar-list-item .linked[onclick*="mobile/livebet/match/index"]')));
        sleep(1);
        $this->driver->findElement(WebDriverBy::cssSelector('.side-bar-list-item .linked[onclick*="mobile/livebet/match/index"]'))->click();
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/livebet/match/index"]')));
    }

    /**
     * @Given /^I enter login and password$/
     */
    public function iEnterLoginAndPassword()
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened #username')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened #password')));

        // fill login form
        $this->driver->findElement(WebDriverBy::cssSelector('.popup.popup-is-opened #username'))->clear();
        $this->driver->findElement(WebDriverBy::cssSelector('.popup.popup-is-opened #username'))->sendKeys(Support_Registry::singleton()->account->email);
        $this->driver->findElement(WebDriverBy::cssSelector('.popup.popup-is-opened #password'))->clear();
        $this->driver->findElement(WebDriverBy::cssSelector('.popup.popup-is-opened #password'))->sendKeys(Support_Registry::singleton()->account->password);
        $this->driver->findElement(WebDriverBy::cssSelector('.popup.popup-is-opened .popup_btn_area button[type="submit"]'))->click();

        // wait for login
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#wwfooter .username .logged-in')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#wwfooter .balance .logged-in')));

        Support_Wait::forTextInElement(WebDriverBy::cssSelector('#wwfooter .username .logged-in'), Support_Registry::singleton()->account->email);

        sleep(1); //todo for any cases

    }
}