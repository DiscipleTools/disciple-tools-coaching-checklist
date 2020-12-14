<?php
/**
 * Plugin Name: Disciple Tools - Coaching Checklist
 * Plugin URI: https://github.com/DiscipleTools/disciple-tools-coaching-checklist
 * Description: Coaching Checklist Inspired by Zume
 * Version:  0.1.0
 * Author URI: https://github.com/DiscipleTools
 * GitHub Plugin URI: https://github.com/DiscipleTools/disciple-tools-coaching-checklist
 * Requires at least: 4.7.0
 * (Requires 4.7+ because of the integration of the REST API at 4.7 and the security requirements of this milestone version.)
 * Tested up to: 5.6
 *
 * @package Disciple_Tools
 * @link    https://github.com/DiscipleTools
 * @license GPL-2.0 or later
 *          https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

add_action( 'after_setup_theme', function (){
  new DT_coaching_checklist();
});
class DT_coaching_checklist {
    public static $required_dt_theme_version = '1.0.0';
    public static $rest_namespace = null; //use if you have custom rest endpoints on this plugin
    public static $plugin_name = "Coaching Checklist";


    public function __construct() {
        $wp_theme = wp_get_theme();
        $version = $wp_theme->version;
        /*
         * Check if the Disciple.Tools theme is loaded and is the latest required version
         */
        $is_theme_dt = strpos( $wp_theme->get_template(), "disciple-tools-theme" ) !== false || $wp_theme->name === "Disciple Tools";
        if ( $is_theme_dt && version_compare( $version, self::$required_dt_theme_version, "<" ) ) {
            add_action( 'admin_notices', [ $this, 'dt_plugin_hook_admin_notice' ] );
            add_action( 'wp_ajax_dismissed_notice_handler', 'dt_hook_ajax_notice_handler' );
            return false;
        }
        if ( !$is_theme_dt ){
            return false;
        }
        /**
         * Load useful function from the theme
         */
        if ( !defined( 'DT_FUNCTIONS_READY' ) ){
            require_once get_template_directory() . '/dt-core/global-functions.php';
        }
        /*
         * Don't load the plugin on every rest request. Only those with the correct namespace
         * This restricts endpoints defined in this plugin this namespace
         */
//        require_once( 'includes/dt-hooks.php' );
        $is_rest = dt_is_rest();
        if ( !$is_rest || strpos( dt_get_url_path(), self::$rest_namespace ) !== false ){
          //call functions
        }
        $this->plugin_hooks();
    }

    private function plugin_hooks(){
        add_filter( 'dt_details_additional_tiles', 'dt_details_additional_tiles', 10, 2 );
        function dt_details_additional_tiles( $tiles, $post_type = "" ){
            if ( $post_type === "contacts" ){
                $tiles["coaching_checklist"] = [ "label" => __( "Coaching Checklist", 'disciple_tools' ) ];
            }
            return $tiles;
        }

        add_filter( "dt_custom_fields_settings", "dt_contact_fields", 1, 2 );
        function dt_contact_fields( array $fields, string $post_type = ""){
            if ( $post_type === "contacts" ){
                $options = [
                    "model" => [ "label" => __( "M", 'disciple_tools' ) ],
                    "assist" => [ "label" => __( "A", 'disciple_tools' ) ],
                    "watch" => [ "label" => __( "W", 'disciple_tools' ) ],
                    "leave" => [ "label" => __( "L", 'disciple_tools' ) ],
                ];

                $coaching_checklist_items = [
                    "Duckling Discipleship" => __( "Duckling Discipleship", 'disciple_tools' ),
                    "Tell Your Story (testimony)" => __( "Tell Your Story (testimony)", 'disciple_tools' ),
                    "Tell God's Story (gospel)" => __( "Tell God's Story (gospel)", 'disciple_tools' ),
                    "List of 100" => __( "List of 100", 'disciple_tools' ),
                    "Pace" => __( "Pace", 'disciple_tools' ),
                    "Non-Sequential Ministry" => __( "Non-Sequential Ministry", 'disciple_tools' ),
                    "3/3 Group Format" => __( "3/3 Group Format", 'disciple_tools' ),
                    "Simple Church" => __( "Simple Church", 'disciple_tools' ),
                    "Being Part of Two Churches" => __( "Being Part of Two Churches", 'disciple_tools' ),
                    "Training Cycle" => __( "Training Cycle", 'disciple_tools' ),
                    "Accountability Groups" => __( "Accountability Groups", 'disciple_tools' ),
                    "SOAPS" => __( "SOAPS", 'disciple_tools' ),
                    "Prayer Wheel" => __( "Prayer Wheel", 'disciple_tools' ),
                    "Spiritual Breathing" => __( "Spiritual Breathing", 'disciple_tools' ),
                    "Persecution & Suffering" => __( "Persecution & Suffering", 'disciple_tools' ),
                    "Eyes to See Where the Kingdom Isn't" => __( "Eyes to See Where the Kingdom Isn't", 'disciple_tools' ),
                    "Person of Peace" => __( "Person of Peace", 'disciple_tools' ),
                    "Prayer Walking" => __( "Prayer Walking", 'disciple_tools' ),
                    "Baptism" => __( "Baptism", 'disciple_tools' ),
                    "Lord's Supper" => __( "Lord's Supper", 'disciple_tools' ),
                    "Coaching Checklist" => __( "Coaching Checklist", 'disciple_tools' ),
                    "Leadership Cells" => __( "Leadership Cells", 'disciple_tools' ),
                    "Peer Mentoring Group" => __( "Peer Mentoring Group", 'disciple_tools' ),
                    "Four Fields Tool" => __( "Four Fields Tool", 'disciple_tools' ),
                    "Generational Mapping" => __( "Generational Mapping", 'disciple_tools' ),
                ];
                foreach ( $coaching_checklist_items as $item_key => $item_label ){
                    $fields["coaching_checklist_" . dt_create_field_key( $item_key ) ] = [
                        "name" => $item_label,
                        "default" => $options,
                        "tile" => "coaching_checklist",
                        "type" => "multi_select",
                        "hidden" => true,
                        "custom_display" => true,

                    ];
                }
            }
            return $fields;
        }
        add_action( "dt_details_additional_section", "dt_add_section", 30, 2 );
        function dt_add_section( $section, $post_type ) {
        if ( $section === "coaching_checklist" && $post_type === "contacts" ) {
            $post_fields = DT_Posts::get_post_field_settings( $post_type );
            $post = DT_Posts::get_post( $post_type, get_the_ID() );

            $total_done = 0;
            $total = 0;
            foreach ($post_fields as $field_key => $field_options ) {
                if ( isset( $field_options["tile"] ) && $field_options["tile"] === "coaching_checklist" ) {
                    $total += sizeof( $field_options["default"] );
                    if ( isset( $post[$field_key])){
                      $total_done += sizeof( $post[$field_key]);
                    }
                }
            }
            ?>
            <p><?php esc_html_e( 'Completed', 'disciple_tools' ); ?> <?php echo esc_html( $total_done ); ?>/<?php echo esc_html( $total ); ?></p>
            <?php

            foreach ($post_fields as $field_key => $field_options ) :
                if ( isset( $field_options["tile"] ) && $field_options["tile"] === "coaching_checklist" ) :
                    $post_fields[$field_key]["hidden"] = false;
                    $post_fields[$field_key]["custom_display"] = false;

                    ?>
                    <div style="display: flex">
                        <div style="flex-grow: 1; overflow: hidden; white-space: nowrap; text-overflow: ellipsis">
                            <?php echo esc_html( $field_options["name"] ); ?>
                        </div>
                        <div style="">
                            <div class="small button-group" style="display: inline-block; margin-bottom: 5px">
                                <?php foreach ( $post_fields[$field_key]["default"] as $option_key => $option_value ): ?>
                                    <?php
                                    $class = ( in_array( $option_key, $post[$field_key] ?? [] ) ) ?
                                        "selected-select-button" : "empty-select-button"; ?>
                                  <button id="<?php echo esc_html( $option_key ) ?>" type="button" data-field-key="<?php echo esc_html( $field_key ); ?>"
                                          class="dt_multi_select <?php echo esc_html( $class ) ?> select-button button " style="padding:5px">
                                      <?php echo esc_html( $post_fields[$field_key]["default"][$option_key]["label"] ) ?>
                                  </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif;
                endforeach; ?>

        <?php }
        }
    }
    function dt_plugin_hook_admin_notice() {
        $wp_theme = wp_get_theme();
        $current_version = $wp_theme->version;
        $message = __( "'Disciple Tools - " . self::$plugin_name . "' plugin requires 'Disciple Tools' theme to work. Please activate 'Disciple Tools' theme or make sure it is latest version.", "dt_plugin" );
        if ( strpos( $wp_theme->get_template(), "disciple-tools-theme" ) !== false || $wp_theme->name === "Disciple Tools" ) {
            $message .= sprintf( esc_html__( 'Current Disciple Tools version: %1$s, required version: %2$s', 'dt_plugin' ), esc_html( $current_version ), esc_html( self::$required_dt_theme_version ) );
        }
        $key = dt_create_field_key( self::$plugin_name );
        // Check if it's been dismissed...
        if ( ! get_option( 'dismissed-' . $key, false ) ) { ?>
            <div class="notice notice-error notice-<?php echo esc_html( $key ); ?> is-dismissible" data-notice="<?php echo esc_html( $key ); ?>">
                <p><?php echo esc_html( $message );?></p>
            </div>
            <script>
              jQuery(function($) {
                $( document ).on( 'click', '.notice-<?php echo esc_html( $key ); ?> .notice-dismiss', function () {
                  $.ajax( ajaxurl, {
                    type: 'POST',
                    data: {
                      action: 'dismissed_notice_handler',
                      type: '<?php echo esc_html( $key ); ?>',
                      security: '<?php echo esc_html( wp_create_nonce( 'wp_rest_dismiss' ) ) ?>'
                    }
                  })
                });
              });
            </script>
        <?php }
    }

}
/**
 * AJAX handler to store the state of dismissible notices.
 */
if ( !function_exists( "dt_hook_ajax_notice_handler" )){
    function dt_hook_ajax_notice_handler(){
        check_ajax_referer( 'wp_rest_dismiss', 'security' );
        if ( isset( $_POST["type"] ) ){
            $type = sanitize_text_field( wp_unslash( $_POST["type"] ) );
            update_option( 'dismissed-' . $type, true );
        }
    }
}




