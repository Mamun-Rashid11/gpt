<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Alentora RSO Settings', 'alentora-rso' ); ?></h1>
    <form method="post" action="options.php">
        <?php
        settings_fields( 'alentora_rso_options' );
        do_settings_sections( 'alentora-rso-settings' );
        submit_button();
        ?>
    </form>
</div>
