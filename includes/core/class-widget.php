<?php

// Creating the widget 
class wapAlbum extends WP_Widget {
	function __construct() {

		parent::__construct(
		'wapAlbum',
		__('Woo Album Widget', 'wap'),
		array( 'description' => __( 'Add Album widget on your page. Make it even better.', 'wap' ), )
		);

	}

	public function widget( $args, $instance ) {
		 
		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
	 
	 	if( isset( $instance['product_id'] ) ){
	 		echo do_shortcode( '[wap_album  product_id="'. $instance['product_id'] .'"]' );
	 	}
		
		echo $args['after_widget'];

	}

	public function form( $instance ) {

		$products = wc_get_products( array( 'limit' => -1, 'type' => array( 'variable_album', 'album' ) ) );

		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'product_id' ) ); ?>"><?php esc_attr_e( 'Select Album Product:', 'wap' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'product_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'product_id' ) ); ?>">

				<?php

				if( $products ){
					echo '<option value="">Select One</option>';
					foreach ( $products as $key => $product ) {
						echo '<option value="'. $product->get_id() .'" '. selected( ( isset( $instance['product_id'] ) ? $instance['product_id'] : 0 ), $product->get_id() ) .' >'. $product->get_title() .'</option>';
					}
				}

				?>

			</select>
		</p>

		<?php

	}

	public function update( $new_instance, $old_instance ) {

		$instance = array();
		$instance['product_id'] = ( ! empty( $new_instance['product_id'] ) ) ? esc_attr( $new_instance['product_id'] ) : '0';

		return $instance;

	}

}