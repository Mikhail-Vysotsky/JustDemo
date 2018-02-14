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

$ticket = [];
$item = $ticket[0]->attributes->items;

$_id = $item['id'];
$pass = md5('asfhajsfh__'.$item['Pass']);

echo "<testdata>
<ticket>$_id</ticket>
<hashed>$pass</hashed>
</testdata>
";