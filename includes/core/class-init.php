<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class wap_init{
	function __construct() {

		// All magic actions
        add_action( 'init', array( $this, 'wap_main_actions' ), 1 );

		// Admin 
        add_action( 'init',	array( $this, 'wap_admin_init' ), 2 );

		// Register product types
		add_action( 'init', array( $this, 'wap_product_types' ), 3 );

		// Add templates
		add_action( 'init', array( $this, 'wap_templates_functions' ), 4 );

		// Add Some product functions to show or sawe fields
		add_action( 'init', array( $this, 'wap_product_functions' ), 5 );

		// Add widgets
        add_action( 'widgets_init', array( $this, 'wap_widget' ) );

	}

	/**
     * Initialize the Settings class
     *
     * Register a settings section with a field for a secure WordPress admin option creation.
     */
    public function wap_admin_init() {

        require_once( WAP_ADMIN . '/class-admin-actions.php' );
    }

    /**
	 * Register Produts
	 *
	 */
	public function wap_product_types() {
		require_once( WAP_CORE . '/class-post-type.php' );
	}

    /**
	 * Register Main Actions
	 *
	 */
	public function wap_main_actions() {
		require_once( WAP_CORE . '/class-main.php' );
	}

    /**
     * Add products templates functions
     *
     */
	public function wap_templates_functions(){
		require_once( WAP_CORE . '/class-templates-functions.php' );
	}

    /**
     * Add base product function
     *
     */
	public function wap_product_functions(){
		require_once( WAP_CORE . '/class-product-functions.php' );
	}

    /**
     * Register Widget
     *
     */
    public function wap_widget() {
        require_once( WAP_CORE . '/class-widget.php' );
        register_widget( 'wapAlbum' );
    }

}

$wap_init	=	 new wap_init();