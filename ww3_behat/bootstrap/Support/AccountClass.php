<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 20.11.15
 * Time: 11:27
 */
class Support_AccountClass {

    public $email = null;
    public $old_email;
    public $password = null;
    public $old_password = null;
    public $phone_confirm_key = null;


    public $first_name;
    public $last_name;
    public $birth_tmstmp;
    public $birth_place;
    public $nationality;
    public $occupation;
    public $job_post;
    public $country;
    public $phone;
    public $new_phone;
    public $city;
    public $street;
    public $zip;
    public $language;

    /**
     * @param bool $email
     * @param int $balance
     * @param string $currency
     * @param bool $phone
     * @return $this|bool
     * @throws Support_ConfigsException
     */
    public function createNewAccount($email = false, $balance = 100, $currency = "CHF", $phone = false, $bonus_points = false)
    {
        if (!$email) {
            $email = strtr(Support_Configs::get()->MAIL_PREF, array('{suff}' => time()));
        }
        // prepare URL to create game
        $base_url = Support_Configs::get()->MANAGE_URL . 'index.php?'
            . 'ac=selenium-test/account_generator'
            . '&do=create'
            . '&email=' . $email
            . '&balance=' . $balance
            . '&currency=' . $currency;

        if ($phone) {
            $base_url = $base_url.'&phone='.$phone;
            $this->phone = $phone;
        }

        if ($bonus_points) {
            $base_url = $base_url."&bonus_points=".$bonus_points;
        }

        $repeat = 10;
        $cnt = 0;

        while ($cnt <= $repeat) {
            $cnt++;

            try {
                Support_Helper::doCurlRequest($base_url);

                $this->email = $email;
                $this->password = '123';

                return $this;
            } catch (Exception $e) {
                sleep(3);
                continue;
            }

        }

        return false;
    }

    /**
     * @return bool
     */
    public function delete()    //todo: write function
    {
        return true;
    }

    /**
     * @return bool|mixed
     * @throws Support_ConfigsException
     */
    public function getConfirmPhoneKey()
    {
        // prepare URL to create game
        $base_url = Support_Configs::get()->MANAGE_URL . 'index.php?'
            . 'ac=selenium-test/account_generator'
            . '&do=getPhoneConfirmKey'
            . '&email=' . $this->email;

        $repeat = 10;
        $cnt = 0;

        while ($cnt <= $repeat) {
            $cnt++;

            try {
                $res = Support_Helper::doCurlRequest($base_url);
                $this->phone_confirm_key = $res;

                return $res;
            } catch (Exception $e) {
                sleep(3);
                continue;
            }
        }
    }
}