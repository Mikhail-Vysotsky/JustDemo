<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 06.11.14
 * Time: 16:32
 */


if (!RS_Config::get()->selenium_enable ) {
    die();
}


require_once dirname(__FILE__) . '/../../inc/loader.inc.php';

$service = new Ticket_AvailableInsurancePayment_Service();
$service->update();

echo "ok";