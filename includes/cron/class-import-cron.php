<?php

if (! defined('ABSPATH')) {
    exit;
}

add_action('fcmsl_import_cron_hook', function () {
    (new FCM_Import_Cron())->run();
});

class FCM_Import_Cron
{
    public function run()
    {
        if (!defined('DISABLE_WP_CRON') || constant('DISABLE_WP_CRON') !== true)
            return;

        $api = new FCM_Sportlink_API(get_option('fcmsl_options')['fcmsl_field_sportlink_clientid']);
        $importer = new FCM_Sportlink_Team_Importer($api);
        $importer->import();
        $importer = new FCM_Sportlink_Player_Importer($api);
        $importer->import();
        // $importer = new FCM_Sportlink_Match_Importer($api);
        // $importer->import();
    }

    public function enable()
    {
        if (!wp_next_scheduled('fcmsl_import_cron_hook'))
            wp_schedule_event(time(), 'hourly', 'fcmsl_import_cron_hook');
    }

    public function disable()
    {
        wp_clear_scheduled_hook('fcmsl_import_cron_hook');
    }
}
