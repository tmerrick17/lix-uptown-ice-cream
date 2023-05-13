<?php
/**
 * Class FeedDisplay
 *
 * @since 1.0
 */
namespace SmashBalloon\Reviews\Pro;


class FeedDisplay extends \SmashBalloon\Reviews\Common\FeedDisplay{
	protected $feed;
	protected $parser;
	protected $translations;

	public function __construct( \SmashBalloon\Reviews\Common\Feed $feed, \SmashBalloon\Reviews\Common\Parser $parser ) {
		$this->feed = $feed;
		$this->parser = $parser;
		$settings = wp_parse_args(get_option('sbr_settings', []), sbr_plugin_settings_defaults());
		$this->translations = $settings['translations'];
	}



	public function media_only( $post ) {
		$settings = $this->feed->get_settings();
		$parser = $this->parser;

		ob_start();
		include sbr_get_feed_template_part( 'post-elements/media', $settings );
		$html = ob_get_contents();
		ob_get_clean();
		return $html;
	}

	public function render_post_elements( $post ) {
		$settings = $this->feed->get_settings();
		$allowed_files = array( 'author', 'text', 'rating', 'media' );

		foreach ( $settings['postElements'] as $file_name ) {
			if ( in_array( $file_name, $allowed_files, true ) ) {
				include sbr_get_feed_template_part( 'post-elements/' . $file_name, $settings );
			}
		}
	}

	public function should_show( $element, $item = '' ) {
		$settings = $this->feed->get_settings();
		if ( $element === 'footer' ) {
			return ! empty( $settings['showFooter'] );
		} elseif ( $element === 'header' ) {
			if ( empty( $item ) ) {
				return ! empty( $settings['showHeader'] );
			}
			if ( in_array( $item, (array) $settings['headerContent'], true ) ) {
				return true;
			}
		} elseif ( $element === 'author' ) {
			if ( in_array( $item, (array) $settings['authorContent'], true ) ) {
				return true;
			}
		} elseif ( $element === 'loadmore' ) {
			if ( empty( $item ) ) {
				return ! empty( $settings['showLoadButton'] );
			}
		}


		return false;
	}

	public function get_header_heading_content() {
		$settings = $this->feed->get_settings();

		if ( ! empty( $settings['headerHeadingContent'] ) ) {
			return $settings['headerHeadingContent'];
		}

		return __( 'Reviews', 'reviews-feed' );
	}

	public function get_load_button_content() {
		$settings = $this->feed->get_settings();

		if ( ! empty( $settings['loadButtonText'] ) ) {
			return $settings['loadButtonText'];
		}

		return __( 'Load More', 'reviews-feed' );
	}


	public function feed_classes() {
		$settings = $this->feed->get_settings();
		$classes = array();
		if ( $settings['layout'] === 'masonry' ) {
			$classes[] = 'sb-cols-' . absint( $settings[ $settings['layout'] . "DesktopColumns"] );
			$classes[] = 'sb-colstablet-' . absint( $settings[ $settings['layout'] . "TabletColumns"] );
			$classes[] = 'sb-colsmobile-' . absint( $settings[ $settings['layout'] . "MobileColumns"] );
		}

		if ( $settings['layout'] === 'carousel' ) {
			$classes[] = 'sb-carousel-wrap';
		}

		if ($settings['layout'] === 'grid') {
			$classes[] = 'sb-grid-wrapper';
		}

		return implode( ' ', $classes );
	}

	public function misc_atts() {
		$atts = '';
		$settings = $this->feed->get_settings();
		$misc_atts = array();
		if ( $settings['layout'] === 'carousel' ) {
			$misc_atts['carousel'] = array(
				$settings['carouselShowArrows'],
				$settings['carouselShowPagination'],
				$settings['carouselEnableAutoplay'],
				$settings['carouselIntervalTime'],
				$settings['carouselLoopType'],
				$settings['carouselDesktopRows'],
			);
		}

		$misc_atts['num'] = array(
			'desktop' => absint( $settings['numPostDesktop'] ),
			'tablet' => absint( $settings['numPostTablet'] ),
			'mobile' => absint( $settings['numPostMobile'] ),
		);

		$misc_atts['flagLastPage'] = $this->feed->is_last_page( 1 );
		$misc_atts['contentLength'] = $settings['contentLength'];

		$atts = ' data-misc="' . esc_attr( wp_json_encode( $misc_atts ) ) . '"';

		if ($settings['layout'] === 'grid') {
			$atts .= ' data-grid-columns="'.esc_attr( $settings['gridDesktopColumns'] ).'" data-grid-tablet-columns="' . esc_attr($settings['gridTabletColumns']) . '" data-grid-mobile-columns="' . esc_attr($settings['gridMobileColumns']) . '" ';
		}

		if ($settings['layout'] === 'carousel') {
			$atts .= ' data-cols="'.esc_attr( $settings['carouselDesktopColumns'] ).' " data-colstablet="'.esc_attr( $settings['carouselTabletColumns'] ).' " data-colsmobile="'.esc_attr( $settings['carouselMobileColumns'] ).' " ';
		}

		if ( $this->feed->should_check_media() ) {
			$atts .= ' data-check-media="'. esc_attr( wp_create_nonce( 'sbr_feed_check_media_' . $this->feed->get_feed_id() ) ).'" ';
		}

		return $atts;
	}

	public function error_html() {
		if ( ! sbr_current_user_can( 'manage_reviews_feed_options' ) ) {
			return '';
		}
		$errors = $this->feed->get_errors();
		if ( empty( $errors ) ) {
			return '';
		}
		ob_start();
		?>
        <div class="sbr-feed-error">
            <span><?php _e('This error message is only visible to WordPress admins', 'reviews-feed' ); ?></span><br />
			<?php foreach ( $errors as $error ) : ?>
                <p><strong><?php echo wp_kses_post( $error['message'] ); ?></strong>
                <p><?php echo wp_kses_post( $error['directions'] ); ?></p>
			<?php endforeach; ?>
        </div>
		<?php
		$html = ob_get_contents();
		ob_get_clean();
		return $html;
	}
}