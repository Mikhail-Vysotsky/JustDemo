<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 15.08.14
 * Time: 16:42
 *
 */
if (!RS_Config::get()->selenium_enable ) {
    die();
}
$cache_id = 'JMETER_AUTH_USERS';
MemcacheWrap::get()->delete($cache_id);
