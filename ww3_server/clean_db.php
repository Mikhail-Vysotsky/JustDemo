<?php

if (!RS_Config::get()->selenium_enable ) {
    echo "die";
    die();
}

Test_Helper::clean_db_data(true);
Test_Helper::create_requered_sports();

echo "OK\n";