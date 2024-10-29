<?php
if ( ! defined( 'ABSPATH' ) ) exit;


class wapClass {
    /**
     *
     * Assign everything as a call from within the constructor
     */
    public function __construct() {

        //Add Public Script&Styles
        add_action( 'wp_enqueue_scripts', array( $this, 'wap_add_public_JS' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'wap_add_public_CSS' ) );

        add_action( 'wp_enqueue_scripts', array( $this, 'wap_ajax_connect' ), 99 );

        add_action( 'init', array( $this, 'wap_validate_download_link' ));

        add_filter( 'woocommerce_customer_available_downloads', array( $this, 'customer_available_downloads'), 10, 2 );

        add_shortcode( 'wap_album', array( $this, 'wap_shortcode' ) );

    }

    /**
     * Adding JavaScript scripts
     *
     */
    public function wap_add_public_JS() {

        wp_enqueue_script( 'jquery' );
        // load custom JSes and put them in footer
        wp_register_script( 'wap_public_js', WAP_URL_FOLDER . '/assets/js/woo-albums.js', array('jquery'), '1.0', true );
        wp_enqueue_script( 'wap_public_js' );

    }

    /**
     * Add CSS styles
     *
     */
    public function wap_add_public_CSS() {

        wp_register_style( 'wap_public_scc', WAP_URL_FOLDER . '/assets/css/woo-albums.css', array(), '1.1', 'screen' );
        wp_enqueue_style( 'wap_public_scc' );

    }

    /**
     * Add Ajax Connect
     *
     */
    public function wap_ajax_connect(){

        wp_localize_script('wap_public_js', 'wap_public_connect',
            array(
                'url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('check-this-nonce')
            )
        );

    }

    public function customer_available_downloads($downloads, $customer_id){
        
        global $wap;

        $orders = wc_get_orders( array( 'customer_id' => $customer_id, ) );
        foreach ( $orders as $order){
            $data = $order->get_data();

            if( $data['status'] != 'processing' && $data['status'] != 'completed' )
                continue;

            foreach ($order->get_items() as $item) {
                if( !empty($item->get_variation_id()) ){
                    $download_name = esc_attr( get_post_meta( $item->get_variation_id(), '_track_name', true ) );
                } else {
                    $download_name = esc_attr( get_post_meta( $item->get_product_id(), '_track_name', true ) );
                }
                if( empty($download_name) )
                    $download_name = $item->get_name();

                $downloads_remaining = esc_attr( get_post_meta( $order->get_id(), '_track_name', true ) );

                $downloads[] = array(
                    'download_url'          => $wap->wap_get_downloads_link( $order->get_id() ),
                    'product_name'          => $item->get_name(),
                    'product_url'           => get_permalink( $item->get_product_id() ),
                    'download_name'         => $download_name,
                    'downloads_remaining'   => $downloads_remaining,
                    'access_expires'        => date("Y-m-d H:i:s", ( $data['date_modified']->getTimestamp() + 86400 )),
                );
            }
        }

        return $downloads;

    }

    public function generate_album_archive( $product_id ){

        $product = new WC_Product_Variable( $product_id );

        $product_title = esc_html( $product->name );

        $variations = $product->get_available_variations();
        
        if( $variations ){

            $zip = new ZipArchive;

            if( $zip->open(  get_temp_dir() . '/temp_files.zip', ZipArchive::CREATE) === TRUE ){

                $uploads = wp_get_upload_dir();

                $album_id_image = esc_attr( get_post_meta( $product_id, '_thumbnail_id', true ) );

                if( !empty( $album_id_image ) ){

                    $variation_image_dist = $uploads['basedir'] . '/' . esc_attr( get_post_meta( $album_id_image, '_wp_attached_file', true ) );

                    $zip->addFile( $variation_image_dist, $product_title . substr( $variation_image_dist, strrpos( $variation_image_dist, '.' ) ) );

                }

                foreach ( $variations as $variation ) {

                    $variation_id_file = esc_attr( get_post_meta( $variation['variation_id'], '_track_id', true) );

                    $variation_file_dist = $uploads['basedir'] . '/' .  esc_attr( get_post_meta( $variation_id_file, '_wp_attached_file', true ) );

                    $variation_name = esc_attr( get_post_meta( $variation['variation_id'], '_track_name', true ) ) . '.mp3';

                    if( !empty( $variation_id_file ) && !empty( $variation_file_dist ) ){

                        $zip->addFile( $variation_file_dist, $variation_name );

                    }

                }


                $zip->close();

                $archive_name = 'album_' . $product_title .'.zip';

                header("Content-type: application/zip"); 

                header("Content-Disposition: attachment; filename = $archive_name"); 

                header("Pragma: no-cache"); 

                header("Expires: 0"); 

                readfile( get_temp_dir() . '/temp_files.zip' );

                unlink( get_temp_dir() . '/temp_files.zip' );

                exit;

            }

        }

        return false;

    }

    public function wap_get_downloads_link( $order_id ){

        $order = new WC_Order( $order_id );

        $order_id = 'order_' . $order_id;
        $email = $order->get_billing_email();
        $hash = wp_hash( 'order_id=' . $order_id . '&email=' . $email );

        $link = '/?download_elements=true&request=' . $order_id . '&customer=' . $email . '&key=' . $hash;

        return get_bloginfo( 'url' ) . $link;

    }

    public function wap_validate_download_link(){

        if( isset( $_GET['download_elements'] ) && $_GET['download_elements'] == 'true' ){

            if( isset( $_GET['request'] ) && isset( $_GET['customer'] ) && isset( $_GET['key'] ) ){

                if( wp_hash( 'order_id=' . $_GET['request'] . '&email=' . $_GET['customer'] ) == $_GET['key'] ){

                    $this->wap_add_downloads_links( str_replace( 'order_', '', esc_attr( $_GET['request'] ) ) );

                }else{

                    global $wp_query;

                    $wp_query->set_404();
                    status_header( 404 );
                    get_template_part( 404 );

                    exit();

                }

            }

        }

    }

    public function wap_add_downloads_links( $order_id ){

        $order = new WC_Order( $order_id );

        foreach ($order->get_items() as $key => $value) {

            $product = wc_get_product( $value['product_id'] );

            if( $product->get_type() == 'variable_album' ){

                if( !empty( $value['variation_id'] ) ){

                    $track_id   = esc_attr( get_post_meta( $value['variation_id'], '_track_id', true ) );
                    $track_name =  get_the_title( $value['product_id'] ) . ' - ' . esc_attr( get_post_meta( $value['variation_id'], '_track_name', true ) );

                    //download single track
                    $this->generate_single_track( $track_id, $track_name );

                }else{

                    //download all album
                    $this->generate_album_archive( $value['product_id'] );

                }

            }

            if( $product->get_type() == 'album' ){

                $track_id   = esc_attr( get_post_meta( $value['product_id'], '_track_id', true ) );
                $track_name = esc_attr( get_the_title( $value['product_id'] ) );

                //download single track
                $this->generate_single_track( $track_id, $track_name );

            }

        }

    }

    public function generate_single_track( $track_id, $track_name ){

        $uploads = wp_get_upload_dir();

        $file_destination = $uploads['basedir'] . '/' . esc_attr( get_post_meta( $track_id, '_wp_attached_file', true ) );

        $file_name = esc_html( $track_name ) . '.mp3';

        header("Content-type: audio/mpeg"); 

        header("Content-Disposition: attachment; filename = $file_name");

        header("Pragma: no-cache"); 

        header("Expires: 0"); 

        readfile( $file_destination );

        exit;

    }

    public function wap_shortcode( $atts ) {

        // Attributes
        $atts = shortcode_atts(
            array(
                'product_id' => '0',
            ),
            $atts,
            'wap_album'
        );

        global $product;

        if( ( $product = wc_get_product( $atts['product_id'] ) ) && $atts['product_id'] != '0' ){

            ob_start();

            do_action( 'woocommerce_' . $product->get_type() . '_add_to_cart' );

            $html = ob_get_contents();

            ob_clean();

            return $html;

        }

    }


}

// Initialize everything
global $wap;

$wap = new wapClass();

