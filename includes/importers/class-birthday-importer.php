<?php

if (! defined('ABSPATH')) {
    exit;
}


class FCMSL_Birthday_Importer extends FCMSL_Importer
{
    protected function get_post_type()
    {
        return ['fcmanager_player', 'fcmanager_volunteer'];
    }

    protected function get_entities()
    {
        return $this->_api->get_birthdays();
    }

    /**
     * Birthdays are only imported for existing players and volunteers, so no new posts are created.
     */
    protected function handle_new_post($entity)
    {
        return false;
    }

    /**
     * Birthdays are only imported for existing players and volunteers, so no posts are deleted.
     */
    protected function handle_obsolete_post($post)
    {
        return false;
    }

    /**
     * Compare by name, as the birthday import does not have an external ID to reliably match players and volunteers.
     */
    protected function is_same($entity, $post)
    {
        return $entity->full_name() == $post->post_title;
    }

    /**
     * Add date of birth to existing player or volunteer posts, but only if it is not already set, to avoid overwriting manually entered data.
     */
    protected function handle_updated_post($post, $entity)
    {
        $current = get_post_meta($post->ID, '_fcmanager_player_date_of_birth', true);
        if ($current == null) {
            return update_post_meta($post->ID, '_fcmanager_player_date_of_birth', $entity->date_of_birth());
        }
        return false;
    }
}
