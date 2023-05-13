<?php
/**
 * Class SinglePostCache
 *
 * @since 1.0
 */
namespace SmashBalloon\Reviews\Common;

class PostAggregator {

	public const UPLOAD_FOLDER_NAME = 'sbr-feed-images';
	public const POSTS_TABLE_NAME = 'sbr_reviews_posts';

	/**
	 * @var array
	 */
	private $post_set = array();

	private $upload_dir;

	private $upload_url;

	private $missing_media_found;

	public function __construct() {
		$upload = wp_upload_dir();
		$upload_dir = $upload['basedir'];
		$upload_dir = trailingslashit( $upload_dir ) . self::UPLOAD_FOLDER_NAME;
		$this->upload_dir = $upload_dir;

		$upload_url = trailingslashit( $upload['baseurl'] ) . self::UPLOAD_FOLDER_NAME;
		$this->upload_url = $upload_url;
		$this->missing_media_found = false;
	}

	public function missing_media_found() {
		return $this->missing_media_found;
	}

	public function add( $post_set ) {
		return $this->post_set = array_merge( $this->post_set, $post_set );
	}

	public function db_post_set( $requests_needed ) {
		global $wpdb;
		$table_name = esc_sql( $wpdb->prefix . self::POSTS_TABLE_NAME );

		$where_clause = $this->build_provider_business_where_clause( $requests_needed );

		$results = $wpdb->get_results(
			"SELECT * FROM $table_name
					WHERE $where_clause ORDER BY time_stamp DESC LIMIT 150", ARRAY_A );

		return $results;
	}

	public function db_posts_for_media_finding_and_resizing_set( $requests_needed ) {
		global $wpdb;
		$table_name = esc_sql( $wpdb->prefix . self::POSTS_TABLE_NAME );

		$where_clause = $this->build_provider_business_where_clause( $requests_needed );

		$results = $wpdb->get_results(
			"SELECT * FROM $table_name
					WHERE $where_clause
					AND images_done = 0
					ORDER BY time_stamp DESC LIMIT 150", ARRAY_A );

		return $results;
	}

	public function normalize_db_post_set( $results ) {
		$normalized_set = array();
		foreach ( $results as $result ) {
			if ( ! empty( $result['json_data'] ) ) {
				$post = json_decode( $result['json_data'], true );
				if ( ! empty( $post ) ) {
					$post = self::add_local_image_urls( $post, $result );
				}
				if ( (int)$result['images_done'] === 0 ) {
					$this->missing_media_found = true;
				}
				$normalized_set[] = $post;
			}
		}

		return $normalized_set;
	}

	public function add_local_image_urls( $post, $result ) {
		$return     = $post;
		$base_url   = $this->upload_url;
		$resize_url = apply_filters( 'sbr_resize_url', trailingslashit( $base_url ) );

		if ( ! empty( $post['reviewer']['avatar'] ) ) {
			if ( ! empty( $result['avatar_id'] ) && $result['avatar_id'] !== 'error' ) {
				$return['reviewer']['avatar_local'] = $resize_url . $result['avatar_id']. '.png';
			}
		}

		if ( ! empty( $post['media'] ) ) {
			$sizes = ! empty( $result['sizes'] ) ? json_decode( $result['sizes'] ) : array();
			$i     = 0;
			foreach ( $post['media'] as $single_image ) {
				if ( ! empty( $result['media_id'] ) && $result['media_id'] !== 'error' ) {
					$return['media'][ $i ]['local'] = array();
					$media_id                       = $result['media_id'];
					foreach ( $sizes as $size ) {
						$local_url_for_size = $resize_url . $media_id . '-' . $i . '-' .  $size . '.jpg';
						$return['media'][ $i ]['local'][ $size ] = $local_url_for_size;
					}
				}
				$i ++;
			}
		}

		return $return;
	}

	public function update_last_requested( $requests_needed ) {
		global $wpdb;
		$table_name = esc_sql( $wpdb->prefix . self::POSTS_TABLE_NAME );
		$where_clause = $this->build_provider_business_where_clause( $requests_needed );
		$query = $wpdb->query(
			$wpdb->prepare(
				"UPDATE $table_name
					SET last_requested = %s
					WHERE $where_clause;",
				date( 'Y-m-d' )

			)
		);

	}

	private function build_provider_business_where_clause( $requests_needed ) {

		$i            = 1;
		$where_clause = '';
		foreach ( $requests_needed as $request ) {
			$single_clause = sprintf( 'provider_id = "%s"', esc_sql( $request['account_id'] ) );
			if ( isset( $request['lang'] ) ) {
				$single_clause.= sprintf( ' AND lang = "%s"', esc_sql( $request['lang'] ) );
			}
			$where_clause .= '(' . $single_clause . ')';
			if ( $i <  count( $requests_needed ) ) {
				$where_clause .= ' OR ';
			}
			$i ++;
		}

		return $where_clause;
	}
}
