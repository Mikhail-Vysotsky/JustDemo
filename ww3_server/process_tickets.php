<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 10.12.15
 * Time: 11:22
 */
if (!RS_Config::get()->selenium_enable ) {
    die();
}


require_once dirname(__FILE__) . '/../../inc/shell.inc.php';

LB_Queue_Service::get()->execute_queue('LB_Queue_Bets');
LB_Queue_Service::get()->execute_queue('LB_Queue_ClearTicket');

echo "OK";