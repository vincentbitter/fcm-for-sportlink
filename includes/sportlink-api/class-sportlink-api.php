<?php

if (! defined('ABSPATH')) {
    exit;
}

require_once('class-sportlink-team.php');

class FCM_Sportlink_API
{
    private static $_base_url = 'https://data.sportlink.com/';
    private $_client_id = null;

    public function __construct($client_id)
    {
        $this->_client_id = $client_id;
    }

    /**
     * Retrieves a list of all teams from Sportlink.
     *
     * @return array List of FCM_Sportlink_Team objects.
     */
    public function get_teams() : array
    {
        return $this->get_json('teams', 'FCM_Sportlink_Team');
        // $response = $this->get_json('teams');
        // return array_map(function ($team) {
        //     return new FCM_Sportlink_Team($team->teamcode, $team->teamnaam);
        // }, $response);
    }

    private function get_json($uri, $class)
    {
        $url = self::$_base_url . $uri;
        $url .= (strstr($url, '?')) ? '&' : '?';
        $url .= 'client_id=' . $this->_client_id;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($curl);
        curl_close($curl);

        return json_decode($content);
    }
}
