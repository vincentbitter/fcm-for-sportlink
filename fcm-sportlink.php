<?php
/*
 * Plugin Name: Football Club Manager - Sportlink
 * Plugin URI: https://github.com/vincentbitter/fcm-sportlink
 * Description: Import data from Sportlink to Football Club Manager.
 * Version: 0.1
 * Requires at least: 6.8.1
 * Requires PHP: 8.2
 * Author: Vincent Bitter
 * Author URI: https://vincentbitter.nl
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: fcm-sportlink
 * Domain Path: /languages
 * Requires Plugins: football-club-manager
 */

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Register settings
require_once('includes/settings.php');

// Register administration pages
require_once('admin/page_sportlink.php');

// Register administration menu
if (! function_exists('fcmsl_register_administration_menu')) {
    function fcmsl_register_administration_menu()
    {
        add_submenu_page(
            'fcm',
            __('Sportlink', 'fcm-sportlink'),
            __('Sportlink', 'fcm-sportlink'),
            'manage_options',
            'fcm-sportlink',
            'fcmsl_page_sportlink',
            20
        );
    }
}

// Add CSS files
if (! function_exists('fcmsl_admin_enqueue_scripts')) {
    function fcmsl_admin_enqueue_scripts()
    {
        wp_enqueue_style('fcmsl-admin', plugins_url('css/admin.css', __FILE__));
    }

    add_action('admin_enqueue_scripts', 'fcmsl_admin_enqueue_scripts');
}

// On admin init
if (! function_exists('fcmsl_admin_init')) {
    function fcmsl_admin_init()
    {
        fcmsl_settings_init();
    }

    add_action('admin_init', 'fcmsl_admin_init');
}

// On admin menu
if (! function_exists('fcmsl_admin_menu')) {
    function fcmsl_admin_menu()
    {
        fcmsl_register_administration_menu();
    }

    add_action('admin_menu', 'fcmsl_admin_menu', 20);
}
