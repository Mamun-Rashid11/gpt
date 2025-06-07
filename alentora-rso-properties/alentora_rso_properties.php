<?php
/**
 * Plugin Name: Alentora RSO Properties
 * Description: Integrates with Resales Online API to display property listings and search forms.
 * Version: 1.0.0
 * Author: Your Name
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Alentora_RSO_Properties {

    const VERSION = '1.0.0';

    public function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }

    private function define_constants() {
        define( 'ALENTORA_RSO_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
        define( 'ALENTORA_RSO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
    }

    private function includes() {
        require_once ALENTORA_RSO_PLUGIN_PATH . 'includes/class-alentora-rso-api.php';
        require_once ALENTORA_RSO_PLUGIN_PATH . 'includes/class-alentora-shortcodes.php';
    }

    private function init_hooks() {
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    public function activate() {
        add_option( 'alentora_rso_api_key', '' );
        add_option( 'alentora_rso_client_id', '' );
        add_option( 'alentora_rso_default_filter_id', '' );
    }

    public function register_settings_page() {
        add_options_page(
            __( 'Alentora RSO Settings', 'alentora-rso' ),
            __( 'Alentora RSO', 'alentora-rso' ),
            'manage_options',
            'alentora-rso-settings',
            array( $this, 'render_settings_page' )
        );
    }

    public function register_settings() {
        register_setting( 'alentora_rso_options', 'alentora_rso_api_key', array( 'sanitize_callback' => 'sanitize_text_field' ) );
        register_setting( 'alentora_rso_options', 'alentora_rso_client_id', array( 'sanitize_callback' => 'sanitize_text_field' ) );
        register_setting( 'alentora_rso_options', 'alentora_rso_default_filter_id', array( 'sanitize_callback' => 'absint' ) );

        add_settings_section(
            'alentora_rso_main_section',
            __( 'API Settings', 'alentora-rso' ),
            '__return_false',
            'alentora-rso-settings'
        );

        add_settings_field(
            'alentora_rso_api_key',
            __( 'API Key', 'alentora-rso' ),
            array( $this, 'render_api_key_field' ),
            'alentora-rso-settings',
            'alentora_rso_main_section'
        );

        add_settings_field(
            'alentora_rso_client_id',
            __( 'Client ID', 'alentora-rso' ),
            array( $this, 'render_client_id_field' ),
            'alentora-rso-settings',
            'alentora_rso_main_section'
        );

        add_settings_field(
            'alentora_rso_default_filter_id',
            __( 'Default Filter ID', 'alentora-rso' ),
            array( $this, 'render_filter_id_field' ),
            'alentora-rso-settings',
            'alentora_rso_main_section'
        );
    }

    public function enqueue_assets() {
        wp_enqueue_style( 'alentora-rso-style', ALENTORA_RSO_PLUGIN_URL . 'assets/css/style.css', array(), self::VERSION );
    }

    public function render_api_key_field() {
        $value = get_option( 'alentora_rso_api_key', '' );
        echo '<input type="text" name="alentora_rso_api_key" value="' . esc_attr( $value ) . '" class="regular-text" />';
    }

    public function render_client_id_field() {
        $value = get_option( 'alentora_rso_client_id', '' );
        echo '<input type="text" name="alentora_rso_client_id" value="' . esc_attr( $value ) . '" class="regular-text" />';
    }

    public function render_filter_id_field() {
        $value = get_option( 'alentora_rso_default_filter_id', '' );
        echo '<input type="number" name="alentora_rso_default_filter_id" value="' . esc_attr( $value ) . '" class="small-text" />';
    }

    public function render_settings_page() {
        if ( ! current_user_can( "manage_options" ) ) {
            return;
        }
        include ALENTORA_RSO_PLUGIN_PATH . "admin/settings-page.php";
    }
}

// Initialize plugin
add_action( 'plugins_loaded', function() {
    new Alentora_RSO_Properties();
} );

?>
