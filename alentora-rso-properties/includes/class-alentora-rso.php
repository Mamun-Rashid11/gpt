<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Alentora_RSO {
    private $api_key;
    private $client_id;

    public function __construct() {
        $this->api_key   = get_option( 'alentora_rso_api_key', '' );
        $this->client_id = get_option( 'alentora_rso_client_id', '' );
    }

    /**
     * Fetch properties from Resales Online.
     *
     * @param string $filter_id
     * @param int    $page
     * @param int    $per_page
     *
     * @return array|WP_Error
     */
    public function get_properties( $filter_id, $page = 1, $per_page = 10 ) {
        $url  = 'https://api.resales-online.com/v6/search';
        $args = [
            'headers' => [
                'RSOAPIKEY' => $this->api_key,
                'RSOCLIENTID' => $this->client_id,
                'Content-Type' => 'application/json',
            ],
            'body'    => wp_json_encode([
                'filterid' => $filter_id,
                'page'     => $page,
                'nbitems'  => $per_page,
            ]),
            'timeout' => 15,
        ];

        $response = wp_remote_post( $url, $args );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return new WP_Error( 'invalid_json', 'Invalid JSON response from API' );
        }

        return $data;
    }

    /**
     * Fetch single property details.
     *
     * @param string $property_id
     *
     * @return array|WP_Error
     */
    public function get_property_details( $property_id ) {
        $url  = 'https://api.resales-online.com/v6/properties/' . urlencode( $property_id );
        $args = [
            'headers' => [
                'RSOAPIKEY' => $this->api_key,
                'RSOCLIENTID' => $this->client_id,
            ],
            'timeout' => 15,
        ];

        $response = wp_remote_get( $url, $args );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return new WP_Error( 'invalid_json', 'Invalid JSON response from API' );
        }

        return $data;
    }
}
