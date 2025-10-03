<?php

if (! defined('ABSPATH')) {
    exit;
}

require_once('class-importer.php');
require_once(ABSPATH . "wp-admin" . '/includes/image.php');

class FCMSL_Match_Result_Importer extends FCMSL_Importer
{
    private $_team_id_by_code = array();
    private $_match_code_by_id = array();

    protected function get_post_type()
    {
        return 'fcmanager_match';
    }

    /**
     * Retrieve a list of all scheduled matched from Sportlink.
     *
     * @return array List of FCMSL_Match_Result objects.
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

        $results = $this->_api->get_match_results();
        foreach ($results as $result) {
            $result->isAway = array_key_exists($result->uitteamid, $this->_team_id_by_code) ? 1 : 0;
        }
        return $results;
    }

    /**
     * Check if the given entity and post represent the same match.
     *
     * @param FCMSL_Match_Result $entity The Sportlink Match Result entity.
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
     * @param FCMSL_Match_result $entity The Sportlink Match Result to update the post for
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
        $dirty = $this->update_metadata_if_needed($post->ID, $meta_data, '_fcmanager_match_away', $entity->isAway) || $dirty;

        $dirty = $this->update_metadata_if_needed($post->ID, $meta_data, '_fcmanager_match_goals_for', $entity->matchGoalsFor()) || $dirty;
        $dirty = $this->update_metadata_if_needed($post->ID, $meta_data, '_fcmanager_match_goals_against', $entity->matchGoalsAgainst()) || $dirty;
        $dirty = $this->update_metadata_if_needed($post->ID, $meta_data, '_fcmanager_match_goals_forFinal', $entity->matchGoalsForFinal()) || $dirty;
        $dirty = $this->update_metadata_if_needed($post->ID, $meta_data, '_fcmanager_match_goals_againstFinal', $entity->matchGoalsAgainstFinal()) || $dirty;

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
     * @param FCMSL_Match_Result $entity The Sportlink Match to create a post for
     * @return int The ID of the newly created post
     */
    protected function handle_new_post($entity)
    {
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
                '_fcmanager_match_away' => $entity->isAway,

                '_fcmanager_match_goals_for' => $entity->matchGoalsFor(),
                '_fcmanager_match_goals_against' => $entity->matchGoalsAgainst(),
                '_fcmanager_match_goals_forFinal' => $entity->matchGoalsForFinal(),
                '_fcmanager_match_goals_againstFinal' => $entity->matchGoalsAgainstFinal()
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
        // Never remove matches, because results might just not be in yet, or are no longer available via the API.
        return false;
    }
}
