<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 09.02.16
 * Time: 14:03
 *
 * @property string BASE_URL
 * @property string MANAGE_URL
 * @property string MOBILE_BASE_URL
 * 
 * @property string v2_manage_host
 * @property string v2_host
 * @property string v2_admin
 * @property string v2_password
 * @property string v2_secret_word

 * @property string MAIL_PREF
 * @property string SELENIUM_HOST
 * @property string SELENIUM_PORT
 * @property string UPLOAD_FILES_DIR
 * @property string BROWSER_DOWNLOAD_DIR
 * @property string PATH_TO_FIREFOX_PROFILE
 * @property string CREATE_CARD_PASSWORD
 * @property string admin_login
 * @property string admin_password
 * @property string mail_host
 * @property string mail_login
 * @property string mail_password
 * @property string mail_port
 * @property string skrill_email
 * @property string skrill_password
 * @property string paypal_email
 * @property string paypal_password
 * @property string visa_card
 * @property string mastercard_card
 * @property string paysafe_card
 * @property string game_manager
 * @property string game_manager_password
 * @property string ticket_manager
 * @property string ticket_manager_password
 * @property string risk_officer
 * @property string risk_officer_password
 * @property string financial_manager
 * @property string financial_manager_password
 */

class Support_Configs {
    /**
     * @var array
     */
    private $_params=array();
    /**
     * @var array
     */
    private static $_instances=array();

    /**
     * @param string $config_name
     * @throws Support_ConfigsException
     * @return Support_Configs
     */
    public static function get($config_name='instance')
    {
        if(!isset(self::$_instances[$config_name]))
        {
            self::$_instances[$config_name] = new Support_Configs();

            $main_conf_path = __DIR__ . '/configs/' . $config_name . '.php';
            if(!file_exists($main_conf_path))
                throw new Support_ConfigsException("File does not exist $main_conf_path");

            $params = include $main_conf_path;

            $usr_conf_path = __DIR__ . '/../../etc/' . $config_name . '.php';
            if(file_exists($usr_conf_path))
            {
                $usr_params = include $usr_conf_path;

                foreach($usr_params as $key=>$val)
                    $params[$key] = $val;
            }

            self::$_instances[$config_name]->_params = $params;
        }

        return self::$_instances[$config_name];
    }

    /**
     * @param $name
     * @return null
     */
    function __get($name)
    {
        if(array_key_exists($name,$this->_params))
        {
            return $this->_params[$name];
        }
        return null;
    }

}