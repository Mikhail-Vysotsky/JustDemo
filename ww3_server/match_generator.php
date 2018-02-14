<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 24.02.15
 * Time: 17:02
 */

if (!RS_Config::get()->selenium_enable ) {
    echo "die";
    die();
}

// generate need games as 1 by default
$league_level = _gs('league_level'); //(strlen(_gs('league_level') > 0)) ? _gs('league_level') : '*';

//$regular_games = (strlen(_gs('regular_games') > 0)) ? _gs('regular_games') : 1;
//$outright_games = (strlen(_gs('outright_games') > 0)) ? _gs('outright_games') : 1;
$regular_games = _gs('regular_games');
$outright_games = _gs('outright_games');
$special_games = _gs('special_games');
$twoWay_games = _gs('twoWay_games');
$livebet_games = _gs('livebet_games');
$f_running = _gi('f_running');
$min_quota = _gi('min_quota');
$max_quota = _gi('max_quota');


// set default quota value
if ($max_quota == 0) $max_quota = 22;
if ($min_quota == 0) $min_quota = 11;

$sport_id = '';

$arMatch = array();
$arGames = array();
$arResult = array();

$arExistLeagueLevels = array(
    '*' => 1,
    '1st' => 5,
    '2nd' => 2,
    '3rd' => 3,
    '4th' => 4,
);

function rand_quota($max = 22, $min = 11) {
    return rand($min, $max)/10;
}

//------------------------------------------------------------------------
// CREATE TEST DATA
//------------------------------------------------------------------------
if (_gs('do')==='create') {
    // create outrights match
    for ($_or=0; $_or < $outright_games; $_or++) {
        $mc = Test_Helper::create_fake_outright(array('status'=>'not_started'));
        $arMatch[] = $mc;
        $arGames[$mc->id] = 'r'.$mc->id;
    }

    // create regular match
    for ($_rm=0; $_rm < $regular_games; $_rm++) {
        $first_quota = _gs('first_quota');

        if (strlen($first_quota) >= 1) {
            $arQuotes = array('1' => $first_quota, 'X' => rand_quota($max_quota, $min_quota), '2' => rand_quota($max_quota, $min_quota));
        } else {
            $arQuotes = Test_Helper::generateQuotes($max_quota, $min_quota);
        }

        $mc = Test_Helper::create_fake_match(array('restriction_id'=> $arExistLeagueLevels[$league_level]), $arQuotes);

        $livescore=Test_Helper::create_fake_livescore([
            "date_match" => date("Y-m-d H:i:s",time()+60*60),
            "home" => "home team ".$mc->id,
            "away" => "away team ".$mc->id,
        ], $mc);

        $arMatch[] = $mc;
        $arGames[$mc->id] = 'r'.$mc->id;
    }

    for ($_lb = 0; $_lb < $livebet_games; $_lb++) {
        $props['ext_sport_id']  = 1;
        $props['f_has_livebet']  = 1;

        $mc = Test_Helper::create_active_livebet_match($f_running, ["sport_id"=>LB_Sport::soccer_id(),], Test_Helper::generateQuotes(35, $min_quota));
        $livescore=Test_Helper::create_fake_livescore([
            "home" => "home team ".$mc->id,
            "away" => "away team ".$mc->id,
        ], $mc);

        $arGames[$mc->id] = 'r'.$mc->id;
    }

    // create games with special bets
    for ($_sg = 0; $_sg < $special_games; $_sg++) {
        $arOutcomes = array();


        for ($_ou = 0; $_ou < 10; $_ou++) {
            $spec_1 = rand(11, 39)/10;
            $spec_2 = rand(11, 39)/10;
            $spec_3 = rand(11, 39)/10;
            $arOutcomes["$_sg/$_ou"] = array("spec-1  $_sg|$_ou" => $spec_1, "spec-2  $_sg|$_ou" => $spec_2, /*"spec-3  $_sg|$_ou" => $spec_3*/);
        }

        $mc = Test_Helper::create_fake_match(array('restriction_id'=> $arExistLeagueLevels[$league_level]), Test_Helper::generateQuotes($max_quota, $min_quota), false, $arOutcomes);
        $arMatch[] = $mc;
        $arGames[$mc->id] = 'r'.$mc->id;
    }

    // create 2way games
    for ($_tw = 0; $_tw < $twoWay_games; $_tw++) {
        $mc = Test_Helper::create_fake_match(array('restriction_id'=> $arExistLeagueLevels[$league_level]), Test_Helper::generate2wayQuotes($max_quota, $min_quota));
        $arMatch[] = $mc;
        $arGames[$mc->id] = 'r'.$mc->id;
    }


    // create public category
    $public_category = Test_Helper::create_fake_public_category_tournament();
    $public_category->league_level_id = $arExistLeagueLevels[$league_level];


    // add match to public category
    foreach ($arMatch as $match) {

        /**
         * @var $match LB_Match
         */
        $match->changed_public_category = $public_category;
        LB_Match_Storage::get()->save($match);

        if (!isset($sport_id)) $sport_id = $match->sport_id;
    }

    // prepare result
    $arResult['arGames'] = $arGames;
    $arResult['pid'] = $public_category->id;
    $arResult['pc_category'] = $public_category->parent;
    $arResult['pc_sport'] = $public_category->parent->parent;
    $arResult['league_level'] = $league_level;
    $arResult['sport_id'] = $sport_id;

    echo json_encode($arResult);
}

if (_gs('do')==='delete_ticket') {
    $ticket= _gs('ticket');

    Test_Helper::remove_ticket($ticket);
} else if (_gs('do')==='delete_match') {
    $match_to_delete = _gs('match');

    Test_Helper::remove_fake_match($m);
} else if (_gs('do')==='delete_category') {
    $pc = _gs('public_category');
    $pc = LB_PublicCategory_Storage::get()->find_by_pk($pc);
    $pc_category = $pc->parent;
    $pc_sport = $pc->parent->parent;

    LB_PublicCategory_Storage::get()->delete($pc);
    LB_PublicCategory_Storage::get()->delete($pc_category);
    LB_PublicCategory_Storage::get()->delete($pc_sport);

}


Helper::reset_public_cache_keys(true);
