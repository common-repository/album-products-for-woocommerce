<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class wap_Admin{

    /**
     * Assign everything as a call from within the constructor
     */

    public function __construct() {

        // Add scrips
		add_action( 'admin_enqueue_scripts', array( $this, 'wap_add_admin_JS' ) );
        // Add styles
		add_action( 'admin_enqueue_scripts', array( $this, 'wap_add_admin_CSS' ) );

        // Add settins to Woocommerce product settings page
        add_filter( 'woocommerce_product_settings', array( $this, 'add_length_sample' ) );

        // Add Album product type to Woocommerce
		add_filter( 'product_type_selector', array( $this, 'add_album_type' ) );

        // Add Album product tab selector to Woocommerce
        add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_album_tab' ) );

        // Add Full Album price to general tab
        add_action( 'woocommerce_product_options_general_product_data', array( $this, 'general_tab_album_price' ) );

        // Add Album product tab content
        add_action( 'woocommerce_product_data_panels', array( $this, 'album_product_tab_product_tab_content' ) );

        // Save all stuff 
        add_action( 'woocommerce_process_product_meta', array( $this, 'wap_save_simple_album_meta' ), 99 );

        // Load Album tracks
        add_action( 'get_admin_track_html', array( $this, 'album_admin_template' ), 10, 1 );

        // Add options to WorpdPress media selector
        add_filter( 'attachment_fields_to_edit', array( $this, 'wap_add_attachment_options' ), null, 2 );

    }

	/**
	 *
	 * Adding JavaScript scripts for the admin pages only
	 *
	 * Loading existing scripts from wp-includes or adding custom ones
	 *
	 */

	public function wap_add_admin_JS( $hook ) {

		wp_enqueue_script( 'jquery' );

		wp_register_script( 'wap_script-admin', WAP_URL_FOLDER . 'assets/js/assets-admin.js', array('jquery'), '1.0', true );

		wp_enqueue_script( 'wap_script-admin' );

	}

	/**
	 *
	 * Add admin CSS styles - available only on admin
	 *
	 */

	public function wap_add_admin_CSS( $hook ) {

		wp_register_style( 'wap_style-admin', WAP_URL_FOLDER . 'assets/css/assets-admin.css', array(), '1.0', 'screen' );

		wp_enqueue_style( 'wap_style-admin' );

	}

    public function add_length_sample( $settings ){

        $settings[] = array(
            'title' => __( 'Woo Albums', 'wap' ),
            'type'  => 'title',
            'desc'  => '',
            'id'    => 'wap_options',
        );

        $settings[] = array(
            'title'     => __( 'Track sample length', 'wap' ),
            'desc_tip' => __( 'Length of demo record in album player', 'wap' ),
            'id'       => 'wap_cut_length',
            'type'     => 'text',
            'css'      => 'min-width:300px;',
            'std'      => '30',  // WC < 2.0
            'default'  => '30',  // WC >= 2.0
            'desc'     => __( 'Set it in seconds', 'wap' ),
            );

        $settings[] = array(
            'type' => 'sectionend',
            'id'   => 'wap_options',
        );

        return $settings;
    }

    public function wap_add_attachment_options( $form_fields, $post ){

        if( $post->post_mime_type == "audio/mpeg" ){

            $data = array();

            $data['post_id'] = $post->ID;
            $data['cut_file'] = esc_attr ( get_post_meta( $post->ID, '_wp_attached_cut_file', true ) );

            $form_fields['woo-albums'] = array(
                'label' => __( 'Track sample' , 'wap' ),
                'input' => 'html',
                'html' => $this->get_media_html_content( $data ),
                'show_in_edit'  => true,
                'show_in_modal' => true,
            );

        }

        return $form_fields;
    }

    public function get_media_html_content( $data = array() ){

        ob_start();
        ?>
            <div class="wap_media_contents">
                <table>
                    <tbody>
                        <tr>
                            <td>
                                <strong>
                                    <?php echo __( 'Sample generated', 'wap' ); ?>:
                                </strong>
                            </td>
                            <td class="cut_status">
                                <?php echo ( !empty( $data['cut_file'] ) ? __( 'Yes', 'wap' ) : __( 'No', 'wap' ) ); ?>
                            </td>
                        </tr>

                        <?php do_action( 'wap_get_media_html_content', $data ); ?>

                        <tr>
                            <td colspan="2">
                                <button type="button" onclick="wap_regenerate_cut( jQuery(this) )" data-post_id="<?php echo $data['post_id']; ?>" class="button generate_button"><?php echo( !empty( $data['cut_file'] ) ? __( 'Regenerate sample', 'wap' ) : __( 'Generate sample', 'wap' ) ); ?></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php
        $content = ob_get_clean();

        return $content;
    }

	public function add_album_type( $types ){

        $types[ 'variable_album' ] = __( 'Album', 'wap' );
 
        return $types;

    }

    public function add_album_tab( $tabs ){

        $tabs['variable_album'] = array(
          'label'    => __( 'Tracks', 'wap' ),
          'target' => 'album_variable_product_options',
          'class'  => array( 'show_if_variable_album' ),
         );

        $tabs[ 'shipping' ][ 'class' ][] = 'hide_if_variable_album';

        return $tabs;

    }

    public function general_tab_album_price() {

        global $post;

        ?>

        <div class="options_group show_if_variable_album">

            <?php

            woocommerce_wp_text_input(array(
                  'id' => '_album_regular_price',
                  'label' => sprintf( __( 'Regular price (%s)', 'wap' ), get_woocommerce_currency_symbol() ),
                  'placeholder' => '',
                  'desc_tip' => 'true',
                  'class' => 'short wc_input_price',
                  'type' => 'text',
                  'value' => esc_attr( get_post_meta( $post->ID, '_album_regular_price', true ) )
                )
            );

            woocommerce_wp_text_input(
                array(
                    'id'          => '_album_sale_price',
                    'value'       => esc_attr( get_post_meta( $post->ID, '_album_sale_price', true ) ),
                    'data_type'   => 'price',
                    'label'       => __( 'Sale price', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')',
                    'description' => '<a href="#" class="sale_schedule">' . __( 'Schedule', 'woocommerce' ) . '</a>',
                )
            );

            $sale_price_dates_from = !empty( $date = get_post_meta( $post->ID, '_sale_price_dates_from', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
            $sale_price_dates_to   = !empty( $date = get_post_meta( $post->ID, '_sale_price_dates_to', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';

            echo '<p class="form-field sale_price_dates_fields">
                    <label for="_sale_price_dates_from">' . esc_html__( 'Sale price dates', 'woocommerce' ) . '</label>
                    <input type="text" class="short" name="_album_sale_price_dates_from" id="_album_sale_price_dates_from" value="' . esc_attr( $sale_price_dates_from ) . '" placeholder="' . esc_html( _x( 'From&hellip;', 'placeholder', 'woocommerce' ) ) . ' YYYY-MM-DD" maxlength="10" pattern="' . esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ) . '" />
                    <input type="text" class="short" name="_album_sale_price_dates_to" id="_album_sale_price_dates_to" value="' . esc_attr( $sale_price_dates_to ) . '" placeholder="' . esc_html( _x( 'To&hellip;', 'placeholder', 'woocommerce' ) ) . '  YYYY-MM-DD" maxlength="10" pattern="' . esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ) . '" />
                    <a href="#" class="description cancel_sale_schedule">' . esc_html__( 'Cancel', 'woocommerce' ) . '</a>' . wc_help_tip( __( 'The sale will end at the beginning of the set date.', 'woocommerce' ) ) . '
                </p>';

            ?>
        </div>

        <?php

    }

    public function album_product_tab_product_tab_content(){

            global $post;

        ?>

         <div id='album_variable_product_options' class='panel woocommerce_options_panel'>
            <div class='options_group'>
                <div id="tracks" class="tracks_wrapper">
                    <?php

                        do_action( 'get_admin_track_html', $post->ID );

                    ?>
                </div>
                <div class="tracks_buttons">
                    <button type="button" class="button-primary add_track_element"><?php _e( 'Add Track', 'wap' ); ?></button>
                </div>
             </div>
         </div>

         <?php

    }

    public function wap_save_simple_album_meta( $post_id ){

        if( isset( $_POST['_album_regular_price'] ) && !empty( $_POST['_album_regular_price'] ) ){
            // wp_die(var_dump($_POST['_album_regular_price']));
            update_post_meta( $post_id, '_album_regular_price', esc_attr( $_POST['_album_regular_price'] ) );
            update_post_meta( $post_id, '_regular_price', esc_attr( $_POST['_album_regular_price'] ) );
            update_post_meta( $post_id, '_price', esc_attr( $_POST['_album_regular_price'] ) );

        }

        if( isset( $_POST['_album_sale_price'] ) && !empty( $_POST['_album_sale_price'] ) ){
            update_post_meta( $post_id, '_album_sale_price', esc_attr( $_POST['_album_sale_price'] ) );
            update_post_meta( $post_id, '_price', esc_attr( $_POST['_album_sale_price'] ) );
            update_post_meta( $post_id, '_sale_price', esc_attr( $_POST['_album_sale_price'] ) );
        }

        if( isset( $_POST['_album_sale_price_dates_from'] ) && !empty( $_POST['_album_sale_price_dates_from'] ) ){
            update_post_meta( $post_id, '_album_sale_price_dates_from', strtotime( esc_attr( $_POST['_album_sale_price_dates_from'] ) ) );
            update_post_meta( $post_id, '_sale_price_dates_from', strtotime( esc_attr( $_POST['_album_sale_price_dates_from'] ) ) );
        }

        if( isset( $_POST['_album_sale_price_dates_to'] ) && !empty( $_POST['_album_sale_price_dates_to'] ) ){
            update_post_meta( $post_id, '_album_sale_price_dates_to', strtotime( esc_attr( $_POST['_album_sale_price_dates_to'] ) ) );
            update_post_meta( $post_id, '_sale_price_dates_to', strtotime( esc_attr( $_POST['_album_sale_price_dates_to'] ) ) );
        }

        if( isset( $_POST['_track_variation'] ) ){

            $attributes = array();
            $tracks = array();

            foreach ( $_POST['_track_variation'] as $id => $data ) {

                $updated_data = array(
                    'ID'            => $id,
                    'post_title'    => sanitize_title( $_POST['post_title'] . ' - '. $data['_title'] ),
                    'post_name'      => wc_sanitize_taxonomy_name( sanitize_title( $_POST['post_title'] . ' - '. $data['_title'] ) ),
                    'post_status'   => ( isset( $data['_enabled'] ) ? 'publish' : 'draft' ),
                );

                wp_update_post( $updated_data  );

                update_post_meta( $id, '_variation_description', '' );
                update_post_meta( $id, '_sku', esc_attr( $data['_sku'] ) );
                update_post_meta( $id, '_regular_price', esc_attr( $data['_price'] ) );
                update_post_meta( $id, '_price', esc_attr( $data['_price'] ) );
                update_post_meta( $id, '_product_version', '3.5.0' );
                update_post_meta( $id, '_sale_price', esc_attr( $data['_sale_price'] ) );
                update_post_meta( $id, '_tax_status', 'taxable' );
                update_post_meta( $id, '_tax_class', 'parent' );
                update_post_meta( $id, '_manage_stock', 'no' );
                update_post_meta( $id, '_backorders', 'no' );
                update_post_meta( $id, '_sold_individually', 'no' );
                update_post_meta( $id, '_weight', '' );
                update_post_meta( $id, '_length', '' );
                update_post_meta( $id, '_width', '' );
                update_post_meta( $id, '_height', '' );
                update_post_meta( $id, '_upsell_ids', array() );
                update_post_meta( $id, '_crosssell_ids', array() );
                update_post_meta( $id, '_purchase_note', '' );
                update_post_meta( $id, '_default_attributes', array() );
                update_post_meta( $id, '_virtual', 'yes' );
                update_post_meta( $id, '_downloadable', 'no' );
                update_post_meta( $id, '_product_image_gallery', '' );
                update_post_meta( $id, '_download_limit', '-1' );
                update_post_meta( $id, '_download_expiry', '-1' );
                update_post_meta( $id, '_stock', null );
                update_post_meta( $id, '_stock_status', 'instock' );
                update_post_meta( $id, '_wc_average_rating', '0' );
                update_post_meta( $id, '_wc_rating_count', array() );
                update_post_meta( $id, '_wc_review_count', '0' );
                update_post_meta( $id, '_downloadable_files', array() );
                update_post_meta( $id, '_track_id', esc_attr( $data['_track_id'] ) );
                update_post_meta( $id, '_track_name', esc_attr( $data['_title'] ) );
                update_post_meta( $id, '_track_author_name', esc_attr( $data['_author'] ) );
                update_post_meta( $id, 'attribute_tracks', esc_attr( $data['_title'] ) );

                do_action( 'wap_cut_track', $data['_track_id'] );

              	$tracks[] = $data['_title'];

            }

            $attributes['tracks'] = array(
                'name'         => __( 'Tracks', 'wap' ),
                'value'        => implode( ' | ', esc_attr( $tracks ) ),
                'position'     => '1',
                'is_visible'   => '0',
                'is_variation' => '1',
                'is_taxonomy'  => '0'
            );

            if( isset( $_POST['attribute_names'] ) ){
                foreach ($_POST['attribute_names'] as $key => $value) {
                    if( $value == "Tracks" ){
                        continue;
                    }

                    if( preg_match( '/^pa\_/', $value ) ){
                        $slug = $value;
                        $is_tax = '1';
                    }else{
                        $slug = wc_sanitize_taxonomy_name( $value );
                        $is_tax = '0';
                    }

                    $attributes[$slug] = array(
                        'name'         => $value,
                        'value'        => ( !$is_tax ? esc_attr( $_POST['attribute_values'][$key] ) : '' ),
                        'position'     => esc_attr( $_POST['attribute_position'][$key] ),
                        'is_visible'   => esc_attr( $_POST['attribute_visibility'][$key] ),
                        'is_variation' => '0',
                        'is_taxonomy'  => $is_tax
                    );

                }
            }

            update_post_meta( $post_id, '_product_attributes', $attributes );
            update_post_meta( $post_id, '_virtual', 'yes' );
            update_post_meta( $post_id, '_stock_status', 'instock' );

            wp_set_object_terms( $post_id, 'variable_album', 'product_type', true );

        }

    }

    public function album_admin_template( $post_id ){

    	$arg = array(
            'numberposts' => -1,
            'hierarchical' => false,
            'post_type' => 'product_variation',
            'orderby' => 'id',
            'order'       => 'ASC',
            'post_parent' => $post_id,
        );

        global $thepostid;

        $thepostid = $post_id;

        $variations = get_posts( $arg );
            
        if( $variations ){

            foreach ( $variations as $key => $variation ) {

            	include( WAP_ADMIN . '/templates/html-admin-album-variable.php' );

    		}
        }

    }

}



$wap_Admin = new wap_Admin();