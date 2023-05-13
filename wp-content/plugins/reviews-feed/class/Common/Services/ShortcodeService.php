<?php

namespace SmashBalloon\Reviews\Common\Services;

use SmashBalloon\Reviews\Common\Feed;
use SmashBalloon\Reviews\Common\FeedCache;
use SmashBalloon\Reviews\Common\FeedDisplay;
use SmashBalloon\Reviews\Common\Parser;
use SmashBalloon\Reviews\Common\SBR_Settings;
use Smashballoon\Stubs\Services\ServiceProvider;

class ShortcodeService extends ServiceProvider {

	public function register() {
		add_shortcode('reviews-feed', array( $this, 'render' ) );
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

}
