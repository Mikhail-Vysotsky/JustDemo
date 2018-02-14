<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 06.04.16
 * Time: 17:08
 */
use Facebook\WebDriver\WebDriverBy;

class Support_Check {

    /**
     * @throws Exception
     */
    public static function livescorePageIsValid()
    {
        Support_Registry::singleton()->elementPresent(WebDriverBy::id('livescore_search'));
        Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.modSelector .container'));
        Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.modSelector .reset-close'));
        Support_Registry::singleton()->elementPresent(WebDriverBy::cssSelector('.modSelector .reset-clear'));
        Support_Registry::singleton()->elementPresent(WebDriverBy::id('statusSelectBoxItText'));
        Support_Registry::singleton()->elementPresent(WebDriverBy::id('statusSelectBoxItArrowContainer'));
        Support_Registry::singleton()->elementPresent(WebDriverBy::id('refresh_intervalSelectBoxItText'));
        Support_Registry::singleton()->elementPresent(WebDriverBy::id('refresh_intervalSelectBoxItArrowContainer'));
        Support_Registry::singleton()->elementPresent(WebDriverBy::id('f_group'));
        Support_Registry::singleton()->elementPresent(WebDriverBy::id('ticket_id'));
        Support_Registry::singleton()->elementPresent(WebDriverBy::id('t_item_all_score'));
        Support_Registry::singleton()->elementPresent(WebDriverBy::id('t_item_selected_score'));
    }
}