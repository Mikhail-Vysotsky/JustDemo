<?php

/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 20.06.16
 * Time: 10:39
 */
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class BonusPointsContext implements Context, SnippetAcceptingContext
{
    private $driver;
    private $bonus_points;
    private $arTickets = [];

    function __construct()
    {
        $this->driver = Support_Registry::singleton()->driver;
        $this->bonus_points = 0;
    }

    /**
     * @Then /^Player get bonus points$/
     */
    public function playerGetBonusPoints()
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('a[href*="ac=user/player/bonus_system/details"] span.bonus-points')));
        $bp_current = $this->driver->findElement(WebDriverBy::cssSelector('a[href*="ac=user/player/bonus_system/details"] span.bonus-points'))->getText();
        $bp_current = trim(str_replace(' BP', '', $bp_current));

        PHPUnit_Framework_Assert::assertTrue($bp_current > 0);
        PHPUnit_Framework_Assert::assertTrue(strlen($bp_current) > 0);
        PHPUnit_Framework_Assert::assertTrue($bp_current != ' ');
        PHPUnit_Framework_Assert::assertTrue($bp_current != '');
    }

    /**
     * @Then /^Player "([^"]*)" get bonus points$/
     */
    public function playerGetBonusPoints__($bp_expected)
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('a[href*="ac=user/player/bonus_system/details"] span.bonus-points')));
        $bp_current = $this->driver->findElement(WebDriverBy::cssSelector('a[href*="ac=user/player/bonus_system/details"] span.bonus-points'))->getText();
        $bp_current = trim(str_replace(' BP', '', $bp_current));

        PHPUnit_Framework_Assert::assertEquals($bp_expected, $bp_current);
    }

    /**
     * @Then /^Player do not have bonus points$/
     */
    public function playerDoNotHaveBonusPoints()
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('a[href*="ac=user/player/bonus_system/details"] span.bonus-points')));
        $bp_current = $this->driver->findElement(WebDriverBy::cssSelector('a[href*="ac=user/player/bonus_system/details"] span.bonus-points'))->getText();
        $bp_current = trim(str_replace(' BP', '', $bp_current));

        PHPUnit_Framework_Assert::assertEquals('0', $bp_current);
    }

    /**
     * @Given /^I have account with "([^"]*)" "([^"]*)" in balance and "([^"]*)" bonus points$/
     */
    public function iHaveAccountWithInBalanceAndBonusPoints($amount, $currency, $bonus_points)
    {
        Support_Registry::singleton()->account = new Support_AccountClass();
        Support_Registry::singleton()->account->createNewAccount(false, $amount, $currency, false, $bonus_points);
    }

    /**
     * @When /^I go to bonus system page$/
     */
    public function iGoToBonusSystemPage()
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.account-block a.form-link[href*="ac=user/player/bonus_system/cards"]')));
        $this->driver->findElement(WebDriverBy::cssSelector('.account-block a.form-link[href*="ac=user/player/bonus_system/cards"]'))->click();

        // wait for page to load
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.bonus-system-page-left-menu')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.bonus-system-page-left-menu .item a[href*="user/player/bonus_system/rules"]')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.bonus-system-page-left-menu .item a[href*="user/player/bonus_system/details"]')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.bonus-system-page-left-menu .item a[href*="user/player/bonus_system/shop"]')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.bonus-system-page-left-menu .item a[href*="user/player/bonus_system/cards"]')));

        usleep(250000);
    }

    /**
     * @Given /^I go to bonus shop page$/
     */
    public function iGoToBonusShopPage()
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.bonus-system-page-left-menu .item a[href*="user/player/bonus_system/shop"]')));
        $this->driver->findElement(WebDriverBy::cssSelector('.bonus-system-page-left-menu .item a[href*="user/player/bonus_system/shop"]'))->click();

        // wait for page to load
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#bonus-system-shop-list-area')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.buttons-area .price')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.item')));
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div[onclick*="start_buy_product"]')));
    }

    /**
     * @Then /^I see valid bonus shop page$/
     */
    public function iSeeValidBonusShopPage()
    {
        $ar_elts = $this->driver->findElements(WebDriverBy::cssSelector('.bonus-system-shop-list .item'));
        PHPUnit_Framework_Assert::assertEquals(10, count($ar_elts));
    }

    /**
     * @Given /^I buy \'([^\']*)\' in bonus shop$/
     */
    public function iBuyInBonusShop($buy_item_name)
    {
        $this->bonus_points = $this->driver->findElement(WebDriverBy::cssSelector('.account-block .bonus-points'))->getText();
        $this->bonus_points = trim(str_replace(' BP', '', $this->bonus_points));
        
        $buy_item_name = trim(strtolower($buy_item_name));
        $ar_elts = $this->driver->findElements(WebDriverBy::cssSelector('.bonus-system-shop-list .item'));

        foreach ($ar_elts as $item) {
            $title = $item->findElement(WebDriverBy::cssSelector('.head-content .title-left'))->getText();
            $title = trim(strtolower($title));
            if (strpos($title, $buy_item_name) !== false) {
                $item->findElement(WebDriverBy::cssSelector('.buttons-area div[onclick*="start_buy_product"]'))->click();

                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content .product-detail')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content #custom_alert_btn')));

                $this->driver->findElement(WebDriverBy::cssSelector('#modal_content #custom_alert_btn'))->click();

                // wait success message
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content .popup-success-msg.alert-msg')));
                $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#modal_content #custom_alert_btn')));
                $this->driver->findElement(WebDriverBy::cssSelector('#modal_content #custom_alert_btn'))->click();
                usleep(250000);
                break;
            }

        }
    }

    /**
     * @Given /^I see that bonus point balance is "([^"]*)"$/
     */
    public function iSeeThatBonusPointBalanceIs($expected_balance)
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.account-block .bonus-points')));

        $balance = $this->driver->findElement(WebDriverBy::cssSelector('.account-block .bonus-points'))->getText();
        $balance = trim(str_replace(' BP', '', $balance));

//        var_dump($expected_balance, $balance); exit;
        PHPUnit_Framework_Assert::assertEquals($expected_balance, $balance);
    }

    /**
     * @Given /^I see card in bonus points tab$/
     */
    public function iSeeCardInBonusSystemTab()
    {
        $this->iGoToBonusSystemPage();
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.public-search-table .bonus-system-cards-progress')));
    }

    /**
     * @Given /^I see card in money source select box$/
     */
    public function iSeeCardInMoneySourceSelectBox()
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#select-money-source-boxSelectBoxItOptions .selectboxit-option')));
        $moneySources = $this->driver->findElements(WebDriverBy::cssSelector('#select-money-source-boxSelectBoxItOptions .selectboxit-option'));

        $isCardExist = false;
        foreach ($moneySources as $moneySource) {
            $source_label = $moneySource->getAttribute('data-text');
            if (strpos($source_label, 'Card')!==false) $isCardExist = true;
        }

        PHPUnit_Framework_Assert::assertTrue($isCardExist, 'can\'t find card money source');
    }

    /**
     * @Given /^I try to buy \'([^\']*)\' in bonus shop$/
     */
    public function iTryToBuyInBonusShop($buy_item_name)
    {
        $this->bonus_points = $this->driver->findElement(WebDriverBy::cssSelector('.account-block .bonus-points'))->getText();
        $this->bonus_points = trim(str_replace(' BP', '', $this->bonus_points));

        $buy_item_name = trim(strtolower($buy_item_name));
        $ar_elts = $this->driver->findElements(WebDriverBy::cssSelector('.bonus-system-shop-list .item'));

        foreach ($ar_elts as $item) {
            $title = $item->findElement(WebDriverBy::cssSelector('.head-content .title-left'))->getText();
            $title = trim(strtolower($title));
            if (strpos($title, $buy_item_name) !== false) {

                // check item attribute
                $item_attr = $item->getAttribute('class');
                PHPUnit_Framework_Assert::assertContains('exceeded_balance', $item_attr);

                // check that user can't buy item
                $item->findElement(WebDriverBy::cssSelector('.buttons-area div[onclick*="start_buy_product"]'))->click();
                sleep(2);
                Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('#modal_content .product-detail'));
                Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('#modal_content #custom_alert_btn'));
                Support_Registry::singleton()->elementNotPresent(WebDriverBy::cssSelector('#modal_content .popup-success-msg.alert-msg'));

                break;
            }

        }
    }

    /**
     * @Given /^I buy bonus card$/
     */
    public function iBuyBonusCard()
    {
        // go to bonus shop page
        $this->iGoToBonusSystemPage();
        $this->iGoToBonusShopPage();
        $this->iBuyInBonusShop('Card 10 EUR');
    }

    /**
     * @When /^I use money for create ticket$/
     */
    public function iUseMoneyForCreateTicket()
    {
        Support_TicketHelper::selectBetToGame('each');
        Support_TicketHelper::setUseRealMoney();
        Support_TicketHelper::setStake('10');
        Support_TicketHelper::doClickToCreateTicket();
    }

    /**
     * @Then /^I do not get bonus points on card$/
     */
    public function iDoNotGetBonusPointsOnCard()
    {
        $this->iGoToBonusSystemPage();

        $ticketCount = $this->driver->findElement(WebDriverBy::cssSelector('.public-search-table .t-add-tickets'))->getText();
        $bpAmount = $this->driver->findElement(WebDriverBy::cssSelector('.public-search-table .t-current-points'))->getText();
        $bpNeed = $this->driver->findElement(WebDriverBy::cssSelector('.public-search-table .t-points-need'))->getText();

        PHPUnit_Framework_Assert::assertEquals('0', $ticketCount);
        PHPUnit_Framework_Assert::assertEquals('0', $bpAmount);
        PHPUnit_Framework_Assert::assertEquals('100', $bpNeed);
    }

    /**
     * @Given /^If i use bonus card to create ticket$/
     */
    public function ifIUseBonusCardToCreateTicket()
    {
        Support_TicketHelper::selectBetToGame('each');
        Support_TicketHelper::setUseBonusCard();
        Support_TicketHelper::setStake('10');
        Support_TicketHelper::doClickToCreateTicket();
    }

    /**
     * @Then /^I get bonus point to card$/
     */
    public function iGetBonusPointToCard()
    {
        $this->iGoToBonusSystemPage();

        $ticketCount = $this->driver->findElement(WebDriverBy::cssSelector('.public-search-table .t-add-tickets'))->getText();
        $bpAmount = $this->driver->findElement(WebDriverBy::cssSelector('.public-search-table .t-current-points'))->getText();
        $bpNeed = $this->driver->findElement(WebDriverBy::cssSelector('.public-search-table .t-points-need'))->getText();

        PHPUnit_Framework_Assert::assertEquals('1', $ticketCount);
        PHPUnit_Framework_Assert::assertEquals('10', $bpAmount);
        PHPUnit_Framework_Assert::assertEquals('100', $bpNeed);
    }

    /**
     * @When /^I create "([^"]*)" tickets$/
     */
    public function iCreateTicketsAndTicket($amount)
    {
        $arGames = array_keys(Support_Registry::singleton()->arGames[0]->getArGames());

        for ($i = 0; $i < $amount; $i++) {
            Support_GoPage::game();
//            Support_TicketHelper::selectBetToGame('each');
            Support_TicketHelper::selectBetToGameById($arGames[$i]);
            Support_TicketHelper::setUseBonusCard();
            Support_TicketHelper::setStake('1');
            Support_TicketHelper::doClickToCreateTicket();
//            Support_TicketHelper::getTicketNumber();
        }

    }

    /**
     * @Given /^each ticket win$/
     */
    public function eachTicketWin()
    {
        $arGames = array_keys(Support_Registry::singleton()->arGames[0]->getArGames());
        foreach ($arGames as $gid) {
            Support_AdminHelper::markGame($gid, 'won');
            Support_Helper::processTicketQueue();
        }
    }
}