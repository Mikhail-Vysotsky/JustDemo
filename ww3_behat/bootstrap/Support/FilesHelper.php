<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 22.03.16
 * Time: 12:17
 */
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class Support_FilesHelper {

    /**
     * @param $voucher_id
     * @return array
     * @throws Exception
     * @throws Support_ConfigsException
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws null
     */
    public static function getVouchersFromExcel($voucher_id)
    {
        sleep(1);
        $outDir = Support_Configs::get()->BROWSER_DOWNLOAD_DIR;
        $driver = Support_Registry::singleton()->driver;
        // wait timeout variables
        $current_time = microtime(true);
        $timeout = 120 * 1000;

        // store files in out directory
        $f_count = self::scanDir($outDir);

        // store current windows data
        $current_window = $driver->getWindowHandle();
        $curr_window_count = count($driver->getWindowHandles());

        $driver->findElement(WebDriverBy::cssSelector("#r".$voucher_id." a.t-excel-view"))->click();

        // wait for new window
        while ($current_time + $timeout > microtime(true)) {
            $arWnd = $driver->getWindowHandles();

            if (count($arWnd) > $curr_window_count)
                break;
        }

        // select popUp window
        $new_window = end($arWnd);
        $driver->switchTo()->window($new_window);

            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('password')));
            $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('button[type="button"]')));

            usleep(150000);

            $driver->findElement(WebDriverBy::id('password'))->sendKeys(Support_Configs::get()->CREATE_CARD_PASSWORD);
            usleep(150000);
            $driver->findElement(WebDriverBy::cssSelector('.ui-button.ui-widget.ui-state-default.ui-corner-all.ui-button-text-only'))->click();


        // wait for pop up close
        while ($current_time + $timeout > microtime(true)) {
            $arWnd = $driver->getWindowHandles();
            if (count($arWnd) === $curr_window_count)
                break;
        }
        $driver->switchTo()->window($current_window);

        // check that popUp is close
        PHPUnit_Framework_Assert::assertTrue($curr_window_count === count($arWnd));


        // wait for new file available
        Support_Wait::waitForAnyNewFile($outDir, count($f_count), "timeout: can't find new excel document");

        $_file = null;
        // found a new file
        foreach (self::scanDir($outDir) as $n_file) {
            if (!in_array($n_file, $f_count)) $_file = $n_file;
        }

        return self::readVoucherExcel($outDir.$_file);
    }

    /**
     * @param $s_dir
     * @return array
     */
    public static function scanDir($s_dir /*dir to scan*/)
    {
        $ar_files = array();
        foreach (scandir($s_dir) as $s_file) {
            if (is_file($s_dir.$s_file)) $ar_files[] = $s_file;
        }
        return $ar_files;
    }

    /**
     * @param $file
     * @return array
     * @throws Exception
     * @throws PHPExcel_Reader_Exception
     */
    private static function readVoucherExcel($file)
    {
        if (!file_exists($file)) throw new Exception("File $file does not exist");

        // init excel reader
        $excelReader = PHPExcel_IOFactory::createReaderForFile($file);
        $excelReader->setReadDataOnly();
        $excelReader->setLoadAllSheets();

        // add filter to reader
        $excelReader->setReadFilter(new VoucherReadFilter());
        $obExcel = $excelReader->load($file);

        // parse document to array
        $arDoc = $obExcel->getActiveSheet()->toArray();

        $arResult = array();

        // prepare result
        foreach ($arDoc as $row_voucher) {
            if (!is_null($row_voucher[1]) && !is_null($row_voucher[2])) {
//                $arResult[trim($row_voucher[1])] = trim($row_voucher[2]);
                $arResult[] = ['qr_code' => trim($row_voucher[0]), 'number' => trim($row_voucher[1]), 'nominal' => trim($row_voucher[2])];
            }

        }

        return $arResult;

    }
}

/**
 * Class VoucherReadFilter
 */
class VoucherReadFilter implements PHPExcel_Reader_IReadFilter {
    /**
     * @param String $column
     * @param string|integer $row
     * @param string $worksheetName
     * @return bool
     */
    public function readCell($column, $row, $worksheetName = '')
    {
        if ($row > 1 && $row <= 1000) {
            if (in_array($column, range('A', 'C'))) {
                return true;
            }
        }
        return false;
    }
}