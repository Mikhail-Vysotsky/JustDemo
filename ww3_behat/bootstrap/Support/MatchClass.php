<?php

/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 16.02.15
 * Time: 14:53
 */
class Support_MatchClass
{
    protected $league_level;
    /**
     * @var int public ID to regular wir-wetten
     */
    protected $pid;
    protected $pc_region_category_id;
    protected $pc_sport_category_id;
    protected $m_pid;
    protected $sport_id;
    /**
     * @var int public ID to mobile wir-wetten
     */
    protected $url_regular;
    protected $url_mobile;
    /**
     * @var array
     */
    protected $arGames;


    public function createMixedMatch($amount_outright, $amount_regular, $league_level)
    {
        return $this->createGames(0, array("outright" => $amount_outright, "regular" => $amount_regular), $league_level);
    }

    /**
     * method to create games
     * @param $amount
     * @param $type
     * @param string $league_level league level
     * @param bool $first_quota
     * @param bool $f_running
     * @param int $max_quota
     * @param int $min_quota
     * @return bool
     * @throws Support_ConfigsException
     */
    public function createGames($amount, $type, $league_level, $first_quota = false, $f_running = false, $max_quota = 0, $min_quota = 0)
    {
        $regular_game_count = 0;
        $outright_game_count = 0;
        $special_games_count = 0;
        $twoWay_games_count = 0;
        $livebet_games_count = 0;

        if (is_array($type)) {
            foreach ($type as $_type => $_amount) {
                if ($_type === 'regular') $regular_game_count = $_amount;
                if ($_type === 'outright') $outright_game_count = $_amount;
                if ($_type === 'special') $special_games_count = $_amount;
                if ($_type === '2way') $twoWay_games_count = $_amount;
                if ($type === 'livebet') $livebet_games_count = $amount;
            }
        } else {
            if ($type === 'regular') $regular_game_count = $amount;
            if ($type === 'outright') $outright_game_count = $amount;
            if ($type === 'special') $special_games_count = $amount;
            if ($type === '2way') $twoWay_games_count = $amount;
            if ($type === 'livebet') $livebet_games_count = $amount;
        }

        // prepare URL to create game
        $base_url = Support_Configs::get()->MANAGE_URL . 'index.php?'
            . 'ac=selenium-test/match_generator'
            . '&do=create'
            . '&regular_games=' . $regular_game_count
            . '&outright_games=' . $outright_game_count
            . '&special_games=' . $special_games_count
            . '&twoWay_games=' . $twoWay_games_count
            . '&league_level=' . $league_level
            . '&max_quota='.$max_quota
            . '&min_quota='.$min_quota;

        if ($livebet_games_count > 0) {
            if ($f_running)
                $f_running = 1;
            else
                $f_running = 0;
            $base_url = $base_url.'&livebet_games='.$livebet_games_count.'&f_running='.$f_running;
        }
        
        if ($first_quota) {
            $base_url = $base_url . '&first_quota=' . $first_quota;
        }

        $repeat = 20;
        $cnt = 0;

        while ($cnt <= $repeat) {
            $cnt++;

            try {
                $result = Support_Helper::doCurlRequest($base_url, 'json');

                $this->pid = $result->pid;
                $this->m_pid = $this->pid - 1;

                $this->pc_region_category_id = $result->pc_category->old_attributes->id;
                $this->pc_sport_category_id = $result->pc_sport->old_attributes->id;

                $this->league_level = $result->league_level;
                $this->sport_id = $result->sport_id;
                $this->arGames = (array)$result->arGames;

                $this->url_regular = Support_Configs::get()->BASE_URL . 'index.php?ac=v3/sports/index#in=' . $this->pid;
                $this->url_mobile = Support_Configs::get()->MOBILE_BASE_URL . '#/index.php?ac=mobile/tournament/index&pid='.$this->m_pid;

                return true;

            } catch (Exception $e) {
                sleep(3);
                continue;
            }
        }
        return false;
    }


    public function createLivebetGames($amount, $type, $f_running, $league_level)
    {
        $this->createGames($amount, $type, $league_level, false, $f_running);
    }
    /**
     * hallow
     * @return string 200 if success
     */
    public function deleteAll()
    {
        return $this->deleteCategory();

    }

    /**
     * @return string 200 if success
     */
    public function deleteCategory()
    {

        $base_url = Support_Configs::get()->MANAGE_URL . 'index.php?ac=selenium-test/match_generator&do=delete_category'
            . '&public_category=' . $this->pid;
        $result = Support_Helper::doCurlRequest($base_url, 'info');
        return $result;
    }

    /**
     * @return mixed
     */
    public function getUrlMobile()
    {
        return $this->url_mobile;
    }

    /**
     * @return mixed
     */
    public function getUrlRegular()
    {
        return $this->url_regular;
    }

    /**
     * @return int
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @return int
     */
    public function getMPid()
    {
        return $this->m_pid;
    }

    /**
     * @return array
     */
    public function getArGames()
    {
        return $this->arGames;
    }

    /**
     * @param array $arGames
     */
    public function setArGames($arGames)
    {
        $this->arGames = $arGames;
    }

    /**
     * @return int
     */
    public function getCategoryRegionId()
    {
        return $this->pc_region_category_id;
    }

    /**
     * @return int
     */
    public function getCategorySportId()
    {
        return $this->pc_sport_category_id;
    }

    /**
     * @return int
     */
    public function getPublicCategoryId()
    {
        return $this->pid;
    }
}