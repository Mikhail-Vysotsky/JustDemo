<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 18.08.14
 * Time: 12:19
 */

if (!RS_Config::get()->selenium_enable ) {
    die();
}

    // get random match
    $matches=LB_Match_Storage::get()->find_all(array(
        'where'=>LB_Match::get_published_sql_condition('t').'
            and t.f_outright = 0
            and t.f_has_public_category = 1
            and t.source_id not in(' . implode(',', array(LB_Source::MANUAL, LB_Source::SOCCER_ROULETTE)) . ')
            and t.f_h2h != 1',
        'limit'=>'7',
        'order'=>'random()',
    ));


$result = array();
foreach ($matches as $match) {

    $odds = $match->get_3_way_odds();
    if(!$odds) continue;
    if(!$odds->build_agg_f_visible()) continue;

    $outcomes = $odds->get_public_outcomes();
    if (count($outcomes)===0) continue;

    $outcome = $outcomes[array_rand($outcomes)];

    $result[] = array(
        'sport_page'    => $match->get_sport_public_category() ? $match->get_sport_public_category()->id : 0,
        'match_page'    => $match->get_tournament_public_category() ? $match->get_tournament_public_category()->id : 0,
        'match_id'      => $match->id,
        'odds_ext_id'   => urlencode($outcome->odd_ext_id),
        'outcome_index' => urlencode($outcome->index),
    );
}

echo "<test>
<sportpage>{$result[0]['sport_page']}</sportpage>
";


foreach ($result as $match) {
    echo "<testdata>
        <eventpage>{$match['match_page']}</eventpage>
        <ev_id>{$match['match_id']}</ev_id>
        <ext_id>{$match['odds_ext_id']}</ext_id>
        <outcome_index>{$match['outcome_index']}</outcome_index>
    </testdata>";

}
echo "
</test>";
