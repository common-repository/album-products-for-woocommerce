<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WC_Product_Variable_Album extends WC_Product_Variable {

	public function __construct( $product = 0 ) {

        $this->product_type = 'variable_album';

        parent::__construct( $product );

    }

	public function get_type() {

		return 'variable_album';

	}

	public function is_type( $type ) {

		if ( 'variable' == $type || ( is_array( $type ) && in_array( 'variable', $type ) ) ) {

			return true;

		} else {

			return parent::is_type( $type );

		}

	}

	public function get_regular_price( $context = 'view' ) {

		return esc_attr( get_post_meta( $this->get_id(), '_regular_price', true ) );

	}

	public function get_sale_price( $context = 'view' ) {

		return esc_attr( get_post_meta( $this->get_id(), '_sale_price', true ) );

	}
	public function get_track_price(){

		$variations_price = $this->get_variation_prices();

		$min = min( $variations_price['price'] );

		$max = max( $variations_price['price'] );

		if( $max == $min ){
			$price = wc_price( $max );
		}else{
			$price = wc_price( $min ) . ' - ' . wc_price( $max );
		}

		return $price;

	}

	public function get_album_price(){

		if ( '' === $this->get_price() ) {

			$price = apply_filters( 'woocommerce_empty_price_html', '', $this );

		} elseif ( $this->is_on_sale() ) {

			$price = wc_format_sale_price( wc_get_price_to_display( $this, array( 'price' => $this->get_regular_price() ) ), wc_get_price_to_display( $this ) ) . $this->get_price_suffix();

		} else {

			$price = wc_price( wc_get_price_to_display( $this ) ) . $this->get_price_suffix();

		}

		return $price;

	}

	public function is_on_sale( $context = 'view' ) {

		if ( '' !== (string) $this->get_sale_price( $context ) && $this->get_regular_price( $context ) > $this->get_sale_price( $context ) ) {
			$on_sale = true;

			if ( $this->get_date_on_sale_from( $context ) && $this->get_date_on_sale_from( $context )->getTimestamp() > current_time( 'timestamp', true ) ) {
				$on_sale = false;
			}

			if ( $this->get_date_on_sale_to( $context ) && $this->get_date_on_sale_to( $context )->getTimestamp() < current_time( 'timestamp', true ) ) {
				$on_sale = false;
			}
		} else {
			$on_sale = false;
		}

		return 'view' === $context ? apply_filters( 'woocommerce_product_is_on_sale', $on_sale, $this ) : $on_sale;

	}

	public function get_price_html( $price = '' ) {
		ob_start();
		?>

		<div class="_album_price">
			<?php echo $this->get_album_price(); ?>
		</div>

		<div class="_track_price">
			&#040;
			<?php echo $this->get_track_price(); ?>
			&#041;
		</div>

		<?php

		$price = ob_get_clean();

		return apply_filters( 'woocommerce_get_price_html', $price, $this );

	}

}