<?php

namespace FluentFormPro\classes;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class AddressAutoComplete
{
    private $optionKey = 'ff_google_maps_autocomplete';
    private $integrationKey = 'google_maps_autocomplete';

    public function init()
    {
        add_filter('fluentform_global_settings_components', array($this, 'addGlobalMenu'), 1);
        add_filter('fluentform_global_integration_settings_' . $this->optionKey, array($this, 'getSettings'), 10);
        add_filter('fluentform_global_integration_fields_' . $this->optionKey, array($this, 'getSettingsFields'), 10, 1);
        add_action('fluentform_save_global_integration_settings_' . $this->optionKey, array($this, 'saveSettings'), 10, 1);

        add_filter('fluentform_editor_vars', function ($vars) {
            if ($this->isEnabled()) {
                $vars['has_address_gmap_api'] = true;
            }
            return $vars;
        });

        add_action('fluentform_address_map_autocomplete', array($this, 'addGmapJs'), 10, 2);

    }

    public function addGlobalMenu($setting)
    {
        $setting['google_maps_autocomplete'] = [
            'hash'         => $this->integrationKey,
            'component'    => 'general-integration-settings',
            'settings_key' => $this->optionKey,
            'title'        => 'Google Maps Integration'
        ];
        return $setting;
    }

    public function getSettings($settings)
    {
        $globalSettings = get_option($this->optionKey);
        if (!$globalSettings) {
            $globalSettings = [];
        }

        $defaults = [
            'api_key' => '',
            'status'  => ''
        ];

        return wp_parse_args($globalSettings, $defaults);
    }

    public function getSettingsFields($fields)
    {
        return [
            'logo'             => FLUENTFORMPRO_DIR_URL . 'public/images/google_map.png',
            'menu_title'       => __('Google Map Integration Settings', 'fluentformpro'),
            'menu_description' => __('For address autocomplete feature you may setup google map API details', 'fluentformpro'),
            'valid_message'    => __('Google Map API is set.', 'fluentformpro'),
            'invalid_message'  => __('Google Map API key is not valid', 'fluentformpro'),
            'save_button_text' => __('Save Settings', 'fluentformpro'),
            'fields'           => [
                'api_key' => [
                    'type'        => 'password',
                    'placeholder' => '',
                    'label_tips'  => __("Enter your Google Map API Key", 'fluentformpro'),
                    'label'       => __('Google Map API Key', 'fluentformpro'),
                ],
            ],
            'hide_on_valid'    => true,
            'discard_settings' => [
                'section_description' => 'Google Map API is set. You can now enable google map autocomplete feature for Address Field',
                'button_text'         => 'Disconnect Google Map API',
                'data'                => [
                    'api_key' => ''
                ]
            ]
        ];
    }

    public function saveSettings($settings)
    {
        $key = $settings['api_key'];

        if (!$key) {
            $defaults = [
                'api_key' => '',
                'status'  => ''
            ];
            update_option($this->optionKey, $defaults, 'no');
            wp_send_json_success([
                'message' => __('Your settings has been updated and discarded', 'fluentformpro'),
                'status'  => false
            ], 200);
        }

        update_option($this->optionKey, [
            'api_key' => sanitize_text_field($settings['api_key']),
            'status'  => true
        ], 'no');

        wp_send_json_success([
            'message' => __('Google Map Api key has been saved', 'fluentformpro'),
            'status'  => true
        ], 200);

    }

    public function isEnabled()
    {
        $settings = $this->getSettings([]);

        return !empty($settings['api_key']) && $settings['status'];
    }

    public function addGmapJs($data, $form)
    {
        $settings = $this->getSettings([]);

        if (empty($settings['api_key'])) {
            return;
        }

        $apiKey = $settings['api_key'];
        wp_enqueue_script('google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . $apiKey . '&libraries=places', [], FLUENTFORM_VERSION, true);
        wp_enqueue_script('ff_gmap', FLUENTFORMPRO_DIR_URL.'public/js/ff_gmap.js', ['jquery'], FLUENTFORM_VERSION, true);
    }
}