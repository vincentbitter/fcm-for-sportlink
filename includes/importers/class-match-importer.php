<?php

if (! defined('ABSPATH')) {
    exit;
}

require_once('class-importer.php');
require_once(ABSPATH . "wp-admin" . '/includes/image.php');

class FCMSL_Match_Importer extends FCMSL_Importer
{
    private $_team_id_by_code = array();
    private $_match_code_by_id = array();
    private $_referee_id_by_name = array();

    protected function get_post_type()
    {
        return 'fcmanager_match';
    }

    /**
     * Retrieve a list of all scheduled matched from Sportlink.
     *
     * @return array List of FCMSL_Match objects.
     */
    protected function get_entities()
    {
        $teams = get_posts(array(
            'post_type' => 'fcmanager_team',
            'numberposts' => -1
        ));
        $this->_team_id_by_code = array();
        foreach ($teams as $team) {
            $teamcode = get_post_meta($team->ID, '_fcmanager_team_external_id', true);
            if ($teamcode)
                $this->_team_id_by_code[$teamcode] = $team->ID;
        }

        $referees = get_posts(array(
            'post_type' => 'fcmanager_referee',
            'numberposts' => -1
        ));
        $this->_referee_id_by_name = array();
        foreach ($referees as $referee) {
            $this->_referee_id_by_name[$referee->post_title] = $referee->ID;
        }

        return $this->_api->get_schedule();
    }

    /**
     * Check if the given entity and post represent the same match.
     *
     * @param FCMSL_Match $entity The Sportlink Match entity.
     * @param WP_Post $post The post object representing the match.
     * @return bool True if the entity and post have the same matchcode, false otherwise.
     */
    protected function is_same($entity, $post)
    {
        if (! isset($this->_match_code_by_id[$post->ID]))
            $this->_match_code_by_id[$post->ID] = get_post_meta($post->ID, '_fcmanager_match_external_id', true);
        $matchcode = $this->_match_code_by_id[$post->ID];

        return $matchcode && $matchcode == $entity->wedstrijdcode;
    }

    /**
     * Update a match
     *
     * @param WP_Post $post The post object representing the match.
     * @param FCMSL_Match $entity The Sportlink Match to update the post for
     * @return bool Whether the post was updated.
     */
    protected function handle_updated_post($post, $entity)
    {
        $dirty = false;
        if ($post->post_title != $entity->wedstrijd) {
            $post->post_title = $entity->wedstrijd;
            $dirty = true;
        }
        if ($post->post_status != 'publish') {
            $post->post_status = 'publish';
            $dirty = true;
        }

        if ($dirty) {
            wp_update_post($post);
        }

        $meta_data = get_post_meta($post->ID);
        $dirty = $this->update_metadata_if_needed($post->ID, $meta_data, '_fcmanager_match_date', $entity->date()) || $dirty;
        $dirty = $this->update_metadata_if_needed($post->ID, $meta_data, '_fcmanager_match_starttime', $entity->aanvangstijd) || $dirty;
        $dirty = $this->update_metadata_if_needed($post->ID, $meta_data, '_fcmanager_match_team', $this->_team_id_by_code[$entity->team()]) || $dirty;
        $dirty = $this->update_metadata_if_needed($post->ID, $meta_data, '_fcmanager_match_opponent', $entity->opponent()) || $dirty;
        $dirty = $this->update_metadata_if_needed($post->ID, $meta_data, '_fcmanager_match_away', $entity->isAway()) || $dirty;
        $dirty = $this->update_metadata_if_needed($post->ID, $meta_data, '_fcmanager_match_referee', $this->get_or_create_referee_id($entity->referee())) || $dirty;

        $team_id = $this->_team_id_by_code[$entity->team()];
        if ($team_id != $meta_data['_fcmanager_match_team'][0]) {
            update_post_meta($post->ID, '_fcmanager_match_team', $team_id);
            $dirty = true;
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
     * Create a new match
     *
     * @param FCMSL_Match $entity The Sportlink Match to create a post for
     * @return int The ID of the newly created post
     */
    protected function handle_new_post($entity)
    {
        $referee_id = $this->get_or_create_referee_id($entity->referee());

        $post = array(
            'post_title' => $entity->wedstrijd,
            'post_type' => $this->get_post_type(),
            'post_status' => 'publish',
            'meta_input' => array(
                '_fcmanager_match_external_id' => $entity->wedstrijdcode,
                '_fcmanager_match_date' => $entity->date(),
                '_fcmanager_match_starttime' => $entity->aanvangstijd,
                '_fcmanager_match_team' => $this->_team_id_by_code[$entity->team()],
                '_fcmanager_match_opponent' => $entity->opponent(),
                '_fcmanager_match_away' => $entity->isAway(),
                '_fcmanager_match_referee' => $referee_id,
            )
        );
        return wp_insert_post($post);
    }

    /**
     * Remove a match
     *
     * @param WP_Post $post The post object representing the team.
     * @return int|false The post ID on success, false on failure.
     */
    protected function handle_obsolete_post($post)
    {
        // Never delete matches, as they appear in the match results if canceled.
    }

    /**
     * Get the ID of a referee by name, creating a new referee if not found.
     *
     * @param FCMSL_Referee $referee The referee to get or create.
     * @return int The ID of the referee or null if there is no referee name.
     */
    private function get_or_create_referee_id($referee)
    {
        $name = $referee->name();
        if (!$name) {
            return null;
        }

        if (isset($this->_referee_id_by_name[$name])) {
            return $this->_referee_id_by_name[$name];
        }

        $post_id = wp_insert_post(array(
            'post_title' => $name,
            'post_type' => 'fcmanager_referee',
            'post_status' => 'publish',
            'meta_input' => array(
                '_fcmanager_referee_first_name' => $referee->first_name,
                '_fcmanager_referee_last_name' => $referee->last_name,
                '_fcmanager_referee_publish_birthday' => FCManager_Settings::instance()->referee->publish_birthday_by_default() ? 'true' : 'false',
                '_fcmanager_referee_publish_age' => FCManager_Settings::instance()->referee->publish_age_by_default() ? 'true' : 'false',
            )
        ));
        $this->_referee_id_by_name[$name] = $post_id;
        return $post_id;
    }
}
