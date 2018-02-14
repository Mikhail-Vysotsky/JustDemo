<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 01.02.16
 * Time: 18:11
 */
if (!RS_Config::get()->selenium_enable ) {
    die();
}

require_once dirname(__FILE__) .'/../../inc/shell.inc.php';

LB_Player_Service::get()->check_account_deposit_limits();