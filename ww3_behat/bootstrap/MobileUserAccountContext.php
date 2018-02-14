<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 04.02.16
 * Time: 12:34
 */
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;

class MobileUserAccountContext implements Context, SnippetAcceptingContext {
    private $driver;

    function __construct()
    {
        $this->driver = Support_Registry::singleton()->driver;
    }
}