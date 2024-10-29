<?php 
/**
 * Album product add to cart
 * @package album-products/templates/single-product/add-to-cart
 * @version 1.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

?>
<div class="wap_variable_album">
	<div class="wap_variable_album_price_wrapper">
		<table>
			<tbody>
				<tr>
					<td><?php _e( 'Full album price', 'wap' ); ?>: </td>
					<td><?php echo $product->get_album_price(); ?></td>
				</tr>
				<tr>
					<td><?php _e( 'Single track price', 'wap' ); ?>: </td>
					<td><?php echo $product->get_track_price(); ?></td>
				</tr>
			</tbody>
		</table>
	</div>

<form action="" method="POST">
	<input type="hidden" name="wap_buy_album" value="true">
	<input type="hidden" name="product_id" value="<?php echo $product->get_id(); ?>">
	<button type="submit" class="single_add_to_cart_button button alt"><?php _e( 'Buy Full Album', 'wap' ); ?></button>
</form>

<hr>

<h4><?php echo _e( 'Tracklist', 'wap' ); ?></h4>

<?php

$uploads = wp_get_upload_dir();

if( $variations = $product->get_available_variations() ){
	foreach ( $product->get_variation_attributes() as $attribute_name => $options ) : ?>
	
	<form action="" method="POST">
		<input type="hidden" name="wap_buy_track_album" value="true">
		<input type="hidden" name="product_id" value="<?php echo $product->get_id(); ?>">

		<?php

			$attachments = array();

			foreach ($variations as $key => $variation_array) {

				$track_id = get_post_meta( $variation_array['variation_id'], '_track_id', true );

				$variation = new WC_Product_Variation( $variation_array['variation_id'] );
				$attachments[] = array( 
					'track' => get_post( $track_id ), 
					'variation_id' => $variation->get_id(),
					'variation_price' => $variation->get_price_html(),
					'variation_author' => esc_attr( get_post_meta( $variation->get_id(), '_track_author_name', true ) ),
					'variation_name' => esc_attr( get_post_meta( $variation->get_id(), '_track_name', true ) )
				);
			}

			$safe_style = 'light';
			$safe_type = 'audio';
			$outer = 22; // default padding and border of wrapper

			$default_width = 640;
			$default_height = 360;

			$theme_width = empty( $content_width ) ? $default_width : ( $content_width - $outer );
			$theme_height = empty( $content_width ) ? $default_height : round( ( $default_height * $theme_width ) / $default_width );

			  $data = array(
			    'type' => $safe_type,
			    'tracklist' => wp_validate_boolean( true ),
			    'tracknumbers' => wp_validate_boolean( true ),
			    'images' => wp_validate_boolean( true ),
			    'artists' => wp_validate_boolean( true ),
			  );

			  $tracks = array();
			  foreach ( $attachments as $attachment ) {

			    $url = $uploads['baseurl'] . get_post_meta( $attachment['track']->ID, '_wp_attached_cut_file', true);
			    
			    $ftype = wp_check_filetype( $url, wp_get_mime_types() );

			    $track = array(
			      'src' => $url,
			      'type' => $ftype['type'],
			      'title' => ( $attachment['variation_name'] ? $attachment['variation_name'] : $attachment['track']->post_title ),
			      'caption' => $attachment['track']->post_excerpt,
			      'description' => $attachment['track']->post_content,
			      'buy' => array(
			      	'id' => $attachment['variation_id'],
			      	'text' => $attachment['variation_price'],
			      )
			    );

			    $track['meta'] = array();
			    $meta = wp_get_attachment_metadata( $attachment['track']->ID );
			    if ( ! empty( $meta ) ) {

			      foreach ( wp_get_attachment_id3_keys( $attachment['track'] ) as $key => $label ) {

			        if ( ! empty( $meta[ $key ] ) ) {

			        	if( $key == 'artist' ){
			        		$track['meta'][ $key ] = ( $attachment['variation_author'] ? $attachment['variation_author'] : $meta[ $key ] );
			        	}else{
			        		$track['meta'][ $key ] = $meta[ $key ];
			        	}

			        }

			      }

			    }

			      $thumb_id = get_post_thumbnail_id( $attachment['track']->ID );
			      if ( ! empty( $thumb_id ) ) {
			        list( $src, $width, $height ) = wp_get_attachment_image_src( $thumb_id, 'full' );
			        $track['image'] = compact( 'src', 'width', 'height' );
			        list( $src, $width, $height ) = wp_get_attachment_image_src( $thumb_id, 'thumbnail' );
			        $track['thumb'] = compact( 'src', 'width', 'height' );
			      } else {
			        $src = wp_mime_type_icon( $attachment['track']->ID );
			        $width = 48;
			        $height = 64;
			        $track['image'] = compact( 'src', 'width', 'height' );
			        $track['thumb'] = compact( 'src', 'width', 'height' );
			      }

			    $tracks[] = $track;

			  }

			  $data['tracks'] = $tracks;

		?>

		<?php do_action( 'wp_playlist_scripts', $safe_type, $safe_style ); ?>

		<div class="wp-playlist wp-<?php echo $safe_type ?>-playlist wp-playlist-<?php echo $safe_style ?>">

		  <div class="wp-playlist-current-item"></div>

		  <<?php echo $safe_type ?> controls="controls" preload="none" width="<?php
		    echo (int) $theme_width;
		  ?>"></<?php echo $safe_type ?>>
		  <div class="wp-playlist-next"></div>
		  <div class="wp-playlist-prev"></div>
		  <script type="application/json" class="wp-playlist-script"><?php echo wp_json_encode( $data ) ?></script>
		</div>

	</form>
	<?php endforeach; 

}
	
?>
</div>
