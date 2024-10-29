<?php

/**
 * Display album tab on Woocommerce product page
 * @package album-products/includes/admin/templates
 * @version 1.0
 */
?>
<div id="variation_id_<?php echo $variation->ID; ?>" data-variation_id="<?php echo $variation->ID; ?>" class="variation_wrapper closed">
    <input type="hidden" name="_track_variation[<?php echo $variation->ID; ?>][_id]" value="<?php echo $variation->ID; ?>">
    <h3>
        <span class="tack_title"><?php esc_attr_e( get_post_meta( $variation->ID, '_track_name', true ) ? get_post_meta( $variation->ID, '_track_name', true ) : __( 'No title' , 'wap' ) ); ?>
            
            	<?php echo ( empty( get_post_meta( $variation->ID, '_track_id', true ) ) ? '<span class="no_media_added">' . __( 'No media added', 'wap' ) . '</span>' : '' ); ?>
        </span>
        <div class="track_wrapper_actions">
        	<span class="open_track_wrapper"></span>
        	<a href="#" class="remove_this_track" rel="<?php echo $variation->ID; ?>"><?php _e( 'Remove' , 'wap' ); ?></a>
        </div>
    </h3>
    <div class="variation_inner_wrapper">
        <div class="track_checkbox">
            <div class="flex_box">
                <label>
                    Enabled:
                    <input type="checkbox" class="checkbox" name="_track_variation[<?php echo $variation->ID; ?>][_enabled]" <?php checked( in_array( $variation->post_status, array( 'publish', false ), true ), true ) ?>>
                </label>
            </div>
        </div>
        <div class="wap_include_file">
            <div class="flex_box">
                <?php

                woocommerce_wp_text_input(
                array( 
                    'id'    => '_track_variation[' . $variation->ID . '][_track_id]',
                    'label'       => __( 'Media ID', 'wap' ),
                    'placeholder' => __( 'Media ID', 'wap' ),
                    'class' => 'wap_track_id',
                    'value' => esc_attr( get_post_meta( $variation->ID, '_track_id', true ) )
                    )
                );

                ?>
                <p class="form-field">
                	<a href="#" class="button" data-track_id="<?php esc_attr_e( get_post_meta( $variation->ID, '_track_id', true ) ); ?>" onclick="add_track_file( jQuery(this) ); return false;"><?php _e( 'Add File' , 'wap' ); ?></a>
                </p>
            </div>
        </div>
        <div class="wap_titles">
            <div class="flex_box">
            <?php

                woocommerce_wp_text_input(
                array( 
                    'id'    => '_track_variation[' . $variation->ID . '][_title]', 
                    'label'       => __( 'Track name', 'wap' ),
                    'placeholder' => __( 'Track name', 'wap' ),
                    'class' => 'track_variation_title',
                    'value' => esc_attr( get_post_meta( $variation->ID, '_track_name', true ) )
                    )
                );

                woocommerce_wp_text_input(
                array( 
                    'id'    => '_track_variation[' . $variation->ID . '][_author]', 
                    'label'       => __( 'Track Author', 'wap' ),
                    'placeholder' => __( 'Track Author', 'wap' ),
                    'class' => 'track_variation_author',
                    'value' => esc_attr( get_post_meta( $variation->ID, '_track_author_name', true ) )
                    )
                );

            ?>
            </div>
        </div>
        <div class="wap_prices">
            <div class="flex_box">
                <?php

                woocommerce_wp_text_input(
                array( 
                    'id'    => '_track_variation[' . $variation->ID . '][_price]', 
                    'label'       => sprintf( __( 'Track price (%s)', 'wap' ), get_woocommerce_currency_symbol() ),
                    'placeholder' => sprintf( __( 'Track price (%s)', 'wap' ), get_woocommerce_currency_symbol() ),
                    'class' => 'track_variation_price wc_input_price',
                    'value' => esc_attr( get_post_meta( $variation->ID, '_regular_price', true ) )
                    )
                );

                woocommerce_wp_text_input(
                array( 
                    'id'    => '_track_variation[' . $variation->ID . '][_sale_price]', 
                    'label'       => sprintf( __( 'Track sale price (%s)', 'wap' ), get_woocommerce_currency_symbol() ),
                    'placeholder' => sprintf( __( 'Track sale price (%s)', 'wap' ), get_woocommerce_currency_symbol() ),
                    'class' => 'track_variation_sale_price wc_input_price',
                    'value' => esc_attr( get_post_meta( $variation->ID, '_sale_price', true ) )
                    )
                );

            ?>
            </div>
        </div>
        <div class="wap_sku">
            <?php

               woocommerce_wp_text_input(
                array( 
                    'id'    => '_track_variation[' . $variation->ID . '][_sku]', 
                    'label'       => __( 'Track SKU', 'wap' ),
                    'placeholder' => __( 'Track SKU', 'wap' ),
                    'class' => 'track_variation_sale_price wc_input_price',
                    'value' => esc_attr( get_post_meta( $variation->ID, '_sku', true ) )
                    )
                );

            ?>
        </div>

        <?php do_action( 'album_after_main_content', $variation->ID ); ?>

    </div>
</div>