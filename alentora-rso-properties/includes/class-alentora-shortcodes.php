<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Alentora_Shortcodes {

    protected $api;

    public function __construct() {
        $this->api = new Alentora_RSO_API();
        add_shortcode( 'alentora_rso_search', array( $this, 'search_form_shortcode' ) );
        add_shortcode( 'alentora_rso_properties', array( $this, 'properties_shortcode' ) );
        add_shortcode( 'alentora_rso_properties_by_refs', array( $this, 'properties_by_refs_shortcode' ) );
    }

    /**
     * Render search form shortcode
     */
    public function search_form_shortcode( $atts ) {
        $defaults = array(
            'is_form_rental' => 'short',
            'filter_id' => get_option( 'alentora_rso_default_filter_id', '' ),
            'results_own_page' => 'false',
            'include_title_page' => '',
            'number_properties' => '',
            'all_areas' => 'false',
            'no_show_areas' => 'false',
            'no_show_type' => 'false',
            'no_show_rooms' => 'false',
            'no_show_baths' => 'false',
            'no_show_from_price' => 'false',
            'no_show_to_price' => 'false',
            'no_show_reference_search' => 'false',
            'no_show_number_properties' => 'false',
            'no_show_new_development' => 'false',
            'show_advanced_search' => 'false',
            'p_musthavefeatures' => '0',
        );
        $atts = shortcode_atts( $defaults, $atts, 'alentora_rso_search' );

        ob_start();
        ?>
        <form class="alentora-rso-search-form">
            <?php wp_nonce_field( 'alentora_rso_search', 'alentora_rso_nonce' ); ?>
            <label>
                <?php esc_html_e( 'Keyword', 'alentora-rso' ); ?>
                <input type="text" name="keyword" />
            </label>
            <button type="submit"><?php esc_html_e( 'Search', 'alentora-rso' ); ?></button>
        </form>
        <?php
        return ob_get_clean();
    }

    /**
     * Render properties list shortcode
     */
    public function properties_shortcode( $atts ) {
        $defaults = array(
            'filter_id' => get_option( 'alentora_rso_default_filter_id', '' ),
            'include_search' => 'false',
            'is_form_rental_search' => 'short',
            'results_own_page_search' => 'false',
            'include_pagination' => 'true',
            'include_sort' => 'true',
            'include_num_results' => 'true',
            'include_view_type' => 'true',
            'view_type' => 'card',
            'location' => '',
            'type' => '',
            'features' => '',
            'p_sortyype' => '0',
            'p_pagesize' => '10',
            'p_new_devs' => 'exclude',
        );
        $atts = shortcode_atts( $defaults, $atts, 'alentora_rso_properties' );

        $args = array(
            'filter_id' => absint( $atts['filter_id'] ),
            'location'  => sanitize_text_field( $atts['location'] ),
            'type'      => sanitize_text_field( $atts['type'] ),
            'features'  => sanitize_text_field( $atts['features'] ),
        );

        $properties = $this->api->get_properties( $args );
        if ( is_wp_error( $properties ) ) {
            return '<p>' . esc_html( $properties->get_error_message() ) . '</p>';
        }

        ob_start();
        echo '<div class="alentora-rso-properties">';
        if ( ! empty( $properties ) ) {
            foreach ( $properties as $property ) {
                echo '<div class="alentora-rso-property">';
                echo '<h3>' . esc_html( $property['title'] ?? '' ) . '</h3>';
                echo '</div>';
            }
        } else {
            esc_html_e( 'No properties found.', 'alentora-rso' );
        }
        echo '</div>';
        return ob_get_clean();
    }

    /**
     * Render properties by references shortcode
     */
    public function properties_by_refs_shortcode( $atts ) {
        $defaults = array(
            'filter_id' => get_option( 'alentora_rso_default_filter_id', '' ),
            'references' => '',
        );
        $atts = shortcode_atts( $defaults, $atts, 'alentora_rso_properties_by_refs' );

        $refs = array_filter( array_map( 'trim', explode( ',', $atts['references'] ) ) );
        $properties = $this->api->get_properties_by_refs( $refs );
        if ( is_wp_error( $properties ) ) {
            return '<p>' . esc_html( $properties->get_error_message() ) . '</p>';
        }

        ob_start();
        echo '<div class="alentora-rso-properties">';
        if ( ! empty( $properties ) ) {
            foreach ( $properties as $property ) {
                echo '<div class="alentora-rso-property">';
                echo '<h3>' . esc_html( $property['title'] ?? '' ) . '</h3>';
                echo '</div>';
            }
        } else {
            esc_html_e( 'No properties found.', 'alentora-rso' );
        }
        echo '</div>';
        return ob_get_clean();
    }
}

new Alentora_Shortcodes();
