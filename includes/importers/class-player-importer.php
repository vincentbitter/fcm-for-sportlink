<?php

if (! defined('ABSPATH')) {
    exit;
}

require_once('class-importer.php');
require_once(ABSPATH . "wp-admin" . '/includes/image.php');

class FCMSL_Player_Importer extends FCMSL_Importer
{
    private $_team_id_by_code = array();
    private $_player_code_by_id = array();

    protected function get_post_type()
    {
        return 'fcmanager_player';
    }

    public function import()
    {
        add_filter('fcmanager_skip_meta_box_save', '__return_true');
        $report = parent::import();
        remove_filter('fcmanager_skip_meta_box_save', '__return_true');
        return $report;
    }

    /**
     * Retrieve a list of all players from Sportlink for teams in Football Club Manager.
     * Technical staff and private profiles are excluded.
     * Players playing in multiple teams are only returned once, in the last team found.
     *
     * @return array List of FCMSL_Player objects.
     */
    protected function get_entities()
    {
        $teams = get_posts(array(
            'post_type' => 'fcmanager_team',
            'numberposts' => -1
        ));

        $entities = array();
        $this->_team_id_by_code = array();
        foreach ($teams as $team) {
            $teamcode = get_post_meta($team->ID, '_fcmanager_team_external_id', true);

            if ($teamcode) {
                $this->_team_id_by_code[$teamcode] = $team->ID;
                $entities = array_merge($entities, $this->_api->get_players($teamcode));
            }
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
     * @param FCMSL_Player $entity The Sportlink Player entity.
     * @param WP_Post $post The post object representing the player.
     * @return bool True if the entity and post have the same relatiecode, false otherwise.
     */
    protected function is_same($entity, $post)
    {
        if (! isset($this->_player_code_by_id[$post->ID]))
            $this->_player_code_by_id[$post->ID] = get_post_meta($post->ID, '_fcmanager_player_external_id', true);
        $relatiecode = $this->_player_code_by_id[$post->ID];
        return $relatiecode && $relatiecode == $entity->relatiecode;
    }

    /**
     * Update a player
     *
     * @param WP_Post $post The post object representing the player.
     * @param FCMSL_Player $entity The Sportlink Player to update the post for
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

        if ($dirty) {
            wp_update_post($post);
        }

        $meta_data = get_post_meta($post->ID);
        $dirty = $this->update_metadata_if_needed($post->ID, $meta_data, '_fcmanager_player_first_name', $entity->voornaam) || $dirty;
        $dirty = $this->update_metadata_if_needed($post->ID, $meta_data, '_fcmanager_player_last_name', $entity->last_name()) || $dirty;

        $old_image_md5 = get_post_meta($post->ID, '_fcmanager_player_sportlink_image_md5', true);
        if (!empty($entity->foto)) {
            $new_image = base64_decode($entity->foto);
            $new_image_md5 = md5($new_image);
            $sportlink_image_changed = $old_image_md5 != $new_image_md5;

            if ($sportlink_image_changed) {
                $current_image_md5 = '';
                $current_image_id = get_post_thumbnail_id($post->ID, 'full');
                if ($current_image_id) {
                    $current_image_path = get_attached_file($current_image_id);
                    if (file_exists($current_image_path)) {
                        $current_image_md5 = md5_file($current_image_path);
                    }
                }

                $current_image_is_from_sportlink = $current_image_md5 == $old_image_md5;
                if ($current_image_md5 == '' || $current_image_is_from_sportlink) {
                    if ($current_image_id)
                        wp_delete_attachment($current_image_id, true);
                    $this->upload_player_image($entity, $post->ID, $new_image);
                }

                $dirty = true;
                update_post_meta($post->ID, '_fcmanager_player_sportlink_image_md5', $new_image_md5);
            }
        }

        $team_id = $this->_team_id_by_code[$entity->teamcode];
        if ($team_id != $meta_data['_fcmanager_player_team'][0]) {
            update_post_meta($post->ID, '_fcmanager_player_team', $team_id);
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
     * Create a new player
     *
     * @param FCMSL_Player $entity The Sportlink Player to create a post for
     * @return int The ID of the newly created post
     */
    protected function handle_new_post($entity)
    {
        $post = array(
            'post_title' => $entity->full_name(),
            'post_type' => $this->get_post_type(),
            'post_status' => 'publish',
            'meta_input' => array(
                '_fcmanager_player_external_id' => $entity->relatiecode,
                '_fcmanager_player_first_name' => $entity->voornaam,
                '_fcmanager_player_last_name' => $entity->last_name(),
                '_fcmanager_player_team' => $this->_team_id_by_code[$entity->teamcode]
            )
        );
        $post_id = wp_insert_post($post);
        if (!empty($entity->foto)) {
            $new_image = base64_decode($entity->foto);
            $new_image_md5 = md5($new_image);
            $this->upload_player_image($entity, $post_id, $new_image);
            update_post_meta($post_id, '_fcmanager_player_sportlink_image_md5', $new_image_md5);
        }
        return $post_id;
    }

    /**
     * Remove a player
     *
     * @param WP_Post $post The post object representing the player.
     * @return int|false The post ID on success, false on failure.
     */
    protected function handle_obsolete_post($post)
    {
        $relatiecode = get_post_meta($post->ID, '_fcmanager_player_external_id', true);
        if ($relatiecode) {
            return wp_trash_post($post->ID, false);
        }
    }

    private function upload_player_image($entity, $post_id, $image)
    {
        $upload_dir = wp_upload_dir();
        $image_name = '/player_' . $entity->relatiecode . '.gif';
        $image_path = $upload_dir['path'] . $image_name;
        if (file_put_contents($image_path, $image)) {
            $attachment = array(
                'post_mime_type' => 'image/gif',
                'post_parent'    => $post_id,
                'post_title'     => esc_html($entity->full_name()),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );
            $attachment_id = wp_insert_attachment($attachment, $image_path, $post_id);
            if (! is_wp_error($attachment_id)) {
                $attachment_data = wp_generate_attachment_metadata($attachment_id, $image_path);
                wp_update_attachment_metadata($attachment_id,  $attachment_data);
                set_post_thumbnail($post_id, $attachment_id);
            } else {
                wp_delete_file($image_path);
            }
        }
    }
}
