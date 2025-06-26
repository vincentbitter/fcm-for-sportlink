<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('fcmsl_field_sportlink_clientid_callback')) {
    function fcmsl_field_text_callback($args)
    {
        $options = get_option('fcmsl_options');
?>
        <input type="text" id="<?php echo esc_attr($args['label_for']); ?>"
            name="fcmsl_options[<?php echo esc_attr($args['label_for']); ?>]"
            value="<?php echo isset($options[$args['label_for']]) ? esc_attr($options[$args['label_for']]) : ''; ?>">
<?php
    }
}

if (! function_exists('fcmsl_settings_init')) {
    function fcmsl_settings_init()
    {
        // Register a new setting for "fcm-sportlink" page.
        register_setting('fcm-sportlink', 'fcmsl_options');

        // Register a new section in the "fcm-sportlink" page.
        add_settings_section(
            'fcmsl_section_settings',
            __('Settings', 'fcm-sportlink'),
            null,
            'fcm-sportlink'
        );

        // Register a new field in the "wporg_section_developers" section, inside the "wporg" page.
        add_settings_field(
            'fcmsl_field_sportlink_clientid',
            __('Sportlink client ID', 'fcm-sportlink'),
            'fcmsl_field_text_callback',
            'fcm-sportlink',
            'fcmsl_section_settings',
            array('label_for' => 'fcmsl_field_sportlink_clientid')
        );
    }
}
