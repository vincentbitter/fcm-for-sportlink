<?php

if (! defined('ABSPATH')) {
    exit;
}

function fcmsl_field_text_callback($args)
{
    $options = get_option('fcmsl_options');
?>
    <input type="text" id="<?php echo esc_attr($args['label_for']); ?>"
        name="fcmsl_options[<?php echo esc_attr($args['label_for']); ?>]"
        value="<?php echo isset($options[$args['label_for']]) ? esc_attr($options[$args['label_for']]) : ''; ?>">
<?php
}


function fcmsl_field_toggle_callback($args)
{
    $options = get_option('fcmsl_options');
?>
    <input type="checkbox" id="<?php echo esc_attr($args['label_for']); ?>"
        name="fcmsl_options[<?php echo esc_attr($args['label_for']); ?>]"
        value="1" <?php checked(
                        1,
                        (isset($options[$args['label_for']]) ? $options[$args['label_for']] : 0)
                    ); ?>>
<?php
}


function fcmsl_options_sanitize_callback($input)
{
    $input['fcmsl_field_sportlink_clientid'] = sanitize_text_field($input['fcmsl_field_sportlink_clientid']);
    $input['fcmsl_field_automatic_import'] = $input['fcmsl_field_automatic_import'] == 1 ? 1 : 0;

    return $input;
}

function fcmsl_settings_init()
{
    // Register a new setting for "fcm-for-sportlink" page.
    register_setting('fcm-for-sportlink', 'fcmsl_options', 'fcmsl_options_sanitize_callback');

    // Register a new section in the "fcm-for-sportlink" page.
    add_settings_section(
        'fcmsl_section_settings',
        __('Settings', 'fcm-for-sportlink'),
        null,
        'fcm-for-sportlink'
    );

    // Register "Client ID" field: fcm-for-sportlink > fcmsl_section_settings > fcmsl_field_sportlink_clientid.
    add_settings_field(
        'fcmsl_field_sportlink_clientid',
        __('Sportlink client ID', 'fcm-for-sportlink'),
        'fcmsl_field_text_callback',
        'fcm-for-sportlink',
        'fcmsl_section_settings',
        array('label_for' => 'fcmsl_field_sportlink_clientid')
    );

    // Register "Automatic import" toggle: fcm-for-sportlink > fcmsl_section_settings > fcmsl_field_automatic_import.
    add_settings_field(
        'fcmsl_field_automatic_import',
        __('Automatic import', 'fcm-for-sportlink'),
        'fcmsl_field_toggle_callback',
        'fcm-for-sportlink',
        'fcmsl_section_settings',
        array('label_for' => 'fcmsl_field_automatic_import')
    );
}
