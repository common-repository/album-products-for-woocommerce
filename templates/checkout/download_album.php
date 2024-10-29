<?php 
/*
 * Display link to download media
 * @package album-products/templates/checkout
 * @version 1.0
*/

?>

<div class="wap_generated_links_wrapper">
    <h2><?php echo __( 'Now you can download your File here:', 'wap'); ?></h2>
    <a href="<?php echo esc_url( $download_link ); ?>" class="wap_generated_link"><?php echo __( 'Download', 'wap'); ?></a>
</div>