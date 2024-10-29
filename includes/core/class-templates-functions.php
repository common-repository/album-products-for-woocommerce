<?php
if ( ! defined( 'ABSPATH' ) ) exit;


class wapTemplatesFunctions {


	public function __construct() {

        // Load template for Album product
		add_action( 'woocommerce_variable_album_add_to_cart', array( $this, 'woocommerce_variable_album_add_to_cart' ) );

        // Load teamplate to show on woocommerce thank you page
		add_action( 'woocommerce_thankyou', array( $this, 'wap_print_downloads_links' ) );

        // Change default templates for WordPress audio player
		add_action( 'wp_playlist_scripts', array( $this, 'edited_playlist_template' ), 20 );

	}


    public function woocommerce_variable_album_add_to_cart(){

		global $product;
		
		if( $product->get_type() == 'variable_album' ){
			wc_get_template( 'single-product/add-to-cart/variable_album.php', 
				array(), 
				'',
				WAP_PATH . '/templates/'
			);
		}
		
    }

    public function wap_print_downloads_links( $order_id ){

    	$order = new WC_Order( $order_id );

    	if( $order->get_status() != 'processing' && $order->get_status() != 'completed' )
                return false;

        if( !empty( get_post_meta( $order_id, '_order_is_madiable', true) ) ){

        	global $wap;

        	wc_get_template( 'checkout/download_album.php', 
				array( 'download_link' => $wap->wap_get_downloads_link( $order_id ) ), 
				'',
				WAP_PATH . '/templates/'
			);

        }

    }

	public function edited_playlist_template(){

        global $product;
        
        if( $product ){

            // Unhook default templates.
            remove_action( 'wp_footer', 'wp_underscore_playlist_templates', 0 );
            remove_action( 'admin_footer', 'wp_underscore_playlist_templates', 0 );

            // Hook in new templates.
            add_action( 'wp_footer', array( $this, 'wp_edited_underscore_playlist_templates' ), 0 );
            add_action( 'admin_footer', array( $this, 'wp_edited_underscore_playlist_templates' ), 0 );

        }

    }

    public function wp_edited_underscore_playlist_templates() {
        ?>
        <script type="text/html" id="tmpl-wp-playlist-current-item">
            <# if ( data.image ) { #>
            <img src="{{ data.thumb.src }}" alt="" />
            <# } #>
            <div class="wp-playlist-caption">
                <span class="wp-playlist-item-meta wp-playlist-item-title"><?php
                    /* translators: playlist item title */
                    printf( _x( '&#8220;%s&#8221;', 'playlist item title' ), '{{ data.title }}' );
                ?></span>
                <# if ( data.meta.album ) { #><span class="wp-playlist-item-meta wp-playlist-item-album">{{ data.meta.album }}</span><# } #>
                <# if ( data.meta.artist ) { #><span class="wp-playlist-item-meta wp-playlist-item-artist">{{ data.meta.artist }}</span><# } #>
            </div>
        </script>
        <script type="text/html" id="tmpl-wp-playlist-item">
            <div class="wp-playlist-item">
                <a class="wp-playlist-caption" href="{{ data.src }}">
                    {{ data.index ? ( data.index + '. ' ) : '' }}
                    <# if ( data.caption ) { #>
                        {{ data.caption }}
                    <# } else { #>
                        <span class="wp-playlist-item-title"><?php
                            /* translators: playlist item title */
                            printf( _x( '&#8220;%s&#8221;', 'playlist item title' ), '{{{ data.title }}}' );
                        ?></span>
                        <# if ( data.artists && data.meta.artist ) { #>
                        <span class="wp-playlist-item-artist"> &mdash; {{ data.meta.artist }}</span>
                        <# } #>
                    <# } #>
                </a>
                <div class="wp-playlist-item-length">
                    <button type="submit" class="album_submit btn" onclick="album_submit(jQuery(this));" name="variation_id" value="{{ data.buy.id }}">
                        {{{ data.buy.text }}}
                    </button>
                </div>
            </div>
        </script>
        <?php
    }

}

$wapTemplatesFunctions = new wapTemplatesFunctions();