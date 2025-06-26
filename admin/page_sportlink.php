<?php
if (! defined('ABSPATH')) {
    exit;
}

require_once(dirname(__FILE__) . '/../includes/importers/class-team-importer.php');

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
                                <form action="options.php<?php //menu_page_url('fcm-sportlink') 
                                                            ?>" method="post">
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
                                <h2><?php _e('Manual import', 'fcm-sportlink'); ?></h2>
                                <?php
                                if (isset($_POST['action']) && $_POST['action'] == 'fcm_import') {
                                    // Check nonce
                                    if (! isset($_POST['fcm_import_nonce']) || ! wp_verify_nonce($_POST['fcm_import_nonce'], 'fcm_import_nonce')) {
                                        echo '<div class="error"><p>' . __('Nonce verification failed', 'fcm-sportlink') . '</p></div>';
                                    } else {
                                        $api = new FCM_Sportlink_API(get_option('fcmsl_options')['fcmsl_field_sportlink_clientid']);
                                        if (isset($_POST['fcm_import_teams']))
                                            $importer = new FCM_Sportlink_Team_Importer($api);
                                        // else if (isset($_POST['fcm_import_players']))
                                        //     $importer = new FCM_Sportlink_Player_Importer($api);
                                        // else if (isset($_POST['fcm_import_matches']))
                                        //     $importer = new FCM_Sportlink_Match_Importer($api);

                                        if (!isset($importer)) {
                                            echo '<div class="error"><p>' . __('Import not implemented yet', 'fcm-sportlink') . '</p></div>';
                                        } else {
                                            $report = $importer->import();
                                            echo '<div class="notice notice-success is-dismissible"><p>' . sprintf(__('Imported %s new items, updated %s items, and deleted %s items', 'fcm-sportlink'), $report->created, $report->updated, $report->deleted) . '</p></div>';
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
