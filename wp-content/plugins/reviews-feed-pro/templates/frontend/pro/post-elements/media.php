<?php
/**
 * Smash Balloon Reviews Feed Media Template
 * Adds images if one exists
 *
 * @version 1.0 Reviews Feed by Smash Balloon
 *
 */

$media = $this->parser->get_media( $post );
if ( ! empty( $media ) ) : ?>
	<div class="sb-media-wrap sb-fs">
		<?php foreach ( $media as $single_image ) : ?>
			<a class="sb-single-image" href="<?php echo esc_url( $this->parser->get_media_url( $single_image, 640 ) ); ?>" target="_blank" rel="noopener">
				<img src="<?php echo esc_url( $this->parser->get_media_url( $single_image ) ); ?>" alt="review image" />

				<div class="sb-thumbnail-hover">
				</div>
			</a>
		<?php endforeach; ?>
	</div>
<?php else : ?>
	<span class="sb-media-placeholder"></span>
<?php endif; ?>
