<?php

namespace SmashBalloon\Reviews\Pro\Services;

use SmashBalloon\Reviews\Pro\Feed;
use SmashBalloon\Reviews\Common\FeedCache;
use SmashBalloon\Reviews\Pro\FeedDisplay;
use SmashBalloon\Reviews\Pro\Parser;
use SmashBalloon\Reviews\Common\SBR_Settings;


class ShortcodeService extends \SmashBalloon\Reviews\Common\Services\ShortcodeService {

	public function register() {
		add_shortcode('reviews-feed', array( $this, 'render' ) );
		add_action( 'wp_ajax_sbr_load_more_clicked', array( $this, 'get_next_post_set_json' ) );
		add_action( 'wp_ajax_nopriv_sbr_load_more_clicked', array( $this, 'get_next_post_set_json' ) );
		add_action( 'wp_ajax_sbr_check_media', array( $this, 'check_media_listener' ) );
		add_action( 'wp_ajax_nopriv_sbr_check_media', array( $this, 'check_media_listener' ) );
	}

	public function render( $atts = array() ) {
		$feed_id = ! empty( $atts['feed'] ) ? $atts['feed'] : 0;
		$settings = SBR_Settings::get_settings_by_feed_id( $feed_id );

		do_action( 'sbr_before_shortcode_render', $settings );

		$feed = new Feed( $settings, $feed_id, new FeedCache( $feed_id, 2 * DAY_IN_SECONDS ) );

		$feed->init();
		if ( ! empty( $feed->get_errors() ) ) {
			$feed_display = new FeedDisplay( $feed, new Parser() );
			return $feed_display->error_html();
		}
		$feed->get_set_cache();

		$feed_display = new FeedDisplay( $feed, new Parser() );

		return $feed_display->with_wrap();
	}

	public function get_next_post_set_json() {
		$feed_id = ! empty( $_POST['feed_id'] ) ? sanitize_key( $_POST['feed_id'] ) : 0;
		$next_page = ! empty( $_POST['next_page'] ) ? intval( $_POST['next_page'] ) : 2;
		$settings = SBR_Settings::get_settings_by_feed_id( $feed_id );

		if ( empty( $settings['sources'] ) ) {
			return 'no sources';
		}
		$feed = new Feed( $settings, $feed_id, new FeedCache( $feed_id, 3000000 ) );
		$feed->init();
		$feed->get_set_cache();

		if ( $feed->should_check_media() ) {
			self::check_media( $feed );
		}

		$feed_display = new FeedDisplay( $feed, new Parser() );

		$data = array(
			'html' => $feed_display->items_only( $next_page ),
			'is_last_page' => $feed->is_last_page( $next_page )
		);

		wp_send_json_success( $data );
	}

	public function check_media_listener() {
		$feed_id = ! empty( $_POST['feed_id'] ) ? sanitize_key( $_POST['feed_id'] ) : 0;

		if ( empty( $feed_id ) ) {
			wp_send_json_error( 'feed ID' );
		}

		$nonce = ! empty( $_POST['nonce'] ) ? sanitize_key( $_POST['nonce'] ) : 0;

		if ( ! wp_verify_nonce( $nonce, 'sbr_feed_check_media_' . $feed_id ) ) {
			wp_send_json_error( 'nonce' );

		}

		$settings = SBR_Settings::get_settings_by_feed_id( $feed_id );
		if ( empty( $settings['sources'] ) ) {
			wp_send_json_error();
		}

		$feed = new Feed( $settings, $feed_id, new FeedCache( $feed_id, 3000000 ) );
		$feed->init();

		$return = self::check_media( $feed );

		wp_send_json_success( $return );
	}

	/**
	 * @param Feed $feed
	 */
	public static function check_media( $feed ) {
		$posts = $feed->get_posts_for_media_finding_and_resizing();

		$parser = new Parser();
		$feed_display = new FeedDisplay( $feed, $parser );
		$just_resized = $feed->find_and_resize_media( $posts );
		$return = array();
		foreach ( $just_resized as $single_post ) {
			$return[ $parser->get_id( $single_post ) ] = $feed_display->media_only( $single_post );
		}
		$to_cache = $feed->posts_from_db();
		$feed->update_cache( $to_cache );

		return $return;
	}
}
