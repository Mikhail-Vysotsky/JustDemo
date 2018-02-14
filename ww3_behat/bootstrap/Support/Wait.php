<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 19.11.15
 * Time: 16:31
 */

use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class Support_Wait
{
    /**
     * @param $css_selector
     * @param $old_text
     * @param int $timeout
     * @return bool|string
     * @throws Support_WWTimeoutException
     */
    public static function forTextUpdated($css_selector, $old_text, $timeout = 60)
    {
        $session = Support_Registry::singleton();
        $selenium = $session->driver;

        for ($second = 0; ; $second++) {
            if ($second == $timeout) {
                throw new Support_WWTimeoutException('Timeout: text in element "' . $css_selector . '" does not update');
            }
            try {

                $new_text = $selenium->findElement(WebDriverBy::cssSelector($css_selector))->getText();
//                if ($old_text !== $new_text) return true;
                if ($old_text !== $new_text) return $new_text;
            } catch (Exception $e) {
            }
            sleep(1);
        }
        return false;
    }

    /**
     * @param WebDriverBy $element
     * @param $expected_text
     * @param int $timeout
     * @return bool|string
     * @throws Support_WWTimeoutException
     */
    public static function forTextInElement(WebDriverBy $element, $expected_text, $timeout = 60)
    {
        $session = Support_Registry::singleton();
        $selenium = $session->driver;

        for ($second = 0; ; $second++) {
            if ($second == $timeout) {
                throw new Support_WWTimeoutException('Timeout: text in element does not update');
            }
            try {
                $new_text = $selenium->findElement($element)->getText();

                if ($new_text === $expected_text) return $new_text;
            } catch (Exception $e) {
            }
            sleep(1);
        }
        return false;
    }

    /**
     * @param RemoteWebElement $element
     * @param $old_text
     * @param int $timeout
     * @return bool
     * @throws Support_WWTimeoutException
     */
    public static function forTextUpdatedInElement(RemoteWebElement $element,  $old_text, $timeout = 60)
    {
        for ($second = 0; ; $second++) {
            sleep(1);
            if ($second == $timeout) {
                throw new Support_WWTimeoutException('Timeout: text in element "' . $element->getAttribute('class') . '" does not update');
            }
            try {

                $new_text = $element->getText();
                if ($old_text !== $new_text) return true;
            } catch (Exception $e) {
            }
        }
        return false;
    }

    /**
     * @param $selector
     * @param $old_count
     * @param int $timeout
     * @return bool|int
     * @throws Support_WWTimeoutException
     */
    public static function forCssCountUpdate($selector, $old_count, $timeout = 60)
    {
        $session = Support_Registry::singleton();
        $selenium = $session->driver;

        for ($second = 0; ; $second++) {
            if ($second == $timeout) {
                throw new Support_WWTimeoutException('Timeout: css count does not updated');
            }
            try {

                $new_count = count($selenium->findElements($selector));
                if ($old_count !== $new_count) return $new_count;
            } catch (Exception $e) {
            }
            sleep(1);
        }
        return false;
    }

    /**
     * @param WebDriverBy $selector
     * @param $expected_count
     * @param int $timeout
     * @return bool|int
     * @throws Support_WWTimeoutException
     */
    public static function forCssCountIs(WebDriverBy $selector, $expected_count, $timeout = 60)
    {
        $session = Support_Registry::singleton();
        $selenium = $session->driver;

        for ($second = 0; ; $second++) {
            if ($second == $timeout) {
                throw new Support_WWTimeoutException('Timeout: css count does not updated');
            }
            try {

                $new_count = count($selenium->findElements($selector));
                if ($expected_count === $new_count) return $new_count;
            } catch (Exception $e) {
            }
            sleep(1);
        }
        return false;
    }

    /**
     * @param WebDriverBy $cssSelector
     * @param $attribute_name
     * @param $old_value
     * @param int $timeout
     * @return bool|null|string
     * @throws Exception
     * @throws Support_WWTimeoutException
     */
    public static function forAttributeUpdated(WebDriverBy $cssSelector, $attribute_name, $old_value, $timeout = 60)
    {
        $session = Support_Registry::singleton();
        $selenium = $session->driver;

        for ($second = 0; ; $second++) {
            if (Support_Registry::singleton()->elementNotPresent($cssSelector, false)) {
                continue;
            }

            if ($second == $timeout) {
                throw new Support_WWTimeoutException('Timeout: css count does not updated');
            }
            try {
                $element = $selenium->findElement($cssSelector);
                $new_value = $element->getAttribute($attribute_name);

                if ($old_value !== $new_value) return $new_value;
            } catch (Exception $e) {
            }
            sleep(1);
        }
        return false;
    }

    /**
     * @param $timestamp_css_selector
     * @param $timestamp_attribute
     * @param $old_timestamp
     * @param int $wait_timeout
     * @return bool|null|string
     * @throws Exception
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function forAdminPageLoaded($timestamp_css_selector, $timestamp_attribute, $old_timestamp, $wait_timeout = 60)
    {
        $driver = Support_Registry::singleton()->driver;
        $new_timestamp = false;

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector($timestamp_css_selector)));

        for ($i = 0;;$i++) {
            if ($i > 60) throw new Exception('No timestamp updated');

            $new_timestamp = $driver->findElement(WebDriverBy::cssSelector($timestamp_css_selector))->getAttribute($timestamp_attribute);

            if (strlen($new_timestamp) > 1 && $new_timestamp !== $old_timestamp ) return $new_timestamp;

            sleep(1);
        }
        return $new_timestamp;
    }

    /**
     * @param $outDir
     * @param $count
     * @param string $error_message
     * @return bool
     * @throws Exception
     */
    public static function waitForAnyNewFile($outDir, $count, $error_message = "timeout: can't find new excel document" )
    {
        // wait for new file available
        $old_ar_files = array();
        for ($sec = 0; ; $sec++) {
            if ($sec >= 120) throw new Exception($error_message);

            $ar_files = array();
            foreach (scandir($outDir) as $s_file) {
                if (is_file($outDir.$s_file) && pathinfo($outDir.$s_file)['extension'] !== 'part' && pathinfo($outDir.$s_file)['extension'] !== 'xlsx#') {
                    $ar_files[] = $s_file;
                }
            }

            $new_f_count = count($ar_files);

            if ($new_f_count > $count) {
                $arMerged = array_merge($old_ar_files, $ar_files);
                return $arMerged[0];
            }

            sleep(1);
            $old_ar_files = $ar_files;
        }
        return false;
    }

    /**
     * @param bool $page_timestamp
     * @param WebDriverBy $cssSelector
     * @param string $attribute_name
     * @return bool|null|string
     * @throws Exception
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function forPageTimestampUpdated($page_timestamp = false, WebDriverBy $cssSelector = null, $attribute_name = 'data-page-get')
    {
        $driver = Support_Registry::singleton()->driver;
        $new_timestamp = false;

        if (!$cssSelector) {
            $cssSelector = WebDriverBy::cssSelector('.t-page-gen-timestamp');
        }

        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated($cssSelector));

        if (!$page_timestamp) {
            $page_timestamp = $driver->findElement($cssSelector)->getAttribute($attribute_name);
        }


        for ($i = 0;;$i++) {
            if ($i > 60) throw new Exception('No timestamp updated');

            $new_timestamp = $driver->findElement($cssSelector)->getAttribute($attribute_name);

            if (strlen($new_timestamp) > 1 && $new_timestamp !== $page_timestamp ) return $new_timestamp;

            sleep(1);
        }

        Support_Registry::singleton()->page_timestamp = $new_timestamp;
        return $new_timestamp;
    }
}