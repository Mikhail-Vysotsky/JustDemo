<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 19.11.15
 * Time: 16:28
 */
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class BaseContext implements Context, SnippetAcceptingContext {
    private $driver;
    private $account;
    private $arGames;


    function __construct()
    {
        $this->driver = Support_Registry::singleton()->driver;
        $this->account = Support_Registry::singleton()->account;
    }

    /**
     * @Given /^I am start new browser session$/
     */
    public function iAmStartNewBrowserSession()
    {
//        var_dump(Support_Account::generatePhoneByTemplate()); exit;
        Support_Registry::startNewBrowserSession();

    }
    /**
     * @Given /^I have account with "([^"]*)" "([^"]*)" in balance$/
     */
    public function iHaveAccountWithInBalance($balance, $currency)
    {
        Support_Registry::singleton()->account = new Support_AccountClass();
        Support_Registry::singleton()->account->createNewAccount(false, $balance, $currency);
    }

    /**
     * @Given /^I have account with "([^"]*)" "([^"]*)" in balance and random phone$/
     */
    public function iHaveAccountWithInBalanceAndRandomPhone($balance, $currency)
    {
//        $phone = time();
        Support_Registry::singleton()->account = new Support_AccountClass();
        Support_Registry::singleton()->account->createNewAccount(false, $balance, $currency);
    }

    /**
     * @Given /^I have "([^"]*)" "([^"]*)" games, where league level = "([^"]*)"$/
     */
    public function iHaveGamesWhereLeagueLevel($amount, $type, $league)
    {
        $games = new Support_MatchClass();
        $games->createGames($amount, $type, $league);

        Support_Registry::singleton()->arGames[] = $games;
        PHPUnit_Framework_Assert::assertEquals($amount, count($games->getArGames()));
    }

    /**
     * @Given /^I have "([^"]*)" "([^"]*)" games with "([^"]*)" special games, where league level = "\*"$/
     */
    public function iHaveGamesWithSpecialGamesWhereLeagueLevel($amount, $type, $special_amount, $league)
    {
        $games = new Support_MatchClass();
        $games->createGames($amount, $type, $league);

        Support_Registry::singleton()->arGames[] = $games;
        PHPUnit_Framework_Assert::assertEquals($amount, count($games->getArGames()));
    }

    /**
     * @Given /^I see "([^"]*)" message$/
     */
    public function iSeeMessage($message_type)
    {
        if ($message_type === "success registration") {
            if (strpos(str_replace('https', 'http', $this->driver->getCurrentURL()), Support_Configs::get()->MOBILE_BASE_URL) !== false) {

                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened .msg_success')));
                $message_text = strtolower($this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .msg_success'))->getText());

                $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .popup_btn_wrap .popup_btn.btn'))->click();
            } else {

                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#modal_content div.popup-success-msg')
                ));
                $message_text = strtolower($this->driver->findElement(WebDriverBy::cssSelector('#modal_content div.popup-success-msg'))->getText());
                $this->driver->findElement(WebDriverBy::cssSelector('#modal_content .form-row-buttons button[type="button"]'))->click();
            }

            PHPUnit_Framework_Assert::assertContains('successfully', $message_text);
            PHPUnit_Framework_Assert::assertContains('registered', $message_text);

            usleep(120000);
        } elseif ($message_type === 'password was successful changed') {
            if (Support_Helper::isMobile()) {
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('.popup-is-opened .msg_success')
                ));
                $message_text = strtolower($this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .msg_success'))->getText());

                $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .popup_btn_wrap .popup_btn'))->click();
                usleep(150000);

                // open main mobile page
                $this->driver->get(Support_Configs::get()->MOBILE_BASE_URL);
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('.active_page[pageurl*="'.Support_Configs::get()->MOBILE_BASE_URL.'"]')
                ));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .t-account')));

            } else {
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#modal_content div.popup-success-msg')
                ));
                $message_text = strtolower($this->driver->findElement(WebDriverBy::cssSelector('#modal_content div.popup-success-msg'))->getText());

                $this->driver->findElement(WebDriverBy::cssSelector('#modal_content .form-row-buttons button[type="button"]'))->click();
            }
            PHPUnit_Framework_Assert::assertContains('password', $message_text);
            PHPUnit_Framework_Assert::assertContains('successful', $message_text);
            PHPUnit_Framework_Assert::assertContains('changed', $message_text);

            usleep(120000);
        } elseif ($message_type === 'Instructions to restore password were sent to specified E-Mail') {
            if (Support_Helper::isMobile()) {
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened .msg_success')));

                $message_text = strtolower($this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .msg_success'))->getText());
                $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .popup_btn_wrap .popup_btn'))->click();
            } else {
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#modal_content .popup-success-msg')
                ));

                $message_text = strtolower($this->driver->findElement(WebDriverBy::cssSelector('#modal_content div.popup-success-msg'))->getText());
                $this->driver->findElement(WebDriverBy::cssSelector('#modal_content .form-row-buttons button[type="button"]'))->click();
            }
            //Instructions to restore password were sent to specified E-Mail
            PHPUnit_Framework_Assert::assertContains('instructions', $message_text);
            PHPUnit_Framework_Assert::assertContains('restore', $message_text);
            PHPUnit_Framework_Assert::assertContains('password', $message_text);
            PHPUnit_Framework_Assert::assertContains('sent', $message_text);
            PHPUnit_Framework_Assert::assertContains('specified', $message_text);
            PHPUnit_Framework_Assert::assertContains('mail', $message_text);

            usleep(120000);
        } elseif ($message_type === "New password was generated and sent to E-Mail") {
            if (Support_Helper::isMobile()) {
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/player/restore_password/confirm"]')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page div.html_page div')));

                $message_text = strtolower($this->driver->findElement(WebDriverBy::cssSelector('.active_page div.html_page div'))->getText());


            } else {
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#modal_content .popup-success-msg')
                ));

                $message_text = strtolower($this->driver->findElement(WebDriverBy::cssSelector('#modal_content div.popup-success-msg'))->getText());
                $this->driver->findElement(WebDriverBy::cssSelector('#modal_content .form-row-buttons button[type="button"]'))->click();
            }

            //Instructions to restore password were sent to specified E-Mail
            PHPUnit_Framework_Assert::assertContains('new password', $message_text);
            PHPUnit_Framework_Assert::assertContains('was generated', $message_text);
            PHPUnit_Framework_Assert::assertContains('and sent to', $message_text);
            PHPUnit_Framework_Assert::assertContains('mail', $message_text);

            usleep(120000);
        } elseif ($message_type === "Successfully saved") {
            if (Support_Helper::isMobile()) {
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened .msg_success')));
                $message_text = strtolower($this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .msg_success'))->getText());

                $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .popup_btn.btn'))->click();
                usleep(150000);
            } else {
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#modal_content .popup-success-msg')
                ));

                $message_text = strtolower($this->driver->findElement(WebDriverBy::cssSelector('#modal_content div.popup-success-msg'))->getText());
                $this->driver->findElement(WebDriverBy::cssSelector('#modal_content .form-row-buttons button[type="button"]'))->click();
            }

            //Instructions to restore password were sent to specified E-Mail
            PHPUnit_Framework_Assert::assertContains('successfully', $message_text);
            PHPUnit_Framework_Assert::assertContains('saved', $message_text);
            usleep(120000);
            
        } elseif ($message_type === "Need to confirm email") {
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('#modal_content .popup-success-msg')
            ));

            $message_text = strtolower($this->driver->findElement(WebDriverBy::cssSelector('#modal_content div.popup-success-msg'))->getText());
            PHPUnit_Framework_Assert::assertContains('successfully', $message_text);
            PHPUnit_Framework_Assert::assertContains('saved', $message_text);
            PHPUnit_Framework_Assert::assertContains('confirmation', $message_text);
            PHPUnit_Framework_Assert::assertContains('instruction', $message_text);
            PHPUnit_Framework_Assert::assertContains('sent', $message_text);
            PHPUnit_Framework_Assert::assertContains(Support_Registry::singleton()->account->email, $message_text);

            $this->driver->findElement(WebDriverBy::cssSelector('#modal_content .form-row-buttons button[type="button"]'))->click();
            usleep(120000);
//        } elseif ($message_type === "new e-mail address has successfully been confirmed") {
        } elseif ($message_type === "new e-mail address has successfully been confirmed") {
            if (Support_Helper::isMobile()) {
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/player/settings/change_email"]')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened .msg_success')));
                usleep(150000);

//                $message_text = strtolower($this->driver->findElement(WebDriverBy::cssSelector('.active_page div.html_page div'))->getText());
                $message_text = strtolower($this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .msg_success'))->getText());
                $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .popup_btn.btn'))->click();
                usleep(120000);
            } else {
                //New E-mail was confirmed successfully.
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#modal_content .popup-success-msg')
                ));

                $message_text = strtolower($this->driver->findElement(WebDriverBy::cssSelector('#modal_content div.popup-success-msg'))->getText());
                $this->driver->findElement(WebDriverBy::cssSelector('#modal_content .form-row-buttons button[type="button"]'))->click();
                usleep(120000);
            }

            PHPUnit_Framework_Assert::assertContains('new', $message_text);
            PHPUnit_Framework_Assert::assertContains('mail', $message_text);
            PHPUnit_Framework_Assert::assertContains('confirmed', $message_text);
            PHPUnit_Framework_Assert::assertContains('successfully', $message_text);
        } elseif (strtolower($message_type) === 'new e-mail was confirmed successfully.') {
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened .msg_success')));
            $message_text = $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .msg_success'))->getText();

            // close popup
            $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .popup_btn'))->click();
            usleep(150000);
            
            PHPUnit_Framework_Assert::assertContains('new', $message_text);
            PHPUnit_Framework_Assert::assertContains('mail', $message_text);
            PHPUnit_Framework_Assert::assertContains('was', $message_text);
            PHPUnit_Framework_Assert::assertContains('confirmed', $message_text);
            PHPUnit_Framework_Assert::assertContains('successfully', $message_text);

        } elseif ($message_type === "Account was successfully closed") {
            //Account was successfully closed
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('#modal_content .popup-success-msg')
            ));

            $message_text = strtolower($this->driver->findElement(WebDriverBy::cssSelector('#modal_content div.popup-success-msg'))->getText());
            PHPUnit_Framework_Assert::assertContains('account', $message_text);
            PHPUnit_Framework_Assert::assertContains('successfully', $message_text);
            PHPUnit_Framework_Assert::assertContains('was', $message_text);
            PHPUnit_Framework_Assert::assertContains('closed', $message_text);

            $this->driver->findElement(WebDriverBy::cssSelector('#modal_content .form-row-buttons button[type="button"]'))->click();
            usleep(120000);

        } else {
            $expected_text = strtolower($message_type);
            $arMsgText = explode(' ', $expected_text);

            // get text for mobile ver
            if (Support_Helper::isMobile()) {
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened .msg_success')));
                $message_text = strtolower($this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .msg_success'))->getText());

                $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .popup_btn_wrap .popup_btn.btn'))->click();
                usleep(150000);

            // get text for regular ver
            } else {
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#modal_content .popup-success-msg')
                ));

                $message_text = strtolower($this->driver->findElement(WebDriverBy::cssSelector('#modal_content div.popup-success-msg'))->getText());

                $this->driver->findElement(WebDriverBy::cssSelector('#modal_content .form-row-buttons button[type="button"]'))->click();
                usleep(150000);
            }

            // check text
            foreach ($arMsgText as $msg_item) {
                PHPUnit_Framework_Assert::assertContains($msg_item, $message_text);
            }

        }
    }

    /**
     * @Given /^I have match with "([^"]*)" outright games and "([^"]*)" regular games, where league level = "([^"]*)"$/
     */
    public function iHaveMatchWithOutrightGamesAndGamesWhereLeagueLevel($amount_outright, $amount_regular, $league_level)
    {
        $games = new Support_MatchClass();
        $games->createMixedMatch($amount_outright, $amount_regular, $league_level);

        Support_Registry::singleton()->arGames[] = $games;

        $total_amount = $amount_outright + $amount_regular;
        PHPUnit_Framework_Assert::assertEquals($total_amount, count($games->getArGames()));
    }


    /**
     * @Given /^I login under account$/
     */
    public function iLoginUnderAccount()
    {
        Support_Helper::loginUnderAccount();
        
    }

    /**
     * @When /^I select bets to "([^"]*)" games$/
     */
    public function iSelectBetsToGames($control)
    {
        Support_TicketHelper::selectBetToGame($control);
    }

    /**
     * @Given /^I set stake "([^"]*)"$/
     */
    public function iSetStake($stake)
    {
        if (Support_Helper::isMobile()) {
            Support_Mobile_TicketHelper::setStake($stake);
        } else {
            Support_TicketHelper::setStake($stake);
        }
    }

    /**
     * @Given /^I click to "([^"]*)" button$/
     */
    public function iClickToButton($btn_control)
    {
        Support_Helper::clickButton($btn_control);
    }

    /**
     * @Then /^Ticket is created$/
     */
    public function ticketIsCreated()
    {
        if (Support_Helper::isMobile()) {
            // wait and close success message
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.popup-is-opened .msg_success')));
            $this->driver->findElement(WebDriverBy::cssSelector('.popup-is-opened .popup_btn_wrap .popup_btn'))->click();
            usleep(250000);

            // go to tickets page
            $this->driver->findElement(WebDriverBy::cssSelector('#wwfooter .username .footer-link.logged-in'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/player/index"]')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page div.linked[onclick*="urls.account_tickets"]')));
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page div.linked[onclick*="urls.account_tickets"]'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page[pageurl*="ac=mobile/ticket/list"]')));
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .ticket_list .ticket-item[onclick*="go_to_ticket"]')));

        } else {
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.popup-success-msg.alert-msg')
            ));

            $this->driver->findElement(WebDriverBy::id('custom_alert_btn'))->click();

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('#last_user_tickets div.ticket-list-item')
            ));
        }
    }

    /**
     * @Given /^I select "([^"]*)" ticket type$/
     */
    public function iSelectTicketType($ticket_type)
    {
        $driver = $this->driver;
        $current_ticket_type = $driver->findElement(WebDriverBy::cssSelector('#ticket_tabs div.selected[index]'))->getAttribute('index');

            if ($current_ticket_type !== $ticket_type) {
                $driver->findElement(WebDriverBy::cssSelector('#ticket_tabs div[index="'.$ticket_type.'"]'))->click();

                $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#ticket_tabs div.selected[index="'.$ticket_type.'"]')
                ));

                if ($ticket_type === "system") {
                    $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.tb_odds_row .b-bank')));
                    $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('ticket_special_value')));
                }
                return $current_ticket_type;
            } else {
                return $current_ticket_type;
            }
    }

    /**
     * @Given /^I select double bets for each games$/
     */
    public function iSelectDoubleBetsToGame()
    {
        $driver = $this->driver;
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div.double-bet-b')));

        $arItems = $driver->findElements(WebDriverBy::cssSelector('#ticket_bets div.tb[index]'));

        for ($i = 0 ; $i < count($arItems); $i++) {
            $arNewItems = $driver->findElements(WebDriverBy::cssSelector('#ticket_bets div.tb[index]'));
            $item = $arNewItems[$i];

            $odds = $item->findElements(WebDriverBy::cssSelector('div.double-bet-b'));
            foreach ($odds as $odd) {
                $rows = $driver->findElement(WebDriverBy::id('ticket_rows'));
                if (strpos($odd->getAttribute('class'), 'selected') !== false) continue;

                $odd->click();

                if (Support_Wait::forTextUpdatedInElement($rows, $rows->getText())) break;

            }
        }
    }

    /**
     * @Given /^sleep "([^"]*)"$/
     */
    public function sleep($time_seconds)
    {
        sleep($time_seconds);
    }

    /**
     * @Given /^I open game page$/
     */
    public function iOpenGamePage()
    {
        $gid = key(Support_Registry::singleton()->arGames[0]->getArGames());
        if (Support_Helper::isMobile()) {
            $this->driver->get(Support_Registry::singleton()->arGames[0]->getUrlMobile());
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.active_page .e-item[key="'.$gid.'"]')
            ));
            usleep(150000);
        } else {
            $this->driver->get(Support_Registry::singleton()->arGames[0]->getUrlRegular());
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('div[data-match="' . $gid . '"]')
            ));
        }
    }

    /**
     * @Given /^I create ticket$/
     */
    public function iCreateTicket()
    {
        $this->iOpenGamePage();
        $this->iSelectBetsToGames("each");
        $this->iSetStake('5');
        $this->iClickToButton("set");
        $this->ticketIsCreated();

        // wait timeout variables
        $driver = $this->driver;

        $driver->findElement(WebDriverBy::cssSelector('#last_user_tickets div.ticket-list-item[onclick*="open_ticket_details"]'))->click();
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content .popup-ticket-details')));

            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.ticketDetail .ticketType.item')));
            Support_Registry::singleton()->ticket = str_replace('\'', '', $this->driver->findElement(WebDriverBy::cssSelector('.ticketCoupon.item span.value'))->getText());

        // close popup
        $driver->findElement(WebDriverBy::cssSelector('#modal_content .modal_body_close .fa-close'))->click();
        usleep(200000);
    }

    /**
     * @Given /^I open \'([^\']*)\' tab$/
     */
    public function iOpenTab($control)
    {
        Support_GoPage::openTab($control);
    }

    /**
     * @Then /^User balance is greater$/
     */
    public function userBalanceIsGreater()
    {
        $current_balance = str_replace(' CHF', '', $this->driver->findElement(WebDriverBy::cssSelector('.account-block span.balance'))->getText());
        PHPUnit_Framework_Assert::assertGreaterThanOrEqual('100', $current_balance);
    }



    /**
     * @Then /^User balance is stay same$/
     */
    public function userBalanceIsStaySame()
    {
        $current_balance = str_replace(' CHF', '', $this->driver->findElement(WebDriverBy::cssSelector('.account-block span.balance'))->getText());
        PHPUnit_Framework_Assert::assertGreaterThanOrEqual('95', $current_balance);
    }

    /**
     * @Given /^User balance is "([^"]*)"$/
     */
    public function userBalanceIs($expected_balance)
    {
        sleep(2);
        $current_balance = str_replace(array(' CHF', ' EUR', ' USD', '\''), '', $this->driver->findElement(WebDriverBy::cssSelector('.account-block span.balance'))->getText());
        PHPUnit_Framework_Assert::assertEquals($expected_balance, $current_balance);

    }

    /**
     * @Given /^I open main page$/
     */
    public function iOpenMainPage()
    {
        Support_Helper::openMainPage();
    }

    /**
     * @Given /^I clear browser session and cookies$/
     */
    public function iClearBrowserSessionAndCookies()
    {
        Support_Registry::singleton()->startNewBrowserSession();
    }

    /**
     * @Given /^I open account page$/
     */
    public function iOpenAccountPage()
    {
        Support_Helper::openAccountPage();
    }

    /**
     * @Given /^I have "([^"]*)" "([^"]*)" games, where league level = "([^"]*)" and first quota "([^"]*)"$/
     */
    public function iHaveGamesWhereLeagueLevelAndFirstQuota($amount, $type, $league, $first_quota)
    {
        $games = new Support_MatchClass();
        $games->createGames($amount, $type, $league, $first_quota);

        Support_Registry::singleton()->arGames[] = $games;

        PHPUnit_Framework_Assert::assertEquals($amount, count($games->getArGames()));
    }

    /**
     * @When /^Click to withdraw button$/
     */
    public function clickToWithdrawButton()
    {
        if (Support_Helper::isMobile()) {
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page .li.linked[onclick*="urls.account_withdraw"]')));
            $this->driver->findElement(WebDriverBy::cssSelector('.active_page .li.linked[onclick*="urls.account_withdraw"]'))->click();

            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.active_page button[onclick*="mobile/player/withdraw/bank_transfer"]')));
        } else {
            $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.account-buttons a[href*="withdraw_funds"]')));
            usleep(250000);
            $this->driver->findElement(WebDriverBy::cssSelector('.account-buttons a[href*="withdraw_funds"]'))->click();

            $this->driver->wait()->until(function(){
                if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#modal_content a[href*="player_add_bank_transfer"]'), false)) {
                    return true;
                }
                if (Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('#modal_content #LB_Player_PaymentTransaction_Form'), false)) {
                    return true;
                }
            });
        }
    }

    /**
     * @Given /^User mark as advanced$/
     */
    public function userMarkAsAdvanced()
    {
        Support_AdminHelper::markUserAsAdvanced(Support_Registry::singleton()->account->email);
    }

    /**
     * @Given /^exit$/
     */
    public function tttExit() {
        exit;
    }

    /**
     * @When /^User click to "([^"]*)" button in stake block$/
     */
    public function userClickToButtonInStakeBlock($stake_button)
    {
        if ($stake_button === 'max') {
            $t_winning = $this->driver->findElement(WebDriverBy::id('ticket_winning'))->getText();
            $this->driver->findElement(WebDriverBy::id('ticket_allin_button'))->click();
            Support_Wait::forTextUpdatedInElement($this->driver->findElement(WebDriverBy::id('ticket_winning')), $t_winning);
        } elseif ($stake_button === 'C') {
            $t_winning = $this->driver->findElement(WebDriverBy::id('ticket_winning'))->getText();
            $this->driver->findElement(WebDriverBy::cssSelector('#ticket_stake_buttons .ticket-stake-clear'))->click();
            Support_Wait::forTextUpdatedInElement($this->driver->findElement(WebDriverBy::id('ticket_winning')), $t_winning);
        } elseif ($stake_button === "+") {
            $t_winning = $this->driver->findElement(WebDriverBy::id('ticket_winning'))->getText();
            $this->driver->findElement(WebDriverBy::cssSelector('.change_stake.r'))->click();
            Support_Wait::forTextUpdatedInElement($this->driver->findElement(WebDriverBy::id('ticket_winning')), $t_winning);
        } elseif ($stake_button === "-") {
            $t_winning = $this->driver->findElement(WebDriverBy::id('ticket_winning'))->getText();
            $this->driver->findElement(WebDriverBy::cssSelector('.change_stake.l'))->click();
            Support_Wait::forTextUpdatedInElement($this->driver->findElement(WebDriverBy::id('ticket_winning')), $t_winning);
        } elseif ($stake_button === '10') {
            $t_winning = $this->driver->findElement(WebDriverBy::id('ticket_winning'))->getText();
            $this->driver->findElement(WebDriverBy::cssSelector('#ticket_stake_buttons .ticket-stake-set[data-val="10"]'))->click();
            Support_Wait::forTextUpdatedInElement($this->driver->findElement(WebDriverBy::id('ticket_winning')), $t_winning);
        } elseif ($stake_button === '50') {
            $t_winning = $this->driver->findElement(WebDriverBy::id('ticket_winning'))->getText();
            $this->driver->findElement(WebDriverBy::cssSelector('#ticket_stake_buttons .ticket-stake-set[data-val="50"]'))->click();
            Support_Wait::forTextUpdatedInElement($this->driver->findElement(WebDriverBy::id('ticket_winning')), $t_winning);
        } elseif ($stake_button === '100') {
            $t_winning = $this->driver->findElement(WebDriverBy::id('ticket_winning'))->getText();
            $this->driver->findElement(WebDriverBy::cssSelector('#ticket_stake_buttons .ticket-stake-set[data-val="100"]'))->click();
            Support_Wait::forTextUpdatedInElement($this->driver->findElement(WebDriverBy::id('ticket_winning')), $t_winning);
        } elseif ($stake_button === '500') {
            $t_winning = $this->driver->findElement(WebDriverBy::id('ticket_winning'))->getText();
            $this->driver->findElement(WebDriverBy::cssSelector('#ticket_stake_buttons .ticket-stake-set[data-val="500"]'))->click();
            Support_Wait::forTextUpdatedInElement($this->driver->findElement(WebDriverBy::id('ticket_winning')), $t_winning);
        }
    }

    /**
     * @Then /^Set maximum stake$/
     */
    public function setMaximumStake()
    {
        $cur_stake = $this->driver->findElement(WebDriverBy::id('ticket_winning'))->getText();
        PHPUnit_Framework_Assert::assertEquals('500', $cur_stake);
    }

    /**
     * @Given /^"([^"]*)" button is disabled$/
     */
    public function buttonIsDisabled($btn_type)
    {
        if ($btn_type === "+") {
            $attr = $this->driver->findElement(WebDriverBy::cssSelector('.change_stake.r'))->getAttribute('class');
            PHPUnit_Framework_Assert::assertContains('not_active', $attr);
            $this->driver->findElement(WebDriverBy::cssSelector('.change_stake.r'))->click();
            sleep(2);

            $cur_stake = $this->driver->findElement(WebDriverBy::id('ticket_winning'))->getText();
            PHPUnit_Framework_Assert::assertEquals('500', $cur_stake);
        }
    }

    /**
     * @Then /^Stake is null$/
     */
    public function stakeIsNull()
    {
        $t_winning = $this->driver->findElement(WebDriverBy::id('ticket_winning'))->getText();
        PHPUnit_Framework_Assert::assertEquals('0', $t_winning);
    }

    /**
     * @When /^User "([^"]*)" times click to "([^"]*)" button$/
     */
    public function userTimesClickToButton($count, $button)
    {
        for ($i = 0; $i < $count; $i++) {
            $this->userClickToButtonInStakeBlock($button);
        }
    }

    /**
     * @Then /^Stake is "([^"]*)"$/
     */
    public function stakeIs($amount)
    {
        $t_winning = $this->driver->findElement(WebDriverBy::id('ticket_winning'))->getText();
        PHPUnit_Framework_Assert::assertEquals($amount, $t_winning);
    }

    /**
     * @Given /^I have game with "([^"]*)" special outcomes, where league level = "([^"]*)"$/
     */
    public function iHaveGameWithSpecialOutcomesWhereLeagueLevel($amount, $league)
    {
        $games = new Support_MatchClass();
        $games->createGames(1, "special", $league);

        Support_Registry::singleton()->arGames[] = $games;
        PHPUnit_Framework_Assert::assertEquals(1, count($games->getArGames()));
    }
    
    /**
     * @Given /^I have "([^"]*)" "([^"]*)" games, where league level = "([^"]*)" and min quota = "([^"]*)"$/
     */
    public function iHaveGamesWhereLeagueLevelAndMinQuota($amount, $type, $league, $min_quota)
    {
        $games = new Support_MatchClass();
        $games->createGames($amount, $type, $league, false, false, 0, $min_quota*10);

        Support_Registry::singleton()->arGames[] = $games;
        PHPUnit_Framework_Assert::assertEquals($amount, count($games->getArGames()));

    }
    
    /**
     * @Given /^I have "([^"]*)" "([^"]*)" games, where league level = "([^"]*)" and max quota = "([^"]*)"$/
     */
    public function iHaveGamesWhereLeagueLevelAndMaxQuota($amount, $type, $league, $max_quota)
    {
        $games = new Support_MatchClass();
        $games->createGames($amount, $type, $league, false, false, $max_quota*10);

        Support_Registry::singleton()->arGames[] = $games;
        PHPUnit_Framework_Assert::assertEquals($amount, count($games->getArGames()));

    }

    /**
     * @Given /^I have "([^"]*)" "([^"]*)" games, where league level = "([^"]*)", max quota = "([^"]*)" and min quota = "([^"]*)"$/
     */
    public function iHaveGamesWhereLeagueLevelAndMaxQuotaAndMinQuota($amount, $type, $league, $max_quota, $min_quota)
    {
        $games = new Support_MatchClass();
        $games->createGames($amount, $type, $league, false, false, $max_quota*10, $min_quota*10);

        Support_Registry::singleton()->arGames[] = $games;
        PHPUnit_Framework_Assert::assertEquals($amount, count($games->getArGames()));

    }


    /**
     * @Given /^I have "([^"]*)" "([^"]*)" "([^"]*)" game for livebet where league level = "([^"]*)"$/
     */
    public function iHaveGameForLivebetWhereLeagueLevel($amount, $running, $livebet, $league_level)
    {
        if ($running === 'running') $running = true;
        if ($running === 'not running') $running = false;

        $games = new Support_MatchClass();
        $games->createLivebetGames($amount, 'livebet', $running, $league_level);

        Support_Registry::singleton()->arGames[] = $games;
        PHPUnit_Framework_Assert::assertEquals($amount, count($games->getArGames()));
    }

    /**
     * @Given /^I have bonus shop items$/
     */
    public function iHaveBonusShopItems()
    {
        PHPUnit_Framework_Assert::assertTrue(Support_Helper::generateBonusShopItems());
    }


    /**
     * @Given /^I refresh page$/
     */
    public function iRefreshPage()
    {
        if (Support_Helper::isMobile()) {
            $this->driver->navigate()->refresh();
            sleep(3);

        } else {
            throw new Exception('test method are not implemented');
        }
    }

    /**
     * @Given /^If i clear browser session and cookies$/
     */
    public function ifIClearBrowserSessionAndCookies()
    {
        Support_Registry::singleton()->startNewBrowserSession();
    }

    /**
     * @When /^I login as admin$/
     */
    public function iLoginAsAdmin()
    {
        Support_AdminHelper::loginAsAdmin();
    }

    /**
     * @Given /^I open "([^"]*)" page$/
     */
    public function iOpenPage($target_page)
    {
        Support_GoPage::openAdminPage($target_page);

        if (Support_Registry::singleton()->elementPresent(WebDriverBy::id('reset'))) {
            $this->driver->findElement(WebDriverBy::id('reset'))->click();
            Support_Registry::singleton()->page_timestamp = Support_Wait::forAdminPageLoaded('.t-page-gen-timestamp', 'data-page-get', Support_Registry::singleton()->page_timestamp);
        }
    }

    /**
     * @Given /^If i login as admin$/
     */
    public function ifILoginAsAdmin()
    {
        Support_AdminHelper::loginAsAdmin();
    }

    /**
     * @Given /^I have second account$/
     */
    public function iHaveSecondAccount()
    {
        Support_Registry::singleton()->account_second = new Support_AccountClass();
        Support_Registry::singleton()->account_second->createNewAccount(false, '0', 'EUR');

        var_dump(Support_Registry::singleton()->account, Support_Registry::singleton()->account_second  ); exit;
    }
}