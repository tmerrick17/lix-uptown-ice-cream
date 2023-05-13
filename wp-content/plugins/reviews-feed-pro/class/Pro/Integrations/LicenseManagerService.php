<?php

namespace SmashBalloon\Reviews\Pro\Integrations;

use SmashBalloon\Reviews\Common\AuthorizationStatusCheck;
use SmashBalloon\Reviews\Common\Exceptions\RelayResponseException;
use SmashBalloon\Reviews\Common\Integrations\SBRelay;
use SmashBalloon\Reviews\Common\Services\SettingsManagerService;
use SmashBalloon\Reviews\Common\Utils\AjaxUtil;
use Smashballoon\Stubs\Services\ServiceProvider;

class LicenseManagerService extends ServiceProvider
{

    private $relay;
    private $settings;
    private $status;


    public function __construct(SBRelay $relay, SettingsManagerService $settings, AuthorizationStatusCheck $status)
    {
        $this->relay = $relay;
        $this->settings = $settings;
        $this->status = $status;
    }

    public function register()
    {
        add_action('wp_ajax_sbr_activate_license', [$this, 'ajax_activate_license']);
        add_action('wp_ajax_sbr_deactivate_license', [$this, 'ajax_deactivate_license']);
        add_action('wp_ajax_sbr_test_connection', [$this, 'ajax_test_connection']);
    }

    public function ajax_activate_license()
    {
        check_ajax_referer('sbr-admin', 'nonce');

        if (!sbr_current_user_can('manage_reviews_feed_options')) {
            wp_send_json_error();
        }

        if (!isset($_POST['license_key'])) {
            AjaxUtil::send_json_error('license_key, site_key are required.', 401);
        }

        $license_key = sanitize_text_field($_POST['license_key']);

        $data = [
            'license_key' => $license_key,
            'url'   => get_home_url(),
            'action' => 'activate',
        ];

        try {
            $response = $this->relay->call('auth/license', $data, 'POST', true);

            $this->settings->update_settings([
                'license_status' => isset( $response['data']['license'] ) ? $response['data']['license'] : '',
                'license_info' => isset($response['data']['api_data']) ? $response['data']['api_data'] : '',
                'license_key' => $license_key
            ]);

            $this->status->update_status([
                'last_cron_update' => time(),
                'license_info' => isset($response['data']['api_data']) ? $response['data']['api_data'] : '',
                'license_tier' => isset($response['data']['api_data']['price_id']) ? $response['data']['api_data']['price_id'] : 0
            ]);

            $plugin_status = new AuthorizationStatusCheck();

            echo sbr_json_encode(
                array_merge(
                    $response,
                    [
                        'license_status' => isset($response['data']['license']) ? $response['data']['license'] : '',
                        'license_info' => isset($response['data']['api_data']) ? $response['data']['api_data'] : '',
                        'license_key' => $license_key,
                        'pluginStatus' => $plugin_status->get_statuses()

                    ]
                )
            );
            wp_die();
        } catch (RelayResponseException $exception) {
            AjaxUtil::send_json_error($exception->getMessage(), $exception->getCode());
        }
    }

    public function ajax_deactivate_license()
    {
        check_ajax_referer('sbr-admin', 'nonce');

        if (!sbr_current_user_can('manage_reviews_feed_options')) {
            wp_send_json_error();
        }

        if (!isset($_POST['license_key'])) {
            AjaxUtil::send_json_error('license_key, site_key are required.', 401);
        }

        $license_key = sanitize_text_field($_POST['license_key']);

        $data = [
            'license_key' => $license_key,
            'url' => get_home_url(),
            'action' => 'deactivate',
        ];

        try {
            $response = $this->relay->call('auth/license', $data, 'POST', true);

            $this->settings->update_settings(
                [
                    'license_status' =>  '',
                    'license_info' => '',
                    'license_key' => ''
                ]
            );

            $this->status->update_status([
                'last_cron_update' => time(),
                'license_info' => '',
                'license_tier' => 0
            ]);

            wp_send_json($response);
            wp_die();
        } catch (RelayResponseException $exception) {
            AjaxUtil::send_json_error($exception->getMessage(), $exception->getCode());
        }
    }

    public function ajax_test_connection()
    {
        check_ajax_referer('sbr-admin', 'nonce');

        if (!sbr_current_user_can('manage_reviews_feed_options')) {
            wp_send_json_error();
        }
        try {
            $args = [
                'license_key' => 'xxx',
                'url' => get_home_url(),
                'action' => '',
                'headers' => [
                    'Content-Type' => 'application/json',
                ]
            ];

            $response = wp_remote_post(SBR_RELAY_BASE_URL . 'auth/license', $args);

            wp_send_json([
                'success' => is_wp_error($response) ? false : true
            ]);
        } catch (RelayResponseException $exception) {
            wp_send_json([
                'success' => false
            ]);
        }
        wp_die();
    }
}