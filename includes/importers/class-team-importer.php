<?php

if (! defined('ABSPATH')) {
    exit;
}

require_once('class-importer.php');

class FCM_Sportlink_Team_Importer extends FCM_Sportlink_Importer
{
    protected function get_post_type()
    {
        return 'fcm_team';
    }

    public function get_entities()
    {
        return $this->_api->get_teams();
    }

    public function is_same($entity, $post)
    {
        $teamcode = get_post_meta($post->ID, '_fcm_team_external_id', true);
        return $teamcode && $teamcode == $entity->teamcode;
    }

    public function handle_updated_post($post, $entity)
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

    public function handle_new_post($entity)
    {
        $post = array(
            'post_title' => $entity->teamnaam,
            'post_type' => $this->get_post_type(),
            'post_status' => 'publish',
            'meta_input' => array(
                '_fcm_team_external_id' => $entity->teamcode
            )
        );
        return wp_insert_post($post);
    }

    public function handle_obsolete_post($post)
    {
        $teamcode = get_post_meta($post->ID, '_fcm_team_external_id', true);
        if ($teamcode) {
            return wp_trash_post($post->ID, false);
        }
    }
}
