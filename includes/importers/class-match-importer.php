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
    private $_competition_by_sportlink_id = array();
    private $_pool_id_by_competition_id_and_name = array();

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

        $competitions = get_posts(array(
            'post_type' => 'fcmanager_comp',
            'numberposts' => -1
        ));
        $this->_competition_by_sportlink_id = array();
        foreach ($competitions as $competition) {
            $sportlink_id = get_post_meta($competition->ID, '_fcmanager_comp_external_id', true);
            if ($sportlink_id)
                $this->_competition_by_sportlink_id[$sportlink_id] = $competition;
        }

        $competitions_ids = array_map(fn($comp) => $comp->ID, array_values($this->_competition_by_sportlink_id));
        $pools = get_posts(array(
            'post_type' => 'fcmanager_pool',
            'numberposts' => -1
        ));
        $this->_pool_id_by_competition_id_and_name = array();
        foreach ($pools as $pool) {
            if (in_array($pool->post_parent, $competitions_ids))
                $this->_pool_id_by_competition_id_and_name[$pool->post_parent][$pool->post_title] = $pool->ID;
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
        $competition_id = $this->get_or_create_competition_id($entity->competitie, new DateTime($entity->date()));
        $pool_id = $this->get_or_create_pool_id($competition_id, $entity->klasse, $entity->poule);

        $dirty = false;
        if ($post->post_title != $entity->wedstrijd) {
            $post->post_title = $entity->wedstrijd;
            $dirty = true;
        }
        if ($post->post_status != 'publish') {
            $post->post_status = 'publish';
            $dirty = true;
        }
        if ($post->post_parent != $pool_id) {
            $post->post_parent = $pool_id;
            $dirty = true;
        }

        if ($dirty) {
            wp_update_post($post);
        }

        $meta_data = get_post_meta($post->ID);
        $dirty = $this->update_metadata_if_needed($post->ID, $meta_data, '_fcmanager_match_date', $entity->date()) || $dirty;
        $dirty = $this->update_metadata_if_needed($post->ID, $meta_data, '_fcmanager_match_starttime', $entity->aanvangstijd) || $dirty;
        if (array_key_exists($entity->team(), $this->_team_id_by_code))
            $dirty = $this->update_metadata_if_needed($post->ID, $meta_data, '_fcmanager_match_team', $this->_team_id_by_code[$entity->team()]) || $dirty;
        $dirty = $this->update_metadata_if_needed($post->ID, $meta_data, '_fcmanager_match_opponent', $entity->opponent()) || $dirty;
        $dirty = $this->update_metadata_if_needed($post->ID, $meta_data, '_fcmanager_match_away', $entity->isAway()) || $dirty;
        $dirty = $this->update_metadata_if_needed($post->ID, $meta_data, '_fcmanager_match_referee', $this->get_or_create_referee_id($entity->referee())) || $dirty;

        return $dirty;
    }

    /**
     * Create a new match
     *
     * @param FCMSL_Match $entity The Sportlink Match to create a post for
     * @return int The ID of the newly created post
     */
    protected function handle_new_post($entity)
    {
        $competition_id = $this->get_or_create_competition_id($entity->competitie, new DateTime($entity->date()));
        $pool_id = $this->get_or_create_pool_id($competition_id, $entity->klasse, $entity->poule);
        $referee_id = $this->get_or_create_referee_id($entity->referee());

        $post = array(
            'post_title' => $entity->wedstrijd,
            'post_type' => $this->get_post_type(),
            'post_status' => 'publish',
            'post_parent' => $pool_id,
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
        $matchcode = get_post_meta($post->ID, '_fcmanager_match_external_id', true);

        if ($matchcode) {
            // Only delete planned matches, as played matches will appear in the results import.
            if (get_post_meta($post->ID, '_fcmanager_match_date', true) > wp_date('Y-m-d'))
                return wp_trash_post($post->ID, false);
        }
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

    private function get_or_create_competition_id($competitie, $date)
    {
        if (!$competitie)
            return null;

        $parts = explode(' ', $competitie, 2);
        $sportlink_id = $parts[0];
        $name = $parts[1];

        if (array_key_exists($sportlink_id, $this->_competition_by_sportlink_id)) {
            $competition = $this->_competition_by_sportlink_id[$sportlink_id];

            $start_date = get_post_meta($competition->ID, '_fcmanager_comp_start_date', true);
            $start_date = $start_date ? new DateTime($start_date) : null;

            $end_date = get_post_meta($competition->ID, '_fcmanager_comp_end_date', true);
            $end_date = $end_date ? new DateTime($end_date) : null;

            if (!$start_date || $start_date > $date) {
                update_post_meta($competition->ID, '_fcmanager_comp_start_date', $date->format('Y-m-d'));
            }
            if (!$end_date || $end_date < $date) {
                update_post_meta($competition->ID, '_fcmanager_comp_end_date', $date->format('Y-m-d'));
            }
            if ($name != $competition->post_title) {
                wp_update_post([
                    'ID' => $competition->ID,
                    'post_title' => $name,
                    'post_name' => sanitize_title($name),
                ]);
            }

            return $competition->ID;
        }

        $id = wp_insert_post(array(
            'post_title' => $parts[1],
            'post_name' => sanitize_title($parts[1]),
            'post_type' => 'fcmanager_comp',
            'post_status' => 'publish',
            'meta_input' => array(
                '_fcmanager_comp_external_id' => $parts[0],
                '_fcmanager_comp_start_date' => $date->format('Y-m-d'),
                '_fcmanager_comp_end_date' => $date->format('Y-m-d'),
            )
        ));
        $this->_competition_by_sportlink_id[$sportlink_id] = get_post($id);
        return $id;
    }

    private function get_or_create_pool_id($competition_id, $klasse, $poule)
    {
        if (!$competition_id || !$klasse || !$poule)
            return null;

        $klassepoule = $klasse . ' ' . $poule;
        if (!array_key_exists($competition_id, $this->_pool_id_by_competition_id_and_name))
            $this->_pool_id_by_competition_id_and_name[$competition_id] = array();

        if (array_key_exists($klassepoule, $this->_pool_id_by_competition_id_and_name[$competition_id]))
            return $this->_pool_id_by_competition_id_and_name[$competition_id][$klassepoule];

        $id = wp_insert_post(array(
            'post_title' => $klassepoule,
            'post_name' => sanitize_title($klassepoule),
            'post_type' => 'fcmanager_pool',
            'post_status' => 'publish',
            'post_parent' => $competition_id,
            'meta_input' => array(
                '_fcmanager_pool_division' => $klasse,
                '_fcmanager_pool_name' => $poule
            )
        ));
        $this->_pool_id_by_competition_id_and_name[$competition_id][$klassepoule] = $id;
        return $id;
    }
}
