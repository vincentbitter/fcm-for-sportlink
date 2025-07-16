<?php

if (! defined('ABSPATH')) {
    exit;
}

require_once('class-importer.php');

class FCMSL_Team_Importer extends FCMSL_Importer
{
    private $_team_code_by_id = array();

    protected function get_post_type()
    {
        return 'fcmanager_team';
    }

    /**
     * Retrieve a list of all teams from Sportlink.
     *
     * @return array List of FCMSL_Team objects.
     */
    protected function get_entities()
    {
        return $this->_api->get_teams();
    }

    /**
     * Check if the given entity and post represent the same team.
     *
     * @param FCMSL_Team $entity The Sportlink Team entity.
     * @param WP_Post $post The post object representing the team.
     * @return bool True if the entity and post have the same teamcode, false otherwise.
     */
    protected function is_same($entity, $post)
    {
        if (! isset($this->_team_code_by_id[$post->ID]))
            $this->_team_code_by_id[$post->ID] = get_post_meta($post->ID, '_fcmanager_team_external_id', true);
        $teamcode = $this->_team_code_by_id[$post->ID];

        return $teamcode && $teamcode == $entity->teamcode;
    }

    /**
     * Update a team
     *
     * @param WP_Post $post The post object representing the team.
     * @param FCMSL_Team $entity The Sportlink Team to update the post for
     * @return bool Whether the post was updated.
     */
    protected function handle_updated_post($post, $entity)
    {
        $dirty = false;
        if ($post->post_title != $entity->teamnaam) {
            $post->post_title = $entity->teamnaam;
            $dirty = true;
        }
        if ($post->post_status != 'publish') {
            $post->post_status = 'publish';
            $dirty = true;
        }

        if ($dirty)
            wp_update_post($post);

        return $dirty;
    }

    /**
     * Create a new team
     *
     * @param FCMSL_Team $entity The Sportlink Team to create a post for
     * @return int The ID of the newly created post
     */
    protected function handle_new_post($entity)
    {
        $post = array(
            'post_title' => $entity->teamnaam,
            'post_type' => $this->get_post_type(),
            'post_status' => 'publish',
            'meta_input' => array(
                '_fcmanager_team_external_id' => $entity->teamcode
            )
        );
        return wp_insert_post($post);
    }

    /**
     * Remove a team
     *
     * @param WP_Post $post The post object representing the team.
     * @return int|false The post ID on success, false on failure.
     */
    protected function handle_obsolete_post($post)
    {
        $teamcode = get_post_meta($post->ID, '_fcmanager_team_external_id', true);
        if ($teamcode) {
            return wp_trash_post($post->ID, false);
        }
    }
}
