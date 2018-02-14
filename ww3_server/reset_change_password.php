<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 02.12.14
 * Time: 17:10
 */

if (!RS_Config::get()->selenium_enable ) {
    die();
}


if (_gs('mode') === '545803745ae911bb0509936152cee1d9')
{
    // username: 40Aforced
    $username = _gs('username');


    // get user id
    if($obUser = Auth_User_Storage::get()->ifind_by_username($username))
    {
        // set default password
        if ($password = _gs('password')) {
            $obUser->newPass($password);
            Auth_User_Storage::get()->save($obUser, array('userPass'));
        }

        $obUser->force_to_change_password();

        echo 1;
        return;
    }
}

echo 0;