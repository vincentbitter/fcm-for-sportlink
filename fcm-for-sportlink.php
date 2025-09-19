<?php
/*
 * Plugin Name: Football Club Manager for Sportlink
 * Plugin URI: https://github.com/vincentbitter/fcm-for-sportlink
 * Description: Import data from Sportlink to Football Club Manager.
 * Version: 0.6.0
 * Requires at least: 6.8
 * Requires PHP: 7.4
 * Author: Vincent Bitter
 * Author URI: https://vincentbitter.nl
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: fcm-for-sportlink
 * Domain Path: /languages
 * Requires Plugins: football-club-manager
 */

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('FCMSL_VERSION', '0.6.0');

require_once('includes/cron/class-import-cron.php');

// Register settings
require_once('includes/settings.php');

// Register administration pages
require_once('admin/page_sportlink.php');

// Register administration menu
function fcmsl_register_administration_menu()
{
    add_submenu_page(
        'fcmanager',
        __('Sportlink', 'fcm-for-sportlink'),
        __('Sportlink', 'fcm-for-sportlink'),
        'manage_options',
        'fcm-for-sportlink',
        'fcmsl_page_sportlink',
        20
    );
}


// Add CSS files
function fcmsl_admin_enqueue_scripts()
{
    wp_enqueue_style('fcmsl-admin', plugins_url('css/admin.css', __FILE__), array(), constant('FCMSL_VERSION'));
}

add_action('admin_enqueue_scripts', 'fcmsl_admin_enqueue_scripts');


// On admin init
function fcmsl_admin_init()
{
    fcmsl_settings_init();
}

add_action('admin_init', 'fcmsl_admin_init');


// On admin menu
function fcmsl_admin_menu()
{
    fcmsl_register_administration_menu();
}

add_action('admin_menu', 'fcmsl_admin_menu', 20);


// Show settings link on plugins page
function fcmsl_plugin_settings_link($links)
{
    $settings_link = '<a href="admin.php?page=fcm-for-sportlink">' . __('Settings', 'fcm-for-sportlink') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'fcmsl_plugin_settings_link');


// Enable or disable cron on changing settings
function fcmsl_option_updated($option_name, $old_value, $new_value)
{
    if ($option_name === 'fcmsl_options') {
        $old = isset($old_value['fcmsl_field_automatic_import']) && $old_value['fcmsl_field_automatic_import'] == 1;
        $new = isset($new_value['fcmsl_field_automatic_import']) && $new_value['fcmsl_field_automatic_import'] == 1;
        if ($new !== $old) {
            $cron = new FCMSL_Import_Cron();
            $new ? $cron->enable() : $cron->disable();
        }
    }
}

add_action('update_option', 'fcmsl_option_updated', 10, 3);


// On activation
function fcmsl_activated()
{
    if (get_option('fcmsl_options')['fcmsl_field_automatic_import']) {
        (new FCMSL_Import_Cron())->enable();
    }
}

register_activation_hook(__FILE__, 'fcmsl_activated');


// On deactivation
function fcmsl_deactivated()
{
    (new FCMSL_Import_Cron())->disable();
}

register_deactivation_hook(__FILE__, 'fcmsl_deactivated');
