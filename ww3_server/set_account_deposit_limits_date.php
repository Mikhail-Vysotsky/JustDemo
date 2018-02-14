<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 01.02.16
 * Time: 18:12
 */

if (!RS_Config::get()->selenium_enable ) {
    die();
}

// get player id by email
$email = _gs('email');


$player_id = LB_Player_Storage::get()->find_by_email($email)->id;

//$player_limits = LB_Player_DepositLimits_Storage::get()->find_all_by_attributes(array('player_id' => $player_id), array('order' => 'id desc'));
$player_limits = LB_Player_DepositLimits_Storage::get()->find(array('where' => 'player_id=?', 'order' => 'id desc', 'limit' => '1'), $player_id);

$player_limits->tmstmp_apply = date('Y-m-d H:i:s', strtotime( $player_limits->tmstmp_apply.' -1 days'));
    LB_Player_DepositLimits_Storage::get()->save($player_limits, array('tmstmp_apply'));


// call check account deposit
require_once dirname(__FILE__) .'/../../inc/shell.inc.php';

LB_Player_Service::get()->check_account_deposit_limits();

echo 'OK';