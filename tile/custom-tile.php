<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class DT_Coaching_Checklist_Tile
{
    private static $_instance = null;
    public static function instance(){
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct(){
        add_filter( 'dt_details_additional_tiles', [ $this, "dt_details_additional_tiles" ], 10, 2 );
        add_filter( "dt_custom_fields_settings", [ $this, "dt_custom_fields" ], 10, 2 );
        add_action( "dt_details_additional_section", [ $this, "dt_add_section" ], 30, 2 );
    }

    /**
     * This function registers a new tile to a specific post type
     *
     * @param $tiles
     * @param string $post_type
     * @return mixed
     */
    public function dt_details_additional_tiles( $tiles, $post_type = "" ) {
        if ( $post_type === "contacts" ){
            $tiles["coaching_checklist"] = [ "label" => __( "Coaching Checklist", 'disciple-tools-coaching-checklist' ) ];
        }
        return $tiles;
    }

    /**
     * @param array $fields
     * @param string $post_type
     * @return array
     */
    public function dt_custom_fields( array $fields, string $post_type = "" ) {
        if ( $post_type === "contacts" ){
            $options = [
                "model" => [ "label" => _x( "H", "Coaching Checklist Initial for: Heard", 'disciple-tools-coaching-checklist' ) ],
                "assist" => [ "label" => _x( "O", "Coaching Checklist Initial for: Obeyed", 'disciple-tools-coaching-checklist' ) ],
                "watch" => [ "label" => _x( "S", "Coaching Checklist Initial for: Shared", 'disciple-tools-coaching-checklist' ) ],
                "leave" => [ "label" => _x( "T", "Coaching Checklist Initial for: Trained", 'disciple-tools-coaching-checklist' ) ],
            ];

            $coaching_checklist_items = [
                "Duckling Discipleship" => _x( "Duckling Discipleship", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                "Tell Your Story (testimony)" => _x( "Tell Your Story (testimony)", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                "Tell God's Story (gospel)" => _x( "Tell God's Story (gospel)", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                "List of 100" => _x( "List of 100", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                "Pace" => _x( "Pace", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                "Non-Sequential Ministry" => _x( "Non-Sequential Ministry", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                "3/3 Group Format" => _x( "3/3 Group Format", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                "Simple Church" => _x( "Simple Church", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                "Being Part of Two Churches" => _x( "Being Part of Two Churches", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                "Training Cycle" => _x( "Training Cycle", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                "Accountability Groups" => _x( "Accountability Groups", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                "SOAPS" => _x( "SOAPS", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                "Prayer Wheel" => _x( "Prayer Wheel", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                "Spiritual Breathing" => _x( "Spiritual Breathing", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                "Persecution & Suffering" => _x( "Persecution & Suffering", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                "Eyes to See Where the Kingdom Isn't" => _x( "Eyes to See Where the Kingdom Isn't", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                "Person of Peace" => _x( "Person of Peace", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                "Prayer Walking" => _x( "Prayer Walking", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                "Baptism" => _x( "Baptism", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                "Lord's Supper" => _x( "Lord's Supper", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                "Coaching Checklist" => _x( "Coaching Checklist", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                "Leadership Cells" => _x( "Leadership Cells", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                "Peer Mentoring Group" => _x( "Peer Mentoring Group", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                "Four Fields Tool" => _x( "Four Fields Tool", "coaching checklist", 'disciple-tools-coaching-checklist' ),
                "Generational Mapping" => _x( "Generational Mapping", "coaching checklist", 'disciple-tools-coaching-checklist' ),
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

    public function dt_add_section( $section, $post_type ) {
        if ( $section === "coaching_checklist" && $post_type === "contacts" ) {
            $post_fields = DT_Posts::get_post_field_settings( $post_type );
            $post = DT_Posts::get_post( $post_type, get_the_ID() );

            $total_done = 0;
            $total = 0;
            foreach ($post_fields as $field_key => $field_options ) {
                if ( isset( $field_options["tile"] ) && $field_options["tile"] === "coaching_checklist" ) {
                    $total += sizeof( $field_options["default"] );
                    if ( isset( $post[$field_key] ) ){
                        $total_done += sizeof( $post[$field_key] );
                    }
                }
            }
            ?>
            <p><?php esc_html_e( 'Completed', 'disciple-tools-coaching-checklist' ); ?> <?php echo esc_html( $total_done ); ?>/<?php echo esc_html( $total ); ?></p>
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
DT_Coaching_Checklist_Tile::instance();
