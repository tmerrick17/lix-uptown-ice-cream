<?php
/**
 * Reviews Feed Footer Template
 * Adds pagination and html for errors and resized images
 *
 * @version 1.0 Reviews Feed by Smash Balloon
 *
 */
?>
<?php if ( $this->should_show( 'loadmore' ) ) : ?>
<section class="sb-load-button-ctn">
	<button class="sb-btn sb-load-button sb-btn-small" data-icon-position="left" data-full-width="true" data-onlyicon="true"><span><?php echo esc_html( $this->get_load_button_content() ); ?></span></button>
	<span class="sbr-loader sbr-hidden" style="background-color: rgb(255, 255, 255);" aria-hidden="true"></span>
</section>
<?php endif; ?>