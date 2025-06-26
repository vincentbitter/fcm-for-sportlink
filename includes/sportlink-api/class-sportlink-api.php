<?php

if (! defined('ABSPATH')) {
    exit;
}

require_once('class-sportlink-team.php');
require_once('class-sportlink-player.php');

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
    public function get_teams(): array
    {
        return $this->get_json_array('teams', FCM_Sportlink_Team::class);
    }

    /**
     * Retrieves a list of all players from Sportlink.
     *
     * @return array List of FCM_Sportlink_Player objects.
     */
    public function get_players($teamcode): array
    {
        $players = $this->get_json_array('team-indeling?teamcode=' . $teamcode . '&lokaleteamcode=-1&toonlidfoto=ja', FCM_Sportlink_Player::class);
        foreach ($players as $player) {
            $player->teamcode = $teamcode;
        }
        return $players;
    }

    /**
     * Retrieves a JSON array from the Sportlink API and converts it into an array of objects.
     *
     * @param string $uri The URI to retrieve from the Sportlink API.
     * @param string $class The class name of the objects to create.
     * @return array Array of objects.
     */
    private function get_json_array($uri, $class)
    {
        $content = $this->get_content($uri);
        $data = json_decode($content, true);

        $result = array();
        foreach ($data as $item) {
            $obj = new $class();
            foreach ($item as $key => $value)
                if (property_exists($obj, $key))
                    $obj->{$key} = $value;
            $result[] = $obj;
        }
        return $result;
    }

    /**
     * Retrieves the content as string from the Sportlink API.
     *
     * @param string $uri The URI to retrieve from the Sportlink API.
     * @return string The content retrieved from the Sportlink API.
     */
    private function get_content($uri)
    {
        $url = self::$_base_url . $uri;
        $url .= (strstr($url, '?')) ? '&' : '?';
        $url .= 'client_id=' . $this->_client_id;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($curl);
        curl_close($curl);

        return $content;
    }
}
