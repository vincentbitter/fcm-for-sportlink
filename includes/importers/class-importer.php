<?php

if (! defined('ABSPATH')) {
    exit;
}

require_once(dirname(__FILE__) . '/../sportlink-api/class-sportlink-api.php');
require_once('class-import-report.php');

abstract class FCMSL_Importer
{
    /** @var FCMSL_Sportlink_API */
    protected $_api = null;

    public function __construct($api)
    {
        $this->_api = $api;
    }

    public function import()
    {
        $entities = $this->get_entities();
        $posts = $this->get_posts();
        $handled_posts = array();

        $created = 0;
        $updated = 0;
        foreach ($entities as $entity) {
            $result = $this->import_entity($entity, $posts);
            if ($result != null) {
                $post = $result[1];
                if ($result[0] == 'created') {
                    $created++;
                    $posts[] = $post;
                }
                if ($result[0] == 'updated')
                    $updated++;

                $handled_posts[] = $post;
            }
        }

        $unhandled_posts = array_udiff($posts, $handled_posts, function ($a, $b) {
            return $a->ID - $b->ID;
        });
        $deleted = 0;
        foreach ($unhandled_posts as $post) {
            if ($this->handle_obsolete_post($post))
                $deleted++;
        }

        return new FCMSL_Import_Report($created, $updated, $deleted);
    }

    protected function get_posts()
    {
        return get_posts(array(
            'post_type' => $this->get_post_type(),
            'post_status' => array('publish', 'pending', 'draft', 'future', 'private', 'inherit', 'trash'),
            'posts_per_page' => -1
        ));
    }

    protected function import_entity($entity, $posts)
    {
        foreach ($posts as $post) {
            if ($this->is_same($entity, $post)) {
                if ($this->handle_updated_post($post, $entity))
                    return ['updated', $post];
                return ['keep', $post];
            }
        }

        $post_id = $this->handle_new_post($entity);
        if ($post_id)
            return ['created', get_post($post_id)];
    }

    abstract protected function get_post_type();

    abstract protected function get_entities();

    abstract protected function is_same($entity, $post);

    abstract protected function handle_new_post($entity);

    abstract protected function handle_updated_post($post, $entity);

    abstract protected function handle_obsolete_post($post);
}
