<?php

namespace SmashBalloon\Reviews\Common\Integrations;

use SmashBalloon\Reviews\Common\Exceptions\RelayResponseException;
use SmashBalloon\Reviews\Common\Services\SettingsManagerService;

class SBRelay
{
    public const BASE_URL = SBR_RELAY_BASE_URL;
    /**
     * @var string|null
     */
    private $access_token;

    public function __construct(SettingsManagerService $settings)
    {
        $saved_settings = $settings->get_settings();

        if (isset($saved_settings['access_token'])) {
            $this->access_token = $saved_settings['access_token'];
        }


    }

    /**
     * @param string $endpoint
     * @param array $data
     * @param string $method
     * @param bool $require_auth
     * @return void
     *
     * @throws RelayResponseException
     */
    public function call(string $endpoint, array $data, string $method = 'POST', bool $require_auth = false): array
    {
        $url = $this->format_url($endpoint);
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];
        if (true === $require_auth) {
            $headers['Authorization'] = 'Bearer ' . $this->access_token;
        }

        switch ($method) {
            case 'GET':
                $callback = 'wp_remote_get';
                break;
            default:
                $callback = 'wp_remote_post';
                break;
        }

        $args = [
            'method' => $method,
            'headers' => $headers,
            'body' => $method === 'POST' ? json_encode($data) : $data
        ];

        if ($endpoint === 'auth/license'){
            $args['timeout'] = 120;
        }

        if (isset($this->data['language']) && !empty($this->data['language']) && $this->data['language'] !== 'default') {
            $args['language'] = $this->data['language'];
        }

        $response = $callback($url, $args);
        $body = ! is_wp_error( $response )  ? json_decode(wp_remote_retrieve_body($response), true) : [];
        return $body;
    }

    private function flatten_errors($errors)
    {
        if (is_array($errors)) {
            $mapped_errors = array_column($errors, 0);

            return implode(', ', $mapped_errors);
        }
        return $errors;
    }

    private function format_url($endpoint, $query = []): string
    {
        $query_string = http_build_query($query);
        return self::BASE_URL . stripslashes($endpoint) . '/' . $query_string;
    }

    /**
     * @return string|null
     */
    public function getAccessToken(): ?string
    {
        return $this->access_token;
    }

    /**
     * @param string|null $access_token
     */
    public function setAccessToken(?string $access_token): void
    {
        $this->access_token = $access_token;
    }
}