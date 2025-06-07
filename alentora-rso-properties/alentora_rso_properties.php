<?php
/*
Plugin Name: Alentora RSO Properties
Description: Integrates with Resales Online API to display property listings.
Version: 1.0.0
Author: Your Name
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define plugin constants
if ( ! defined( 'ALENTORA_RSO_PATH' ) ) {
    define( 'ALENTORA_RSO_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'ALENTORA_RSO_URL' ) ) {
    define( 'ALENTORA_RSO_URL', plugin_dir_url( __FILE__ ) );
}

require_once ALENTORA_RSO_PATH . 'includes/class-alentora-rso.php';

/**
 * Activation hook to create options.
 */
function alentora_rso_activate() {
    add_option( 'alentora_rso_api_key', '' );
    add_option( 'alentora_rso_client_id', '' );
    add_option( 'alentora_rso_filter_sales', '' );
    add_option( 'alentora_rso_filter_rentals', '' );
    add_option( 'alentora_rso_filter_features', '' );
}
register_activation_hook( __FILE__, 'alentora_rso_activate' );

/**
 * Register settings page.
 */
function alentora_rso_admin_menu() {
    add_options_page( 'Alentora RSO', 'Alentora RSO', 'manage_options', 'alentora-rso', 'alentora_rso_settings_page' );
}
add_action( 'admin_menu', 'alentora_rso_admin_menu' );

/**
 * Register plugin settings.
 */
function alentora_rso_register_settings() {
    register_setting( 'alentora_rso_options', 'alentora_rso_api_key', [ 'sanitize_callback' => 'sanitize_text_field' ] );
    register_setting( 'alentora_rso_options', 'alentora_rso_client_id', [ 'sanitize_callback' => 'sanitize_text_field' ] );
    register_setting( 'alentora_rso_options', 'alentora_rso_filter_sales', [ 'sanitize_callback' => 'sanitize_text_field' ] );
    register_setting( 'alentora_rso_options', 'alentora_rso_filter_rentals', [ 'sanitize_callback' => 'sanitize_text_field' ] );
    register_setting( 'alentora_rso_options', 'alentora_rso_filter_features', [ 'sanitize_callback' => 'sanitize_text_field' ] );
}
add_action( 'admin_init', 'alentora_rso_register_settings' );

/**
 * Render settings page.
 */
function alentora_rso_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Alentora RSO Settings', 'alentora-rso' ); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'alentora_rso_options' );
            do_settings_sections( 'alentora_rso_options' );
            ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="alentora_rso_api_key"><?php esc_html_e( 'API Key', 'alentora-rso' ); ?></label></th>
                    <td><input name="alentora_rso_api_key" type="text" id="alentora_rso_api_key" value="<?php echo esc_attr( get_option( 'alentora_rso_api_key' ) ); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="alentora_rso_client_id"><?php esc_html_e( 'Client ID', 'alentora-rso' ); ?></label></th>
                    <td><input name="alentora_rso_client_id" type="text" id="alentora_rso_client_id" value="<?php echo esc_attr( get_option( 'alentora_rso_client_id' ) ); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="alentora_rso_filter_sales"><?php esc_html_e( 'Sales Filter ID', 'alentora-rso' ); ?></label></th>
                    <td><input name="alentora_rso_filter_sales" type="text" id="alentora_rso_filter_sales" value="<?php echo esc_attr( get_option( 'alentora_rso_filter_sales' ) ); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="alentora_rso_filter_rentals"><?php esc_html_e( 'Rentals Filter ID', 'alentora-rso' ); ?></label></th>
                    <td><input name="alentora_rso_filter_rentals" type="text" id="alentora_rso_filter_rentals" value="<?php echo esc_attr( get_option( 'alentora_rso_filter_rentals' ) ); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="alentora_rso_filter_features"><?php esc_html_e( 'Features Filter ID', 'alentora-rso' ); ?></label></th>
                    <td><input name="alentora_rso_filter_features" type="text" id="alentora_rso_filter_features" value="<?php echo esc_attr( get_option( 'alentora_rso_filter_features' ) ); ?>" class="regular-text" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

/**
 * Enqueue frontend assets.
 */
function alentora_rso_enqueue_assets() {
    wp_enqueue_style( 'alentora-rso-style', ALENTORA_RSO_URL . 'assets/css/style.css', [], '1.0.0' );
}
add_action( 'wp_enqueue_scripts', 'alentora_rso_enqueue_assets' );

/**
 * Shortcode to display properties.
 */
function alentora_rso_properties_shortcode( $atts ) {
    $atts = shortcode_atts(
        [
            'filter' => get_option( 'alentora_rso_filter_sales', '' ),
            'per_page' => 10,
            'page' => isset( $_GET['apt-page'] ) ? absint( $_GET['apt-page'] ) : 1,
        ],
        $atts,
        'alentora_properties'
    );

    $api = new Alentora_RSO();
    $data = $api->get_properties( $atts['filter'], $atts['page'], $atts['per_page'] );

    if ( is_wp_error( $data ) ) {
        return '<p>' . esc_html( $data->get_error_message() ) . '</p>';
    }

    if ( empty( $data['items'] ) ) {
        return '<p>' . esc_html__( 'No properties found.', 'alentora-rso' ) . '</p>';
    }

    $output = '<div class="alentora-properties">';
    foreach ( $data['items'] as $property ) {
        $image = isset( $property['photos'][0]['url'] ) ? esc_url( $property['photos'][0]['url'] ) : '';
        $title = isset( $property['summarytitle'] ) ? esc_html( $property['summarytitle'] ) : '';
        $location = isset( $property['locationtext'] ) ? esc_html( $property['locationtext'] ) : '';
        $price = isset( $property['price'] ) ? esc_html( $property['price'] ) : '';
        $output .= '<div class="alentora-property">';
        if ( $image ) {
            $output .= '<img src="' . $image . '" alt="" />';
        }
        $output .= '<h3 class="alentora-property-title">' . $title . '</h3>';
        $output .= '<p>' . $location . '</p>';
        $output .= '<p>' . $price . '</p>';
        $output .= '</div>';
    }
    $output .= '</div>';

    // Pagination
    $total_pages = isset( $data['total_pages'] ) ? intval( $data['total_pages'] ) : 1;
    if ( $total_pages > 1 ) {
        $output .= '<div class="alentora-pagination">';
        for ( $i = 1; $i <= $total_pages; $i++ ) {
            $url = add_query_arg( 'apt-page', $i );
            if ( $i === $atts['page'] ) {
                $output .= '<span>' . intval( $i ) . '</span> ';
            } else {
                $output .= '<a href="' . esc_url( $url ) . '">' . intval( $i ) . '</a> ';
            }
        }
        $output .= '</div>';
    }

    return $output;
}
add_shortcode( 'alentora_properties', 'alentora_rso_properties_shortcode' );
