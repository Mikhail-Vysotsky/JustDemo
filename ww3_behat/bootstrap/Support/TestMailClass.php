<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 01.12.15
 * Time: 14:52
 */

class Support_TestMailClass
{
    private $msgCount;
    /**
     * @var Zend_Mail_Storage_Imap
     */
    private $mail;
    private $newMsgId;
    private $newMsgNumber;
    /**
     * @var Zend_Mail_Message
     */
    private $lastNewMessage;

    public function __construct()
    {
        $this->__init();
    }

    /**
     * @throws Support_ConfigsException
     * @throws Support_TestMailClassException
     */
    private function __init()
    {
        $arParams = [

            'host' => Support_Configs::get()->mail_host,
            'user' => Support_Configs::get()->mail_login,
            'password' => Support_Configs::get()->mail_password,
            'url' => Support_Configs::get()->mail_port,
            'ssl' => 'SSL',
        ];
        try {
            $this->mail = new Zend_Mail_Storage_Imap($arParams);
            $this->msgCount = $this->mail->countMessages();
        } catch (Exception $e) {
            throw new Support_TestMailClassException("can't connect to email server: " . $e->getMessage());
        }
    }

    /**
     * @return null|string
     * @throws Zend_Mail_Storage_Exception
     */
    public function checkEmail()
    {
        $lastMessageNumber = $this->mail->countMessages();

        //get last message
        $msg = $this->mail->getMessage($lastMessageNumber);

        return $this->getContent($msg);
    }

    /**
     * @param $_to
     * @param bool $f_ignore_check_seen
     * @return bool|string
     * @throws Exception
     * @throws Zend_Mail_Exception
     * @throws Zend_Mail_Storage_Exception
     */
    public function waitForNewMessage($_to, $f_ignore_check_seen = false)
    {

        // wait for new mail
        if ($this->isNewMessageExist($_to, $f_ignore_check_seen)) {
            // mark message as /Seen
            $return = base64_decode($this->lastNewMessage->getContent());
            $this->mail->removeMessage($this->newMsgNumber);
            return $return;
        }
        return false;
    }

    /**
     * @param $_to
     * @param $f_ignore_check_seen
     * @return bool
     * @throws Exception
     * @throws Support_TestMailClassException
     * @throws Zend_Mail_Storage_Exception
     */
    private function isNewMessageExist($_to, $f_ignore_check_seen)
    {
        $this->__init();

        $count = $this->mail->countMessages();
        $start_from = max(1, $count - 10);
        $minutes_amount = 3;

        for ($sec = 0; $sec < 2 * 60 * $minutes_amount; $sec++) {

            if ($count >= $start_from) {
                for ($i = $count; $i >= $start_from; $i--) {
                    $msg_number = $i;
                    $msg = $this->mail->getMessage($msg_number);

                    $actual_f_seen = $msg->hasFlag(Zend_Mail_Storage::FLAG_SEEN);
                    $actual_email_to = $msg->getHeaders()['to'];

                    if (($f_ignore_check_seen || !$actual_f_seen) && $actual_email_to === $_to) {
                        $this->newMsgId = $this->mail->getUniqueId($msg_number);
                        $this->newMsgNumber = $msg_number;
                        $this->lastNewMessage = $msg;

                        return true;
                    }

                }
            }

            usleep(500000);

            $start_from = $count + 1;
            $count = $this->mail->countMessages();
            $this->mail->noop();
        }

        throw new Exception("Timeout: no new messages in mail box");

    }

    /**
     * @param Zend_Mail_Message $_msg
     * @return null|string
     * @throws Zend_Mail_Exception
     */
    public function getContent(Zend_Mail_Message $_msg)
    {
        $content = null;

        $arFlags = $_msg->getFlags();

        // check that is realy last message (seen is not set)
        if (count($arFlags) == 0) {
            if (isset($_msg->getHeaders()['content-transfer-encoding'])) {

                if (strtolower($_msg->getHeaders()['content-transfer-encoding']) === "base64")
                    $content = mb_convert_encoding(base64_decode($_msg->getContent()), 'UTF-8', 'KOI8-R') . "\n";
                else
                    $content = $_msg->getContent();

            }
        }
        return $content;
    }

    /**
     * @param $string
     * @return bool|string
     */
    public static function getLinkFromString($string) {
        preg_match('/(http|https:\/\/)(.*)/', $string, $link);
        if (empty($link[0])) {
            return false; // URLs not found
        } else {
            return trim($link[0]);
        }
    }

    /**
     * @param $content
     * @return bool|string
     */
    public static function getPasswordFromEmailContent($content)
    {
        $arrStr = explode("\n", $content);

        foreach ($arrStr as $line) {
            if (strpos($line, "Password:")!==false) { // for eng and de languages
                $new_password = trim(str_replace("Password: ", '', $line));
                return $new_password;
            } elseif (strpos($line, "Passwort") !== false) {
                $new_password = trim(str_replace("Passwort: ", '', $line));
                return $new_password;
            }
        }
        return false;
    }
}