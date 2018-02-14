<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 15.08.14
 * Time: 16:42
 *
 * http://2029.ww.vysotsky.rssystems.ru/index.php?ac=selenium-test/test&mode=545803745ae911bb0509936152cee1d9
 */

if (!RS_Config::get()->selenium_enable ) {
    die();
}

$cache_id = 'JMETER_AUTH_USERS';
$used_users = MemcacheWrap::get()->load($cache_id);
if (!$used_users) {
    $used_users = array();
    $where_not_id = '';
} else {
    $where_not_id = 'and ID not in (' . implode(',', $used_users) . ')';
}
//var_dump($used_users);

$start_time = microtime(true);
$sql    = 'select * from _user where username like ? ' . $where_not_id . ' order by ID desc limit 1';
$bind   = array('10A%');
$stmp = RS_Db::get()->query($sql, $bind);
$end_time = microtime(true);

if ($user = $stmp->fetchObject('Auth_User')) {
    $username = $user->username;
    $pwd = $user->userpass;

    $used_users[] = $user->id;

    MemcacheWrap::get()->save($cache_id, $used_users);
}

echo "<testdata>
<username>$username</username>
<pwd>$pwd</pwd>
</testdata>
";
// login
$result = Context::get()->public_player_authorization($username, $pwd, false, false);

//MemcacheWrap::get()->delete($cache_id);
