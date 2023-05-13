<?php
/**
 * Reviews Feed Saver Manager
 *
 * @since 1.0
 */

namespace SmashBalloon\Reviews\Common\Builder;

use SmashBalloon\Reviews\Common\Customizer\DB;
use SmashBalloon\Reviews\Common\Integrations\Providers\Google;
use SmashBalloon\Reviews\Common\Integrations\Providers\Yelp;
use SmashBalloon\Reviews\Common\Integrations\SBRelay;
use SmashBalloon\Reviews\Common\Services\SettingsManagerService;
use SmashBalloon\Reviews\Common\Traits\SBR_Feed_Templates_Settings;
use SmashBalloon\Reviews\Common\Util;
use SmashBalloon\Reviews\Common\FeedCache;


class SBR_Feed_Saver_Manager {

    use SBR_Feed_Templates_Settings;
    /**
     * AJAX hooks for various feed data related functionality
     *
     * @since 1.0
     */
    public static function register(){
        add_action('wp_ajax_sbr_feed_saver_manager_builder_update', array('SmashBalloon\Reviews\Common\Builder\SBR_Feed_Saver_Manager', 'builder_update'));
        add_action('wp_ajax_sbr_feed_saver_manager_duplicate_feed', array('SmashBalloon\Reviews\Common\Builder\SBR_Feed_Saver_Manager', 'duplicate_feed'));
        add_action('wp_ajax_sbr_feed_saver_manager_delete_feeds', array('SmashBalloon\Reviews\Common\Builder\SBR_Feed_Saver_Manager', 'delete_feed'));
        add_action('wp_ajax_sbr_feed_saver_manager_fly_preview', array('SmashBalloon\Reviews\Common\Builder\SBR_Feed_Saver_Manager', 'feed_customizer_fly_preview'));
        add_action('wp_ajax_sbr_feed_saver_manager_start_moderation_mode', array('SmashBalloon\Reviews\Common\Builder\SBR_Feed_Saver_Manager', 'start_moderation_mode'));


        add_action('wp_ajax_sbr_feed_saver_manager_add_source', array('SmashBalloon\Reviews\Common\Builder\SBR_Feed_Saver_Manager', 'add_source'));
        add_action('wp_ajax_sbr_feed_saver_manager_add_facebook_souce', array('SmashBalloon\Reviews\Common\Builder\SBR_Feed_Saver_Manager', 'add_facebook_souce'));
        add_action('wp_ajax_sbr_feed_saver_manager_connect_manual_facebook', array('SmashBalloon\Reviews\Common\Builder\SBR_Feed_Saver_Manager', 'add_manual_facebook_souce'));
        add_action('wp_ajax_sbr_feed_saver_manager_delete_source', array('SmashBalloon\Reviews\Common\Builder\SBR_Feed_Saver_Manager', 'delete_souce'));
        add_action('wp_ajax_sbr_feed_saver_manager_update_api_key', array('SmashBalloon\Reviews\Common\Builder\SBR_Feed_Saver_Manager', 'update_api_key'));
		add_action('wp_ajax_sbr_import_feed_settings', array('SmashBalloon\Reviews\Common\Builder\SBR_Feed_Saver_Manager', 'import_feed_settings'));
		add_action('wp_ajax_sbr_clear_all_caches', array('SmashBalloon\Reviews\Common\Builder\SBR_Feed_Saver_Manager', 'clear_all_caches'));

		add_action( 'wp_ajax_sbr_feed_saver_manager_get_feed_list_page', array( 'SmashBalloon\Reviews\Common\Builder\SBR_Feed_Saver_Manager', 'get_feed_list_page' ) );

    }

    /**
     * Used in an AJAX call to update settings for a particular feed.
     * Can also be used to create a new feed if no feed_id sent in
     * $_POST data.
     *
     * @since 1.0
     */
    public static function builder_update(){

        check_ajax_referer('sbr-admin', 'nonce');

        if ( ! sbr_current_user_can('manage_reviews_feed_options') ) {
            wp_send_json_error();
        }

        $settings_data = $_POST;

        $feed_id = false;
        $is_new_feed = isset($settings_data['new_insert']) ? true : false;
        if (!empty($settings_data['feed_id'])) {
            $feed_id = sanitize_text_field($settings_data['feed_id']);
            unset($settings_data['feed_id']);
        }
        elseif (isset($settings_data['feed_id'])) {
            unset($settings_data['feed_id']);
        }
        unset($settings_data['action']);
        unset($settings_data['nonce']);

        if (!isset($settings_data['feed_name'])) {
            $settings_data['feed_name'] = '';
        }

        $update_feed = isset($settings_data['update_feed']) ? true : false;
        unset($settings_data['update_feed'] );

		$settings_data = self::sanitize_settings( $settings_data );

        //Check if New
        if ( $is_new_feed ) {
            $settings_data['sources'] = json_decode( stripslashes( $settings_data['sources'] ) );
            $settings_data['feed_name'] = self::create_feed_name( stripslashes($settings_data['feed_name']) );
            $settings_data = SBR_Feed_Templates_Settings::get_feed_settings_by_feed_templates($settings_data);
        }

        unset($settings_data['new_insert']);
        $feed_name = '';
        $feed_style = '';
        if ( $update_feed ) {
            $feed_name = stripslashes($settings_data['feed_name']);
            $feed_style = stripslashes( $settings_data['feed_style'] );
            $settings_data = json_decode( stripslashes( $settings_data['settings'] ), true );
        }


        $feed_saver = new SBR_Feed_Saver( $feed_id );
        $feed_saver->set_feed_name( $feed_name );
        $feed_saver->set_feed_style( $feed_style );
        $feed_saver->set_data( $settings_data );

        $return = array(
            'success' => false,
            'feed_id' => false,
        );

        if ( $feed_saver->update_or_insert() ) {
            $return = array(
                'success' => true,
                'feed_id' => $feed_saver->get_feed_id(),
            );
            if ( $is_new_feed ) {
                echo wp_json_encode($return);
                wp_die();
            }
            else {
                $feed_cache = new FeedCache($feed_id);
                $feed_cache->clear('all');
                $feed_cache->clear('posts');
                if (isset($_POST['get_posts']) && $_POST['get_posts'] == true) {
                    $feed = Util::sbr_is_pro() ? new \SmashBalloon\Reviews\Pro\Feed($settings_data, $feed_id, $feed_cache) : new \SmashBalloon\Reviews\Common\Feed($settings_data, $feed_id, $feed_cache);

                    $feed->init();
                    $feed->get_set_cache();
                    $posts = $feed->get_post_set_page();
                    $return['posts'] = $posts;
                }
            }
        }
        echo wp_json_encode($return);
        wp_die();
    }


    /**
     * Create Feed Name
     * This will create the feed name when creating new Feeds
     *
     * @since 1.0
     */
    public static function create_feed_name( $feed_name ){
        return DB::feeds_query_name( $feed_name );
    }

    /**
	 * Used in an AJAX call to delete feeds from the Database
	 * $_POST data.
	 *
	 * @since 1.0
	 */
	public static function delete_feed() {
        check_ajax_referer('sbr-admin', 'nonce');

        if (!sbr_current_user_can('manage_reviews_feed_options')) {
            wp_send_json_error();
        }

        $feeds_id = json_decode( stripslashes( $_POST['feeds_ids'] ) );
		if ( ! empty( $feeds_id ) && is_array( $feeds_id ) ) {
			$feeds_id = array_map( 'absint', $feeds_id );
            DB::delete_feeds_query( $feeds_id );
		}
	}

    /**
	 * Used in an AJAX call to duplicate a Feed
	 * $_POST data.
	 *
	 * @since 1.0
	 */
	public static function duplicate_feed() {
        check_ajax_referer('sbr-admin', 'nonce');

        if (!sbr_current_user_can('manage_reviews_feed_options')) {
            wp_send_json_error();
        }

		if ( ! empty( $_POST['feed_id'] ) ) {
			$feed_id = absint( $_POST['feed_id'] );
			DB::duplicate_feed_query( $feed_id );
		}
	}

    /**
	 * Used to retrieve Feed Posts for preview screen
	 * Returns Feed info or false!
	 *
	 *
	 *
	 * @since 1.0
	 */
	public static function feed_customizer_fly_preview() {
        check_ajax_referer('sbr-admin', 'nonce');

        if (!sbr_current_user_can('manage_reviews_feed_options')) {
            wp_send_json_error();
        }
		if ( isset( $_POST['feedID'] ) && isset( $_POST['previewSettings'] ) ) {
            $return = [
                'posts' => []
            ];
			$preview_settings = json_decode(stripslashes($_POST['previewSettings']), true);
			$preview_settings = self::sanitize_settings( $preview_settings );

            $feed_id = absint( $_POST['feedID'] );
            $feed_cache = new FeedCache($feed_id, 30000);
            $feed_cache->clear('posts');
            $feed = Util::sbr_is_pro() ? new \SmashBalloon\Reviews\Pro\Feed( $preview_settings , $feed_id, $feed_cache) : new \SmashBalloon\Reviews\Common\Feed($preview_settings, $feed_id, $feed_cache);
            $feed->init();

            $feed->get_set_cache();
            $posts            = $feed->get_post_set_page();

            $feed_id          = absint( $_POST['feedID'] );
			$feed_name        = sanitize_text_field( wp_unslash($_POST['feedName'] ) );

            if ( isset( $posts['data'] ) ) {
                $posts = $posts['data'];
            }

            if ( isset( $preview_settings['sortRandomEnabled'] ) && $preview_settings['sortRandomEnabled'] === true) {
                shuffle( $posts );
            }

            // Array of Included Stars $preview_settings['includedStarFilters']


            $feed_saver = new SBR_Feed_Saver( $feed_id );
			$feed_saver->set_feed_name( $feed_name );
			$feed_saver->set_data( $preview_settings );
			// Update feed settings depending on feed templates
			if ( isset( $_POST['isFeedTemplatesPopup'] ) ) {
                $preview_settings = self::get_feed_settings_by_feed_templates( $preview_settings );
                $return['settings'] = $preview_settings;
            }

            $return['posts'] = $posts;



            echo sbr_json_encode(
                $return
            );
		}
		wp_die();
	}

    /**
	 * Used to Start the Moderation Mode & retrieve Reviews
	 *
	 *
	 *
	 * @since 1.0
	 */
	public static function start_moderation_mode() {
        check_ajax_referer('sbr-admin', 'nonce');

        if (!sbr_current_user_can('manage_reviews_feed_options')) {
            wp_send_json_error();
        }
		if ( isset( $_POST['feedID'] ) && isset( $_POST['previewSettings'] ) ) {
			$preview_settings = json_decode(stripslashes($_POST['previewSettings']), true);
			$preview_settings = self::sanitize_settings( $preview_settings );
            $feed_id = absint($_POST['feedID'] );

            $preview_settings['numPostDesktop'] = 100;
            $preview_settings['numPostTablet'] = 100;
            $preview_settings['numPostMobile'] = 100;

            $feed = Util::sbr_is_pro() ? new \SmashBalloon\Reviews\Pro\Feed( $preview_settings , $feed_id, new FeedCache($feed_id, 30000)) : new \SmashBalloon\Reviews\Common\Feed($preview_settings, $feed_id, new FeedCache($feed_id, 30000));
            $feed->init();
            $posts = $feed->get_posts_for_moderation();

            echo sbr_json_encode(
                [
                    'posts' => $posts
                ]
            );
		}
		wp_die();
	}


    /**
    * Used to Add Sources
    *
    *
    *
    * @since 1.0
    */
    public static function add_source(){
        check_ajax_referer('sbr-admin', 'nonce');

        if (!sbr_current_user_can('manage_reviews_feed_options')) {
            wp_send_json_error();
        }
        $return = [];

        if ( isset( $_POST['provider'] ) && isset( $_POST['providerIdUrl'] ) ) {

            if( isset( $_POST['apiKey'] ) && !empty( $_POST['apiKey'] ) && $_POST['apiKey'] !== null ){
	            $data = array(
		            'provider' => sanitize_text_field( $_POST['provider'] ),
		            'providerIdUrl' => sanitize_text_field( $_POST['providerIdUrl'] ),
		            'apiKey' => sanitize_text_field( $_POST['apiKey'] ),
	            );
                $return = self::process_source_apikey( $data );
            }
            echo sbr_json_encode(
                $return
            );
        }
        wp_die();
    }

    /**
    * Used to Add Facebook Sources
    *
    *
    *
    * @since 1.0
    */
    public static function add_facebook_souce()
    {
        check_ajax_referer('sbr-admin', 'nonce');

        if (!sbr_current_user_can('manage_reviews_feed_options')) {
            wp_send_json_error();
        }

        if( isset( $_POST['selectedFacebookPages'] ) && ! empty( $_POST['selectedFacebookPages'] )){
            $relay = new SBRelay(new SettingsManagerService());
            $facebook = new \SmashBalloon\Reviews\Pro\Integrations\Providers\Facebook($relay);

            $selected_facebook_pages = json_decode( stripslashes( $_POST['selectedFacebookPages'] ), true );
            foreach ( $selected_facebook_pages as $page ) {
	            $page['id'] = sanitize_text_field( $page['id'] );
	            $page['access_token'] = sanitize_text_field( $page['access_token'] );

                $relay_args = [
                    'place_id' => $page['id'],
                    'api_key' => $page['access_token']
                ];
                $info = $facebook->getSourcesInfo( $relay_args );
                if( $info !== false ) {
                    $info = json_decode($info, true);
                    //Checks if there is an error
                    if( isset( $info['error'] ) ){
                        return $info;
                    }
                    $info['info']['provider'] = 'facebook';
                    $info['info']['access_token'] = $page['access_token'];

                    SBR_Sources::update_or_insert($info['info']);
                }
            }
        }

        echo sbr_json_encode(
            [
                'sourcesList' => SBR_Sources::get_sources_list()
            ]
        );
        wp_die();
    }

    /**
    * Used to Add Manual Facebook Source
    *
    *
    *
    * @since 1.0
    */
    public static function add_manual_facebook_souce()
    {
        check_ajax_referer('sbr-admin', 'nonce');

        if (!sbr_current_user_can('manage_reviews_feed_options')) {
            wp_send_json_error();
        }

        if(
            isset( $_POST['pageType'] ) && ! empty( $_POST['pageType'] ) &&
            isset( $_POST['pageId'] ) && ! empty( $_POST['pageId'] ) &&
            isset( $_POST['pageToken'] ) && ! empty( $_POST['pageToken'] )
        )
        {
            $relay = new SBRelay(new SettingsManagerService());
            $facebook = new \SmashBalloon\Reviews\Pro\Integrations\Providers\Facebook($relay);

            $page_id  = sanitize_text_field( $_POST['pageId'] );
            $access_token = sanitize_text_field( $_POST['pageToken'] );

            $relay_args = [
                'place_id' => $page_id,
                'api_key' => $access_token
            ];
            $info = $facebook->getSourcesInfo( $relay_args );
            if( $info !== false ) {
                $info = json_decode($info, true);
                //Checks if there is an error
                if( isset( $info['error'] ) || !isset( $info['info'] )){
                    echo sbr_json_encode([ 'error' => true ]);
                    wp_die();
                }
                $info['info']['provider'] = 'facebook';
                $info['info']['access_token'] = $access_token;
                SBR_Sources::update_or_insert($info['info']);
                echo sbr_json_encode(
                    [
                        'sourcesList' => SBR_Sources::get_sources_list()
                    ]
                );
            } elseif( isset( $info['error'] ) || !isset( $info['info'] )) {
                echo sbr_json_encode([ 'error' => true ]);
            }
        }



        wp_die();
    }

    /**
    * Used to Add Sources
    *
    *
    *
    * @since 1.0
    */
    public static function delete_souce(){
        check_ajax_referer('sbr-admin', 'nonce');

        if (!sbr_current_user_can('manage_reviews_feed_options')) {
            wp_send_json_error();
        }

        if( isset( $_POST['sourceID'], $_POST['sourceAccountID'], $_POST['sourceProvider'] ) ){
            $source_id = sanitize_text_field($_POST['sourceID']);
            $source_account_id = sanitize_text_field($_POST['sourceAccountID']);
            $provider = sanitize_text_field($_POST['sourceProvider']);

            SBR_Sources::delete_source( $source_id );
            $relay = new SBRelay(new SettingsManagerService());
            $relay_args = [
                'place_id' => $source_account_id
            ];
            switch ( $provider ) {
                case 'google':
                    $google = new Google($relay);
                    $info = $google->removeSource($relay_args);
                break;
                case 'yelp':
                    $yelp = new Yelp($relay);
                    $info = $yelp->removeSource($relay_args);
                break;
                case 'tripadvisor':
                    $tripadvisor = new \SmashBalloon\Reviews\Pro\Integrations\Providers\TripAdvisor($relay);
                    $info = $tripadvisor->removeSource($relay_args);
                break;
                case 'facebook':
                    $tripadvisor = new \SmashBalloon\Reviews\Pro\Integrations\Providers\Facebook($relay);
                    $info = $tripadvisor->removeSource($relay_args);
                    break;
            }
        }
        echo sbr_json_encode([
            'sourcesList' => SBR_Sources::get_sources_list()
        ]);
        wp_die();
    }


    /**
     * import feed Settings
     *
     *
     * @since 1.0
     */
     public static function import_feed_settings() {
        check_ajax_referer('sbr-admin', 'nonce');

        if (!sbr_current_user_can('manage_reviews_feed_options')) {
            wp_send_json_error();
        }


        $filename = $_FILES['feedFile']['name'];
		$ext = pathinfo( $filename, PATHINFO_EXTENSION );
		if ( 'json' !== $ext ) {
			wp_send_json_error( ['message' => __( 'Supports only Json file', 'feeds-for-reviews' )] );
		}

		$imported_settings = file_get_contents( $_FILES['feedFile']['tmp_name'] );
		if ( empty( $imported_settings ) ) {
			wp_send_json_error( ["message" => __( "Don't have file, Please try again", "feeds-for-reviews" )] );
		}

		$feed_return = SBR_Feed_Saver_Manager::import_feed( $imported_settings );
		wp_send_json( $feed_return, 200 );

		wp_die();
	}

    public static function check_api_limit( $provider ){
        $apikeys_limit = get_option('sbr_apikeys_limit', []);
        return is_array( $apikeys_limit ) && in_array( $provider, $apikeys_limit );
    }

    public static function update_api_limit( $provider, $action ){
        $apikeys_limit = get_option('sbr_apikeys_limit', []);
        if( $action === 'add' ){
            if ( !in_array($provider, $apikeys_limit, true) ) {
                array_push( $apikeys_limit,  $provider );
            }
        }
        if ($action === 'delete') {
            if ( in_array($provider, $apikeys_limit ) ) {
                $apikeys_limit = array_diff($apikeys_limit, [$provider]);
            }
        }
        update_option('sbr_apikeys_limit', $apikeys_limit);
    }

    /**
     * Add Or Update API Key
     * for a value
     *
     * @param string $provider
     * @param string $apikey
     *
     *
     * @since 1.0
     */
    public static function process_source_apikey( $data ){
        $key_name = sanitize_key( $data['provider'] ) . '_api_key';
        $provider = isset( $data['provider'] ) ? $data['provider'] : false;
        $api_keys = get_option('sbr_apikeys', []);
        $relay_args = [];
        $return = [];

        if( $provider !== false ){
            //If there is no providerIdUrl set, then the call is just for checking the API KEY
	        if ( $provider === 'tripadvisor' ) {
		        $relay_args['place_id'] = isset( $data['providerIdUrl'] ) ? self::get_place_id_tripadvisor( $data['providerIdUrl'] ) : 'XXX';
	        } else {
		        $relay_args['place_id'] = isset( $data['providerIdUrl'] ) ? self::get_place_id( $data['providerIdUrl'] ) : 'XXX';
	        }            //Check if we need to add the API Key
            if( isset( $data['apiKey'] ) && !empty( $data['apiKey'] ) && !is_null($data['apiKey']) && $data['apiKey'] !== 'null'){
                $relay_args['api_key'] = $data['apiKey'];
            }elseif( isset( $api_keys[ $provider ] ) && !empty($api_keys[ $provider]) && $api_keys[ $provider ] !== null){
                $relay_args['api_key'] = $api_keys[ $provider ];
            }

            if( !isset( $relay_args['api_key'] ) || empty($relay_args['api_key']) || $relay_args['api_key'] === null || $relay_args['api_key'] === 'null' ){
                unset( $relay_args['api_key'] );
            }

            //This Means that the users tries to get a new source
            //else tries to add API Key to be checked
            if( SBR_Feed_Saver_Manager::check_api_limit( $provider ) && $relay_args['place_id'] !== 'XXX' && (empty($relay_args['api_key']) || is_null($relay_args['api_key'] ))){
                return [
                    'apiKeyLimits' => get_option('sbr_apikeys_limit', []),
                    'pluginNotices' => Util::get_plugin_notices(),
                    'error' => 'sourceLimitExceeded'
                ];
            }

            $relay = new SBRelay( new SettingsManagerService() );
            $info = false;
            switch ( $provider ) {
                case 'google':
                    $google = new Google($relay);
                    $info = $google->getSourcesInfo($relay_args);
                break;
                case 'yelp':
                    $yelp = new Yelp($relay);
                    $info = $yelp->getSourcesInfo($relay_args);
                break;
                case 'tripadvisor':
                    $tripadvisor = new \SmashBalloon\Reviews\Pro\Integrations\Providers\TripAdvisor($relay);
                    $info = $tripadvisor->getSourcesInfo($relay_args);
                break;
            }
            if( $info !== false ) {
                $info = json_decode($info, true);

                //Checks if there is an error
                if( isset( $info['error'] ) ){
                    if( $info['error'] === 'sourceLimitExceeded' ){
                        SBR_Feed_Saver_Manager::update_api_limit( $provider, 'add' );
                    }
                    $info['apiKeyLimits'] = get_option('sbr_apikeys_limit', []);
                    $info['pluginNotices'] = Util::get_plugin_notices();
                    return $info;
                }
                /**
                 * This check for valid API key
                 * When valid the API response may return the source info
                 * OR
                 * Return an error which if it's invalid location
                 */
                $checkValidKey = ( isset($info['info']['error'] ) && $info['info']['error'] !== 'invalidKey' ) || isset($info['info']['id']);
                if ( isset( $data['apiKey'] ) && $data['apiKey'] !== null && $data['apiKey'] !== 'null' && $checkValidKey ) {
                    self::update_provider_apikey( $provider, $data['apiKey'] );
                    $return['apikey'] = 'valid';
                    SBR_Feed_Saver_Manager::update_api_limit($provider, 'delete');
                    $info['apiKeyLimits'] = get_option('sbr_apikeys_limit', []);
                }

                if (isset($info['info']['error'])) {
                    if( $info['info']['error'] === 'invalidKey' ){
                        $return['apikey'] = 'invalid';
                    }
                    if ($info['info']['error'] === 'invalidLocation') {
                        $return['placeId'] = 'invalid';
                    }
                }

                if( isset( $info['info']['id'] ) ){
                    $info['info']['provider'] = $provider;
                    SBR_Sources::update_or_insert( $info['info'] );
                    $return['message'] = 'addedSource';
                }
                $return['apiKeys']      = get_option('sbr_apikeys', []);
                $return['sourcesList']  = SBR_Sources::get_sources_list();

            }else{
                $return['error'] = 'unknown';
            }
        }

        $return['apiKeyLimits'] = get_option('sbr_apikeys_limit', []);
        $return['pluginNotices'] = Util::get_plugin_notices();
        return $return;
    }

    public static function update_api_key( $data ){
        if( isset( $_POST['provider'] ) && isset( $_POST['apiKey'] ) ){
	        $data = array(
		        'provider' => sanitize_text_field( $_POST['provider'] ),
		        'apiKey' => sanitize_text_field( $_POST['apiKey'] ),
	        );
            $return = self::process_source_apikey( $data );
        }
        echo sbr_json_encode(
            $return
        );
        wp_die();

    }

    /**
    * Add Or Update API Key
    * for a value
    *
    * @param string $provider
    * @param string $apikey
    *
    *
    * @since 1.0
    */
    public static function update_provider_apikey( $provider, $apikey ){
        $api_keys = get_option('sbr_apikeys', []);
        if( $apikey !== null && $apikey !== 'null' ){
            $api_keys = !is_array( $api_keys ) ? [] : $api_keys;
            $api_keys[ $provider ] = $apikey;
        }
        if ( empty( $apikey ) && isset( $api_keys[$provider] ) ) {
            unset( $api_keys[$provider] );
        }

        update_option('sbr_apikeys', $api_keys);
    }
    /**
	 * Determines what table and sanitization should be used
	 * when handling feed setting data.
	 *
	 * TODO: Add settings that need something other than sanitize_text_field
	 *
	 * @param string $key
	 *
	 * @return array
	 *
	 * @since 1.0
	 */
	public static function get_data_type( $key ) {
		switch ( $key ) {
			case 'sources':
				$return = array(
					'table'        => 'feed_settings',
					'sanitization' => 'sanitize_text_field',
				);
				break;
			case 'feed_title':
				$return = array(
					'table'        => 'feeds',
					'sanitization' => 'sanitize_text_field',
				);
				break;
			case 'feed_name':
				$return = array(
					'table'        => 'feeds',
					'sanitization' => 'sanitize_text_field',
				);
				break;
			case 'status':
				$return = array(
					'table'        => 'feeds',
					'sanitization' => 'sanitize_text_field',
				);
				break;
			case 'author':
				$return = array(
					'table'        => 'feeds',
					'sanitization' => 'int',
				);
				break;
			default:
				$return = array(
					'table'        => 'feed_settings',
					'sanitization' => 'sanitize_text_field',
				);
				break;
		}

		return $return;
	}

	/**
	 * Check if boolean
	 * for a value
	 *
	 * @param int|string $value
	 *
	 * @return  boolean
	 *
	 * @since 1.0
	 */
	public static function is_boolean( $value ) {
		return ( $value === 'true' || $value === 'false' || is_bool( $value ) ) ? true : false;
	}

    /**
     * Get Place or Page ID from URL
     *
     * @param string $place_url
     *
     * @return string
     *
     * @since 1.0
    */
    public static function get_place_id( $place_url ){
        $res = explode( '/', $place_url );
        return end( $res );
    }

	public static function get_place_id_tripadvisor( $place_url ){
		// probably a place ID
		if ( (bool) filter_var( $place_url, FILTER_VALIDATE_URL ) === false ) {
			return $place_url;
		}

		$broken_up = explode( '/', $place_url );

		foreach ( $broken_up as $url_piece ) {
			if ( strpos( $url_piece, '.html' ) !== false ) {
				preg_match( '/-d+\d{0,10}-/i', $url_piece, $matches, PREG_OFFSET_CAPTURE );
				if ( ! empty( $matches[0] ) ) {
					return str_replace( array( '-', 'd' ), '', $matches[0][0] );
				}
			}
		}
		return $place_url;
	}

	public static function cast_boolean( $value ) {
		if ( $value === 'true' || $value === true || $value === 'on' ) {
			return true;
		}
		return false;
	}

	/**
	 * Uses the appropriate sanitization function and returns the result
	 * for a value
	 *
	 * @param string $type
	 * @param int|string $value
	 *
	 * @return int|string
	 *
	 * @since 1.0
	 */
	public static function sanitize( $type, $value ) {
		switch ( $type ) {
			case 'int':
				$return = intval( $value );
				break;
			case 'boolean':
				$return = self::cast_boolean( $value );
				break;
			default:
				$return = sanitize_text_field( $value );
				break;
		}

		return $return;
	}

	public static function import_feed( $json ) {
		$feed_json = json_decode( $json, true );
		$return = [ 'success' => false ];

		if ( empty( $feed_json['sourcesList'] ) ) {
			$return['message'] = __( 'No feed source is included. Cannot upload feed.', 'feeds-for-reviews' );
			return $return;
		}

        $settings_data = $feed_json['settings'];

		$feed_saver = new SBR_Feed_Saver( false );
		$feed_saver->set_data( $settings_data );

		if ( $feed_saver->update_or_insert() ) {
			$return = array(
				'success' => true,
				'message' => __( 'Feed settings imported successfully.', 'feeds-for-reviews' ),
				'feed_id' => $feed_saver->get_feed_id(),
			);
			return $return;
		} else {
			$return['message'] = __( 'Could not import feed. Please try again', 'feeds-for-reviews' );
		}
		return $return;
	}


    /**
	 * Clear All Feeds Caches
	 *
	 * @return int|string
	 *
	 * @since 1.0
	 */
	public static function clear_all_caches( ) {
        check_ajax_referer('sbr-admin', 'nonce');

        if (!sbr_current_user_can('manage_reviews_feed_options')) {
            wp_send_json_error();
        }
        global $wpdb;

        $cache_table_name = $wpdb->prefix . 'sbr_feed_caches';

        $sql = "
		UPDATE $cache_table_name
		SET cache_value = ''
		WHERE cache_key NOT IN ( 'posts_backup', 'header_backup' );";
        $wpdb->query($sql);

        $table_name = $wpdb->prefix . "options";
        $wpdb->query("
			DELETE
			FROM $table_name
			WHERE `option_name` LIKE ('%\_transient\_sbr\_%')
			");
        $wpdb->query("
			DELETE
			FROM $table_name
			WHERE `option_name` LIKE ('%\_transient\_sbr\_ej\_%')
			");
        $wpdb->query("
			DELETE
			FROM $table_name
			WHERE `option_name` LIKE ('%\_transient\_sbr\_tle\_%')
			");
        $wpdb->query("
			DELETE
			FROM $table_name
			WHERE `option_name` LIKE ('%\_transient\_sbr\_album\_%')
			");
        $wpdb->query("
			DELETE
			FROM $table_name
			WHERE `option_name` LIKE ('%\_transient\_timeout\_sbr\_%')
			");

        //Clear cache of major caching plugins
        if (isset($GLOBALS['wp_fastest_cache']) && method_exists($GLOBALS['wp_fastest_cache'], 'deleteCache')) {
            $GLOBALS['wp_fastest_cache']->deleteCache();
        }
        //WP Super Cache
        if (function_exists('wp_cache_clear_cache')) {
            wp_cache_clear_cache();
        }
        //W3 Total Cache
        if (function_exists('w3tc_flush_all')) {
            w3tc_flush_all();
        }
        if (function_exists('sg_cachepress_purge_cache')) {
            sg_cachepress_purge_cache();
        }

        if (class_exists('W3_Plugin_TotalCacheAdmin')) {
            $plugin_totalcacheadmin = & w3_instance('W3_Plugin_TotalCacheAdmin');
            $plugin_totalcacheadmin->flush_all();
        }
        if (function_exists('rocket_clean_domain')) {
            rocket_clean_domain();
        }

        if (has_action('litespeed_purge_all')) {
            do_action('litespeed_purge_all');
        }
        echo wp_json_encode([
            'success' => true
        ]);

        wp_die();
    }

	public static function sanitize_settings( $raw_settings ) {
		$sanitized_values = array();
		foreach ($raw_settings as $key => $value) {
			if (is_bool( $value )) {
				// already a safe value
				$sanitized_values[ $key ] = $value;
			} elseif (is_int( $value )) {
				// already a safe value
				$sanitized_values[ $key ] = $value;
			} elseif (is_array( $value )) {
				if (empty( $value )) {
					$sanitized_values[ $key ] = array();
				} else {
					foreach ($value as $key2 => $item) {
						if (is_bool( $item )) {
							// already a safe value
							$sanitized_values[ $key ][ $key2 ] = $item;
						} elseif (is_int( $item )) {
							// already a safe value
							$sanitized_values[ $key ][ $key2 ] = $item;
						} else {
							$sanitized_values[ $key ][ $key2 ] = sanitize_text_field( $item );
						}
					}
				}

			} else {
				$sanitized_values[ $key ] = sanitize_text_field( $value );
			}
		}

		return $sanitized_values;
	}


    /**
	 * Get a list of feeds with a limit and offset like a page
	 *
	 * @since 1..1
	 */
	public static function get_feed_list_page()
    {
		check_ajax_referer('sbr-admin', 'nonce');

        if (!sbr_current_user_can('manage_reviews_feed_options')) {
            wp_send_json_error();
        }

		$args = array( 'page' => (int)sanitize_text_field( wp_unslash( $_POST['page'] ) ) );
		$feeds_data = DB::get_feeds_list($args);

        echo wp_json_encode($feeds_data);
        wp_die();
	}

}
