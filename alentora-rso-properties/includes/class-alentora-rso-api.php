<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Alentora_RSO_API {

    protected $api_key;
    protected $client_id;

    public function __construct() {
        $this->api_key   = get_option( 'alentora_rso_api_key', '' );
        $this->client_id = get_option( 'alentora_rso_client_id', '' );
    }

    private function request( $endpoint, $args = array() ) {
        $url = add_query_arg( $args, $endpoint );

        $response = wp_remote_get( $url, array(
            'headers' => array(
                'API-KEY'   => $this->api_key,
                'CLIENT-ID' => $this->client_id,
            ),
            'timeout' => 15,
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( 200 !== $code ) {
            return new WP_Error( 'api_error', __( 'Unexpected API response.', 'alentora-rso' ) );
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return new WP_Error( 'json_error', __( 'Error decoding API response.', 'alentora-rso' ) );
        }

        return $data;
    }

    public function get_properties( array $args = array() ) {
        $endpoint = 'https://api.resales-online.com/v6/properties';
        return $this->request( $endpoint, $args );
    }

    public function get_search_results( array $args = array() ) {
        $endpoint = 'https://api.resales-online.com/v6/search';
        return $this->request( $endpoint, $args );
    }

    public function get_properties_by_refs( array $refs = array() ) {
        $endpoint = 'https://api.resales-online.com/v6/properties-by-ref';
        $args = array( 'refs' => implode( ',', $refs ) );
        return $this->request( $endpoint, $args );
    }

    public function get_property_details( $id ) {
        $endpoint = 'https://api.resales-online.com/v6/property/' . urlencode( $id );
        return $this->request( $endpoint );
    }
}
