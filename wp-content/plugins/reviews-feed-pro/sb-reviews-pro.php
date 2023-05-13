<?php
/*
Plugin Name: Reviews Feed Pro
Plugin URI: http://smashballoon.com/reviews-feed
Description: Reviews Feeds  allows you to display completely customizable Reviews feeds from many different providers.
Version: 1.1.1
Author: Smash Balloon
Author URI: http://smashballoon.com/
Text Domain: reviews-feed
*/

/*
Copyright 2023  Smash Balloon  (email: hey@smashballoon.com)
This program is paid software; you may not redistribute it under any
circumstances without the expressed written consent of the plugin author.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/
if (!defined('SBRVER')) {
	define('SBRVER', '1.1.1');
}

if (!defined('SBR_PLUGIN_BASENAME')) {
	define('SBR_PLUGIN_BASENAME', plugin_basename(__FILE__));
}

if (!defined('SBR_PRO')) {
    define('SBR_PRO', true);
}

if (!defined('SBR_PLUGIN_DIR')) {
    define('SBR_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

if (defined('SBR_LITE') ) {
    if ( isset( $_POST['oth'] ) ) {
        return;
    } else {
        wp_die("Please deactivate the free version of the Reviews Feed plugin before activating this version.<br /><br />Back to the WordPress <a href='" . get_admin_url(null, 'plugins.php') . "'>Plugins page</a>.");
    }
}

require_once trailingslashit(SBR_PLUGIN_DIR) . 'bootstrap.php';


if ( ! class_exists( '\ReviewsFeed\EDD_SL_Plugin_Updater' ) ) {
	// load custom updater
	include dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php';
}
function sb_reviews_plugin_updater() {
	// retrieve license key from the DB
	$settings = get_option( 'sbr_settings', array() );

	$sbr_license_key = ! empty( $settings['license_key'] ) ? trim( $settings['license_key'] ) : '';

	// setup the updater
	$edd_updater = new ReviewsFeed\EDD_SL_Plugin_Updater(
		SBR_STORE_URL,
		__FILE__,
		array(
			'version'   => SBRVER,                   // current version number
			'license'   => $sbr_license_key,        // license key
			'item_name' => SBR_PLUGIN_NAME,         // name of this plugin
			'author'    => 'Smash Balloon'          // author of this plugin
		)
	);
}
add_action( 'admin_init', 'sb_reviews_plugin_updater', 0 );