<?php

if (! defined('ABSPATH')) {
    exit;
}

require_once('class-sportlink-error.php');
require_once('class-sportlink-exception.php');
require_once('class-sportlink-team.php');
require_once('class-sportlink-player.php');

class FCMSL_Sportlink_API
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
     * @return array List of FCMSL_Team objects.
     */
    public function get_teams(): array
    {
        return $this->get_json_array('teams', array(), FCMSL_Team::class);
    }

    /**
     * Retrieves a list of all players from Sportlink.
     *
     * @return array List of FCMSL_Player objects.
     */
    public function get_players($teamcode): array
    {
        $players = $this->get_json_array(
            'team-indeling',
            array(
                'teamcode' => $teamcode,
                'lokaleteamcode' => -1,
                'toonlidfoto' => 'ja'
            ),
            FCMSL_Player::class
        );
        foreach ($players as $player) {
            $player->teamcode = $teamcode;
        }
        return $players;
    }

    /**
     * Retrieves a JSON array from the Sportlink API and converts it into an array of objects.
     *
     * @param string $slug The Sportlink API endpoint to retrieve data from.
     * @param array $args The arguments to pass to the Sportlink API. The client_id will be added automatically.
     * @param string $class The class name of the objects to create.
     * @return array Array of objects.
     */
    private function get_json_array($slug, $args, $class)
    {
        $content = $this->get_content($slug, $args);
        return $this->decode_json_to_array($content, $class);
    }

    /**
     * Decode a json string into an array of objects.
     * 
     * @param string $content The json string to decode.
     * @param string $class The class name of the objects to create.
     * @return array Array of objects.
     */
    private function decode_json_to_array($content, $class)
    {
        $data = json_decode($content, true);

        $result = array();
        foreach ($data as $item) {
            $result[] = $this->map_to_class_instance($item, $class);
        }
        return $result;
    }

    /**
     * Maps an array to a class instance.
     * 
     * @param array $data The array to map.
     * @param string $class The class name.
     * @return object The class instance.
     */
    private function map_to_class_instance($data, $class)
    {
        $obj = new $class();
        foreach ($data as $key => $value)
            if (property_exists($obj, $key))
                $obj->{$key} = sanitize_text_field($value);
        return $obj;
    }

    /**
     * Retrieves the content as string from the Sportlink API.
     *
     * @param string $slug The Sportlink API endpoint to retrieve data from.
     * @param array $args The arguments to pass to the Sportlink API. The client_id will be added automatically.
     * @return string The content retrieved from the Sportlink API.
     */
    private function get_content($slug, $args)
    {
        $args['client_id'] = $this->_client_id;
        $query_params = '?' . http_build_query($args);
        $response = wp_remote_get(self::$_base_url . $slug . $query_params);
        $response_code = wp_remote_retrieve_response_code($response);
        if ((!is_wp_error($response)) && (200 === $response_code)) {
            return $response['body'];
        } else {
            $json = json_decode($response['body'], true);
            $error = $this->map_to_class_instance($json['error'], FCMSL_Sportlink_Error::class);
            $error->http_response_code = $response_code;
            throw new FCMSL_Sportlink_Exception(esc_html__('Failed to retrieve data from Sportlink API.', 'fcm-sportlink'), $error);
        }
    }
}
