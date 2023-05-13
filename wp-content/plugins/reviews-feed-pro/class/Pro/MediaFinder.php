<?php
/**
 * Class MediaFinder
 *
 * @since 1.0
 */
namespace SmashBalloon\Reviews\Pro;

class MediaFinder {

	private $post;

	private $source;

	private $provider;

	private $primary_url;

	public function __construct( $source ) {
		$this->source = $source;
	}

	public function set_post( $post ) {
		$this->post = $post;
	}

	public function set_provider( $provider ) {
		$this->provider = $provider;
	}

	public function construct_url_from_post_and_source() {
		if ( $this->provider === 'yelp' ) {
			$slug = $this->parse_yelp_source_slug();
			$user_id = $this->parse_yelp_reviewer_id();

			if ( $user_id ) {
				$maybe_image_page = 'https://www.yelp.com/biz_photos/' . $slug . '?userid=' . $user_id;
				$this->primary_url = $maybe_image_page;
			}

		} elseif ( $this->provider === 'tripadvisor' ) {
			$this->primary_url = $this->parse_tripadvisor_source_url();
		}
	}

	public function search() {
		$response = $this->make_request();

		if ( ! is_wp_error( $response ) ) {
			$html = wp_remote_retrieve_body( $response );
			return $this->find_media( $html );
		}

		return array();
	}

	public function make_request() {
		if ( empty( $this->primary_url ) ) {
			return new \WP_Error( '999', 'No URL set' );
		}
		$args = array(
			'timeout' => 5,
		);

		if ( $this->provider === 'yelp' ) {
			$args['user-agent'] = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
		}

		return wp_remote_get( $this->primary_url, $args );
	}

	public function find_media( $html ) {
		$return = array();

		libxml_use_internal_errors(true);
		//parsing begins here:
		$doc = new \DOMDocument();
		if ( ! empty( $html ) ) {
			@$doc->loadHTML( $html );

			if ( $this->provider === 'yelp' ) {
				$lists = $doc->getElementsByTagName( 'li' );
				for ( $i = 0; $i < $lists->length; $i++ ) {
					$list = $lists->item( $i );
					$maybe_photo_id = $list->getAttribute( 'data-photo-id' );

					if ( ! empty( $maybe_photo_id ) ) {
						$photo_source = 'https://s3-media0.fl.yelpcdn.com/bphoto/' . sanitize_text_field( $maybe_photo_id ) . '/o.jpg';
						$return[] = array(
							'type' => 'image',
							'url'  => $photo_source
						);
					}
				}
			} elseif ( $this->provider === 'tripadvisor' ) {
				$divs = $doc->getElementsByTagName( 'div' );
				for ( $i = 0; $i < $divs->length; $i++ ) {
					$div = $divs->item( $i );
					$maybe_review_id = $div->getAttribute( 'id' );
					$maybe_class = $div->getAttribute( 'class' );
					$review_id = $this->parse_tripadvisor_review_id();
					if ( $review_id && (string) $maybe_review_id === 'review_' . $review_id && $maybe_class === 'reviewSelector' ) {
						$sub_doc = new \DOMDocument();
						@$sub_doc->loadHTML( $div->C14N() );
						$imgs = $sub_doc->getElementsByTagName( 'img' );
						for ( $ii = 0; $ii < $imgs->length; $ii++ ) {
							$img = $imgs->item( $ii );
							$maybe_media_id = $img->getAttribute( 'data-mediaid' );

							if ( ! empty( $maybe_media_id ) ) {
								$photo_source = $img->getAttribute( 'src' );
								if ( strpos( $photo_source, 'https://media-cdn.tripadvisor.com/') === 0 ) {
									$return[] = array(
										'type' => 'image',
										'url'  => str_replace( '/photo-l/', '/photo-o/', $photo_source ) // get the full size image
									);
								}
							}
						}
					}
				}
			}
		}
		libxml_clear_errors();
		libxml_use_internal_errors(false);

		return $return;
	}

	public function parse_yelp_source_slug() {
		$res = explode( '/', $this->source['url'] );

		$raw_slug = ! empty( $res[ 4 ] ) ? $res[ 4 ] : '';

		return explode( '?', $raw_slug )[0];
	}

	public function parse_yelp_reviewer_id() {
		return ! empty( $this->post['reviewer']['id'] ) ? $this->post['reviewer']['id'] : false;
	}

	public function parse_tripadvisor_review_id() {
		return ! empty( $this->post['review_id'] ) ? $this->post['review_id'] : false;
	}

	public function parse_tripadvisor_source_url() {
		return ! empty( $this->source['url'] ) ? $this->source['url'] : false;
	}
}
