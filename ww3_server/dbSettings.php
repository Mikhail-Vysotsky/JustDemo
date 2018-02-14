<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 01.04.16
 * Time: 17:53
 */


if (!RS_Config::get()->selenium_enable ) {
    die();
}

$param = _gs('param');
$value = _gs('value');
//DbSettings::set_param_value($param, $value, true);
DbSettings::set_param_value('f_allow_feedback_feature', 0, true); //disable
//DbSettings::set_param_value('f_allow_feedback_feature', 1, true); //enable (should be by default)

echo "OK";