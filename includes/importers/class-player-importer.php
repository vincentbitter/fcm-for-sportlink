<?php

if (! defined('ABSPATH')) {
    exit;
}

require_once('class-importer.php');

class FCM_Sportlink_Player_Importer extends FCM_Sportlink_Importer
{
    private $_team_id_by_code = array();
    private $_player_code_by_id = array();

    protected function get_post_type()
    {
        return 'fcm_player';
    }

    /**
     * Retrieve a list of all players from Sportlink for teams in Football Club Manager.
     * Technical staff and private profiles are excluded.
     * Players playing in multiple teams are only returned once, in the last team found.
     *
     * @return array List of FCM_Sportlink_Player objects.
     */
    protected function get_entities()
    {
        $teams = get_posts(array(
            'post_type' => 'fcm_team',
            'numberposts' => -1
        ));

        $entities = array();
        $this->_team_id_by_code = array();
        foreach ($teams as $team) {
            $teamcode = get_post_meta($team->ID, '_fcm_team_external_id', true);
            $this->_team_id_by_code[$teamcode] = $team->ID;

            if ($teamcode)
                $entities = array_merge($entities, $this->_api->get_players($teamcode));
        }

        $entities = array_filter($entities, function ($entity) {
            return $entity->relatiecode && $entity->rol == 'Teamspeler';
        });

        $unique_entities = array();
        foreach ($entities as $entity) {
            $unique_entities[$entity->relatiecode] = $entity;
        }

        return $unique_entities;
    }

    /**
     * Check if the given entity and post represent the same player.
     *
     * @param FCM_Sportlink_Player $entity The Sportlink Player entity.
     * @param WP_Post $post The post object representing the player.
     * @return bool True if the entity and post have the same relatiecode, false otherwise.
     */
    protected function is_same($entity, $post)
    {
        if (! isset($this->_player_code_by_id[$post->ID]))
            $this->_player_code_by_id[$post->ID] = get_post_meta($post->ID, '_fcm_player_external_id', true);
        $relatiecode = $this->_player_code_by_id[$post->ID];
        return $relatiecode && $relatiecode == $entity->relatiecode;
    }

    /**
     * Update a player
     *
     * @param WP_Post $post The post object representing the player.
     * @param FCM_Sportlink_Player $entity The Sportlink Player to update the post for
     * @return bool Whether the post was updated.
     */
    protected function handle_updated_post($post, $entity)
    {
        $dirty = false;
        if ($post->post_title != $entity->full_name()) {
            $post->post_title = $entity->full_name();
            $dirty = true;
        }
        if ($post->post_status != 'publish') {
            $post->post_status = 'publish';
            $dirty = true;
        }

        $meta_data = get_post_meta($post->ID);
        $dirty = $this->update_metadata_if_needed($post->ID, $meta_data, '_fcm_player_first_name', $entity->voornaam) || $dirty;
        $dirty = $this->update_metadata_if_needed($post->ID, $meta_data, '_fcm_player_last_name', $entity->last_name()) || $dirty;

        $team_id = $this->_team_id_by_code[$entity->teamcode];
        if ($team_id != $meta_data['_fcm_player_team'][0]) {
            update_post_meta($post->ID, '_fcm_player_team', $team_id);
            $dirty = true;
        }

        if ($dirty) {
            wp_update_post($post);
        }
        return $dirty;
    }

    private function update_metadata_if_needed($post_id, $meta_data, $key, $new_value)
    {
        if ((!isset($meta_data[$key]) && $new_value) || (isset($meta_data[$key]) && $meta_data[$key][0] != $new_value)) {
            update_post_meta($post_id, $key, $new_value);
            return true;
        }
        return false;
    }

    /**
     * Create a new player
     *
     * @param FCM_Sportlink_Player $entity The Sportlink Player to create a post for
     * @return int The ID of the newly created post
     */
    protected function handle_new_post($entity)
    {
        $post = array(
            'post_title' => $entity->full_name(),
            'post_type' => $this->get_post_type(),
            'post_status' => 'publish',
            'meta_input' => array(
                '_fcm_player_external_id' => $entity->relatiecode,
                '_fcm_player_first_name' => $entity->voornaam,
                '_fcm_player_last_name' => $entity->last_name(),
                '_fcm_player_team' => $this->_team_id_by_code[$entity->teamcode]
            )
        );
        return wp_insert_post($post);
    }

    /**
     * Remove a player
     *
     * @param WP_Post $post The post object representing the player.
     * @return int|false The post ID on success, false on failure.
     */
    protected function handle_obsolete_post($post)
    {
        $relatiecode = get_post_meta($post->ID, '_fcm_player_external_id', true);
        if ($relatiecode) {
            return wp_trash_post($post->ID, false);
        }
    }
}
