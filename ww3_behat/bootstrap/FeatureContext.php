<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 19.11.15
 * Time: 16:25
 */

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\AfterFeatureScope;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;

class FeatureContext implements Context, SnippetAcceptingContext
{
    /** @AfterScenario */
    public static function teardown()
    {
        Support_Registry::singleton()->clean_instance();
    }

    /**
     * @BeforeSuite
     */
    public static function BeforeSuite()
    {
        if(Support_Registry::singleton()->driver===null)
            Support_Registry::singleton()->create_browser();

        Support_Registry::singleton()->clean_games();
    }

    /**
     * @AfterSuite
     */
    public static function close_browser()
    {
        try
        {
            Support_Registry::singleton()->driver->close();
            Support_Registry::singleton()->driver = null;
        }
        catch(Exception $e)
        {

        }
    }

    /**
     * @BeforeSuite
     */
    public static function clean_db()
    {
        $url = Support_Configs::get()->MANAGE_URL . 'index.php?ac=selenium-test/clean_db';
        Support_Helper::doCurlRequest($url, 'info');
    }



    /**
     * @BeforeFeature
     */
    public static function setupTop7Feature(BeforeFeatureScope $scope) {
        $filename = pathinfo($scope->getFeature()->getFile())['filename'];
        if ($filename === 'top7') {
            $games = new Support_MatchClass();
            $games->createGames('10', 'regular', '*');
            Support_Registry::singleton()->top7_games[] = $games;

            Support_AdminHelper::goToBackoffice();
            Support_AdminHelper::goToTop7Page();
            Support_AdminHelper::disableAllTop7Lists();
            Support_AdminHelper::createTop7List();

            Support_AdminHelper::logoutFromBackoffice();
        }
    }

    /**
     * @AfterFeature
     */
    public static function teardownTop7Feature(AfterFeatureScope $scope) {
        $filename = pathinfo($scope->getFeature()->getFile())['filename'];
        if ($filename === 'top7' && Support_Registry::singleton()) {
            Support_AdminHelper::deleteTop7List(Support_Registry::singleton()->top7_list);
            Support_Registry::singleton()->clean_top7_games();
        } else {
            Support_Registry::singleton()->top7_games = false;
        }
    }
}