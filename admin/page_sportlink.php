<?php
if (! defined('ABSPATH')) {
    exit;
}

require_once(dirname(__FILE__) . '/../includes/importers/class-team-importer.php');
require_once(dirname(__FILE__) . '/../includes/importers/class-player-importer.php');

if (! function_exists('fcmsl_page_sportlink')) {
    function fcmsl_page_sportlink()
    {

        if (! current_user_can('manage_options')) {
            return;
        }
?>
        <div class="wrap">
            <h1>Sportlink</h1>
            <div class="dashboard-widgets-wrap">
                <div id="dashboard-widgets" class="metabox-holder">
                    <div class="postbox-container">
                        <div class="meta-box-sortables">
                            <div class="card">
                                <?php
                                $options = get_option('fcmsl_options');
                                if (isset($options['fcmsl_field_automatic_import']) && $options['fcmsl_field_automatic_import'] == 1 && (!defined('DISABLE_WP_CRON') || constant('DISABLE_WP_CRON') !== true)) {
                                    $error_text = __('Please hook WP-cron into the system task scheduler to enable automatic import.', 'fcm-sportlink');
                                    $more_info_text = __('More info', 'fcm-sportlink');
                                    echo '<div class="error"><p>' . esc_html($error_text) . ' <a href="https://developer.wordpress.org/plugins/cron/hooking-wp-cron-into-the-system-task-scheduler/" target="_blank">' . esc_html($more_info_text) . '</a></p></div>';
                                }
                                ?>
                                <form action="options.php" method="post">
                                    <?php
                                    // output security fields for the registered setting "fcmsl_options"
                                    settings_fields('fcm-sportlink');
                                    // output setting sections and their fields
                                    // (sections are registered for "fcm_options", each field is registered to a specific section)
                                    do_settings_sections('fcm-sportlink');
                                    // output save settings button
                                    submit_button(__('Save settings', 'fcm-sportlink'));
                                    ?>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="postbox-container">
                        <div class="meta-box-sortables">
                            <div class="card">
                                <h2><?php esc_html_e('Manual import', 'fcm-sportlink'); ?></h2>
                                <?php
                                if (isset($_POST['action']) && $_POST['action'] == 'fcm_import') {
                                    // Check nonce
                                    if (! array_key_exists('fcm_import_nonce', $_POST) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['fcm_import_nonce'])), 'fcm_import_nonce')) {
                                        echo '<div class="error"><p>' . esc_html__('Nonce verification failed', 'fcm-sportlink') . '</p></div>';
                                    } else {
                                        $api = new FCM_Sportlink_API(get_option('fcmsl_options')['fcmsl_field_sportlink_clientid']);
                                        if (isset($_POST['fcm_import_teams']))
                                            $importer = new FCM_Sportlink_Team_Importer($api);
                                        else if (isset($_POST['fcm_import_players']))
                                            $importer = new FCM_Sportlink_Player_Importer($api);
                                        // else if (isset($_POST['fcm_import_matches']))
                                        //     $importer = new FCM_Sportlink_Match_Importer($api);

                                        if (!isset($importer)) {
                                            echo '<div class="error"><p>' . esc_html__('Import not implemented yet', 'fcm-sportlink') . '</p></div>';
                                        } else {
                                            $report = $importer->import();
                                            /* translators: 1: New items, 2: Updated items, 3: Deleted items. */
                                            $success_text = __('Imported %1$s new items, updated %2$s items, and deleted %3$s items', 'fcm-sportlink');
                                            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html(sprintf($success_text, $report->created, $report->updated, $report->deleted)) . '</p></div>';
                                        }
                                    }
                                }
                                ?>
                                <form method="POST">
                                    <?php wp_nonce_field('fcm_import_nonce', 'fcm_import_nonce'); ?>
                                    <input type="hidden" name="action" value="fcm_import" value="true">
                                    <?php submit_button(__('Import teams', 'fcm-sportlink'), 'primary', 'fcm_import_teams', false); ?>
                                    <?php submit_button(__('Import players', 'fcm-sportlink'), 'primary', 'fcm_import_players', false); ?>
                                    <?php submit_button(__('Import matches', 'fcm-sportlink'), 'primary', 'fcm_import_matches', false); ?>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php
    }
}
