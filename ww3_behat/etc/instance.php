<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 09.02.16
 * Time: 15:05
 */

return array(
    // basic conf
    'BASE_URL' => 'http://ww3.111.rsdemo.ru/',
    'MANAGE_URL' => 'http://manage.ww3.111.rsdemo.ru/',
    'MOBILE_BASE_URL' => 'http://m.ww3.111.rsdemo.ru/',
    'MAIL_PREF' => "behat_{suff}@vysotsky.rssystems.ru",
    'PATH_TO_FIREFOX_PROFILE' => __DIR__.'/../test_data/firefox_profile/ff_profile.zip',     #path to zip archive of ff_profile

    'v2_manage_host' => 'http://manage.ww2034.111.rsdemo.ru/',
    'v2_host' => 'http://manage.ww2034.111.rsdemo.ru/',
    'v2_admin' => 'admin',
    'v2_password' => '123',
    'v2_secret_word' => 'wirwetten',

    'SELENIUM_HOST' => '10.0.0.166',
    'UPLOAD_FILES_DIR' => '/home/selenium/share/to_upload/',

    // test accounts conf
    'admin_login' => 'admin',
    'admin_password'=> '123',
    'game_manager' => 'game_manager',
    'game_manager_password' => '123',
    'ticket_manager' => 'ticket_manager',
    'ticket_manager_password' => '123',
    'risk_officer' => 'risk_officer',
    'risk_officer_password' => '123',
    'financial_manager' => 'financial_manager',
    'financial_manager_password' => '123',

    // mail conf
    'mail_host' => '',
    'mail_login' => '',
    'mail_password' => '',
    'mail_port' => ''
);