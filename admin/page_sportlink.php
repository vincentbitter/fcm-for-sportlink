<?php
if (! defined('ABSPATH')) {
    exit;
}

require_once(dirname(__FILE__) . '/../includes/importers/class-team-importer.php');
require_once(dirname(__FILE__) . '/../includes/importers/class-player-importer.php');

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
                                $error_text = __('Please hook WP-cron into the system task scheduler to enable automatic import.', 'fcm-for-sportlink');
                                $more_info_text = __('More info', 'fcm-for-sportlink');
                                echo '<div class="error"><p>' . esc_html($error_text) . ' <a href="https://developer.wordpress.org/plugins/cron/hooking-wp-cron-into-the-system-task-scheduler/" target="_blank">' . esc_html($more_info_text) . '</a></p></div>';
                            }
                            ?>
                            <form action="options.php" method="post">
                                <?php
                                settings_fields('fcm-for-sportlink');
                                do_settings_sections('fcm-for-sportlink');
                                submit_button(__('Save settings', 'fcm-for-sportlink'));
                                ?>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="postbox-container">
                    <div class="meta-box-sortables">
                        <div class="card">
                            <h2><?php esc_html_e('Manual import', 'fcm-for-sportlink'); ?></h2>
                            <?php
                            if (isset($_POST['action']) && $_POST['action'] == 'fcmsl_import') {
                                // Check nonce
                                if (!check_admin_referer('fcmsl_import', 'fcmsl_import_nonce')) {
                                    echo '<div class="error"><p>' . esc_html__('Nonce verification failed', 'fcm-for-sportlink') . '</p></div>';
                                } else {
                                    $api = new FCMSL_Sportlink_API(get_option('fcmsl_options')['fcmsl_field_sportlink_clientid']);
                                    if (isset($_POST['fcmsl_import_teams']))
                                        $importer = new FCMSL_Team_Importer($api);
                                    else if (isset($_POST['fcmsl_import_players']))
                                        $importer = new FCMSL_Player_Importer($api);
                                    // else if (isset($_POST['fcmsl_import_matches']))
                                    //     $importer = new FCMSL_Match_Importer($api);

                                    if (!isset($importer)) {
                                        echo '<div class="error"><p>' . esc_html__('Import not implemented yet', 'fcm-for-sportlink') . '</p></div>';
                                    } else {
                                        try {
                                            $report = $importer->import();
                                            /* translators: 1: New items, 2: Updated items, 3: Deleted items. */
                                            $success_text = __('Imported %1$s new items, updated %2$s items, and deleted %3$s items.', 'fcm-for-sportlink');
                                            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html(sprintf($success_text, $report->created, $report->updated, $report->deleted)) . '</p></div>';
                                        } catch (FCMSL_Sportlink_Exception $e) {
                                            echo '<div class="error"><p>' . esc_html($e->getMessage(), 'fcm-for-sportlink') . '</p><p>' .
                                                '<strong>' . esc_html__('Response from Sportlink', 'fcm-for-sportlink') . ':</strong><br />' .
                                                esc_html__('Error message', 'fcm-for-sportlink') . ': ' . esc_html($e->getApiErrorMessage()) . '<br />' .
                                                esc_html__('Error code', 'fcm-for-sportlink') . ': ' . esc_html($e->getApiErrorCode()) . '<br />' .
                                                esc_html__('HTTP Code', 'fcm-for-sportlink') . ': ' . esc_html($e->getApiHttpResponseCode()) .
                                                '</p></div>';
                                        }
                                    }
                                }
                            }
                            ?>
                            <form method="POST">
                                <?php wp_nonce_field('fcmsl_import', 'fcmsl_import_nonce'); ?>
                                <input type="hidden" name="action" value="fcmsl_import" value="true">
                                <?php submit_button(__('Import teams', 'fcm-for-sportlink'), 'primary', 'fcmsl_import_teams', false); ?>
                                <?php submit_button(__('Import players', 'fcm-for-sportlink'), 'primary', 'fcmsl_import_players', false); ?>
                                <?php submit_button(__('Import matches', 'fcm-for-sportlink'), 'primary', 'fcmsl_import_matches', false); ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
}
