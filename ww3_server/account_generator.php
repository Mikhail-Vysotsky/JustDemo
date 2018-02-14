<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 19.11.15
 * Time: 17:40
 */
if (!RS_Config::get()->selenium_enable ) {
    die();
}

$action = _gs('do');

if ($action === 'create') {
    $email = _gs('email');
    $balance = _gs('balance');
    $currency = _gs('currency');
    $bonus_points = _gs('bonus_points');


    if (isset($_GET['phone'])) {
        $f_player = Test_Helper::fake_player($balance, $currency, $email, true, _gs('phone'));
    } else {
        $phone = '007'.'912'.rand(1000000, 9999999);
        $f_player = Test_Helper::fake_player($balance, $currency, $email, true, $phone);
    }

    if (isset($_GET['bonus_points'])) {
        $f_player->bonus_points = $bonus_points;
    }
    $f_player->f_confirmed = 1;
    LB_Player_Storage::get()->save($f_player);

    $new_player = LB_Player_Storage::get()->find_by_pk($f_player->id);

//var_dump($f_player);
    if ($new_player)
        echo "OK";
    else
        echo "ERROR";
} elseif ($action === "getPhoneConfirmKey") {
    $email = _gs('email');
    $player = LB_Player_Storage::get()->find_by_email($email);

    $phone = is_null($player->change_phone) ? $player->phone : $player->change_phone;

    $phone_confirm_key = LB_Player_PhoneConfirmation_Storage::get()->find_by_pk(array($player->id, "$phone"));


    echo $phone_confirm_key->key;
}