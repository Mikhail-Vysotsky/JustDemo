<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 02.02.16
 * Time: 14:39
 */


if (!RS_Config::get()->selenium_enable ) {
    die();
}

// get player id by email
$email = _gs('email');
$move_back = _gs('move');

if ($move_back === 'day') {
    $move_back =  ' -1 days';
} elseif ($move_back === 'week') {
    $move_back = ' -1 weeks';
} elseif ($move_back === 'month') {
//    $move_back = ' -1 months';
    $move_back = ' -31 days';
}

$player_id = LB_Player_Storage::get()->find_by_email($email)->id;
$player_transactions = LB_Player_Transaction_Storage::get()->find_all_by_player($player_id);

foreach ($player_transactions as $transact) {

    $transact->tmstmp = date('Y-m-d H:i:s', strtotime( $transact->tmstmp . $move_back));
    LB_Player_Transaction_Storage::get()->update($transact, array('tmstmp'));
}

echo "OK";