<?php

/**

 * Plugin Name: Album products for Woocommerce
 * Description: Add ability to sell media Albums
 * Author: Prosvit.Design
 * Author URI: https://prosvit.design/
 * Version: 1.0
 * Text Domain: wap

 */

if ( ! defined( 'ABSPATH' ) ) exit;


define( 'WAP_PATH', dirname( __FILE__ ) );
define( 'WAP_ADMIN', WAP_PATH . '/includes/admin' );
define( 'WAP_CORE', WAP_PATH . '/includes/core' );
define( 'WAP_LIBS', WAP_PATH . '/includes/libs' );
define( 'WAP_LOG', WAP_PATH . '/logs' );
define( 'WAP_URL_FOLDER', plugin_dir_url( __FILE__ ) );


final class wap_start{

    public function __construct() {

    	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    	    include_once( 'includes/core/class-init.php' );

    	    $plugin = plugin_basename(__FILE__); 

    	    add_filter("plugin_action_links_$plugin", array( $this, 'wap_settings' ) );

		}else{
			add_action( 'admin_notices', array( $this, 'woocommerce_is_missing_wc_notice' ) );
		}

    }

    public function woocommerce_is_missing_wc_notice() {

		echo '<div class="error"><p><strong>' . sprintf( esc_html__( '"Album products for Woocommerce" requires WooCommerce to be installed and active. You can download %s here.', 'wap' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';

	}

	public function wap_settings( $links ) {

		$settings_link = '<a href="admin.php?page=wc-settings&tab=products">Settings</a>'; 
		array_unshift($links, $settings_link); 

		return $links; 

	}

}

new wap_start();