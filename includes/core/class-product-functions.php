<?php
if ( ! defined( 'ABSPATH' ) ) exit;


class wapProductFunctions {

	public function __construct() {

        // Add to  Woocommerce cart Album
        add_action( 'wp_loaded', array( $this, 'wap_add_variations_to_cart' ), 99 );

        // This need for save product
        add_filter( 'woocommerce_data_stores', array( $this, 'wap_data_stores' ) );

        // Create new album variation (track)
        add_action( 'wp_ajax_wap_add_track', array( $this, 'wap_add_track' ) );

        // Remove album variation (track)
        add_action( 'wp_ajax_wap_remove_track', array( $this, 'wap_remove_track' ) );

        // Get all album variations
        add_action( 'wp_ajax_wap_get_tracks', array( $this, 'wap_get_tracks' ) );

        // Action for regenerate cut version of track
        add_action( 'wp_ajax_wap_process_regenerate_cut', array( $this, 'wap_process_regenerate_cut' ) );

        // Create cut version of track
        add_action( 'wap_cut_track', array( $this, 'wap_create_cut_track' ), 10, 1 );

        // Delete cut track if media elemet was deeleted
        add_action( 'delete_attachment', array( $this, 'wap_delete_cut_track' ), 10, 1 );

        // Save different stuff when order created
        add_action('woocommerce_checkout_create_order', array( $this, 'before_checkout_create_order' ), 20, 2);

	}

    public function wap_add_variations_to_cart(){

        global $woocommerce;

        if( isset( $_POST['wap_buy_album'] ) ){
            if( isset( $_POST['product_id'] ) && ( $product = wc_get_product( $_POST['product_id'] ) ) ){

                if( $woocommerce->cart->add_to_cart( esc_attr( $_POST['product_id'] ), 1, '', array(), array() ) ){
                    wc_clear_notices();

                    wc_add_to_cart_message( esc_attr( $_POST['product_id'] ) );

                }
                
            }
        }

        if( isset( $_POST['wap_buy_track_album'] ) ){
            if( isset( $_POST['product_id'] ) && ( $product = wc_get_product( $_POST['product_id'] ) ) ){

                if($woocommerce->cart->add_to_cart( esc_attr( $_POST['product_id'] ), 1, esc_attr( $_POST['variation_id'] ), array(), array() )){
                    wc_clear_notices();

                    wc_add_to_cart_message( esc_attr( $_POST['variation_id'] ) );
                }
                

            }
        }

    }
    

    public function wap_data_stores( $stores ){

        $stores['product-variable_album'] = 'WC_Product_Variable_Data_Store_CPT';
        return $stores;

    } 

    public function wap_get_tracks(){

        if( isset( $_POST['post_id'] ) && get_post_type( $_POST['post_id'] ) == 'product' ){
                
                ob_start();

                do_action( 'get_admin_track_html', esc_attr( $_POST['post_id'] ) );

                $html = ob_get_clean();

                wp_send_json_success( array( 'html' => $html ), 200 );

        }

    }

    public function wap_process_regenerate_cut(){

        if( current_user_can( 'edit_posts' ) && ( isset( $_POST['post_id'] ) && get_post_type( $_POST['post_id'] ) == 'attachment' ) ){

            $this->wap_delete_cut_track( esc_attr( $_POST['post_id'] ) );

            $this->wap_create_cut_track( esc_attr( $_POST['post_id'] ) );
    
            wp_send_json_success( array( 'text' => __( 'Generated', 'wap' ) ), 200 );

        }

        wp_send_json_error( null, 200 );

    }

    public function wap_add_track(){

        if( current_user_can( 'edit_posts' ) && ( isset( $_POST['post_id'] ) && get_post_type( $_POST['post_id'] ) == 'product' ) ){

            $post_data = array(
                'post_title'     => get_the_title( esc_attr( $_POST['post_id'] ) ),
                'post_parent'    => esc_attr( $_POST['post_id'] ),
                'comment_status' => 'closed',
                'post_status'    => 'publish',
                'post_author'    => 1,
                'post_type'      => 'product_variation'
            );

            $post_id = wp_insert_post( $post_data, false );

            if( $post_id ){

                wp_send_json_success( array( 'variation_id' => $post_id ), 200 );

            }else{

                wp_send_json_error( null, 200 );

            }
        }

    }

    public function wap_remove_track(){

        if( current_user_can( 'edit_posts' ) && ( isset( $_POST['post_id'] ) && get_post_type( $_POST['post_id'] ) == 'product_variation' ) ){

            if( $post = wp_delete_post( $_POST['post_id'], true ) ){

                wp_send_json_success( array( 'variation_id' => $post->ID ), 200 );

            }else{

                wp_send_json_error( null, 200 );

            }

        }

    }

    public function wap_create_cut_track( $track_id ){

    	if( !isset( $track_id ) ){
    		return false;
    	}

    	$file_meta = get_post_meta( $track_id, '_wp_attachment_metadata', true );

    	$file_path = esc_attr( get_post_meta( $track_id, '_wp_attached_file', true ) );

    	$uploads = wp_get_upload_dir();

    	if( $file_meta['dataformat'] == 'mp3' ){

    		if( $allready_generated = esc_attr( get_post_meta( $track_id, '_wp_attached_cut_file', true ) ) ){

    			return $allready_generated;

    		}else{


                include_once WAP_LIBS . '/class.mp3.php';

                $mp3 = new PHPMP3( $uploads['basedir'] . "/$file_path" );

    			$new_generated = wp_create_nonce( $track_id ) . '.mp3';

    			if( $mp3_1 = $mp3->extract( 1, get_option( 'wap_cut_length', 31 ) ) ){

                    $mp3_1->save( $uploads['path'] . "/$new_generated" );

    				update_post_meta( $track_id, '_wp_attached_cut_file', $uploads['subdir'] . "/$new_generated" );

    				return true;

    			}

    		}

    	}

    	return false;

    }

    public function wap_delete_cut_track( $post_id ){

        if( !empty( $path = esc_attr( get_post_meta( $post_id, '_wp_attached_cut_file', true ) ) ) ){
            $uploads = wp_upload_dir();

            $del_dir = $uploads['basedir'] . dirname( $path );
            $del_file = $uploads['basedir'] . $path;

            if ( ! wp_delete_file_from_directory( $del_file, $del_dir ) ) {
                return false;
            }

            delete_post_meta( $post_id, '_wp_attached_cut_file' );

            return true;

        }

    }

    public function before_checkout_create_order( $order, $data ) {

        $products = $order->get_items();

        foreach ($products as $value) {
            $product = wc_get_product( $value['product_id'] );

            if( $product->get_type() == 'variable_album' || $product->get_type() == 'album' ){

                $order->update_meta_data( '_order_is_madiable', 'true' );

            }
        }

    }

}

$wapProductFunctions  = new wapProductFunctions();