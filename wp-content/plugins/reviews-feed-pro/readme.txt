=== Reviews Feed Pro ===
Author: Smash Balloon
Contributors: smashballoon
Support Website: https://smashballoon/reviews-feed/
Requires at least: 4.1
Tested up to: 6.2
Stable tag: 1.1.1
Requires PHP: 7.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Feeds for YouTube Pro allows you to display completely customizable YouTube feeds from any channel.

== Description ==
Display **completely customizable**, **responsive** and **search engine crawlable** versions of your customer reviews on your website. Completely match the look and feel of your site with tons of customization options!

= Features =
* **Completely Customizable** - by default inherits your theme's styles
* Review feed content is **crawlable by search engines** adding SEO value to your site
* **Completely responsive and mobile optimized** - works on any screen size
* Display reviews from Google, Yelp, Facebook, and TripAdvisor

For simple step-by-step directions on how to set up the Reviews Feed Pro plugin please refer to our [setup guide](http://smashballoon.com/reviews-feed/docs/setup/ 'Reviews Feed Pro setup guide').


== Installation ==
1. Install the Reviews Feed Pro plugin by uploading the files to your web server (in the /wp-content/plugins/ directory).
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to the 'Reviews Feed' settings page to configure your Reviews and create a new feed.
4. Use the shortcode [reviews=feed feed=1] in your page, post or widget to display your feed.


For simple step-by-step directions on how to set up the Reviews Feed Pro plugin please refer to our [setup guide](http://smashballoon.com/reviews-feed/docs/setup/ 'Reviews Feed Pro setup guide').


== Changelog ==
= 1.1.1 =
* Fix: Fixed a bug with the carousel layout causing only the asterisk (...) to show for some review text.
* Fix: Manual account connection for Facebook reviews was not working.
* Fix: Fixed inaccurate average rating in the header when multiple sources were used in a feed.
* Fix: Fixed visual star rating not showing half-stars. For example, an average rating of 4.6 would show 4 of 5 stars filled in the visual star icon.
* Fix: Fixed pagination not being available when more than 20 feeds were created.
* Fix: Fixed PHP error "undefined index" when header data was not available for a feed.
* Fix: Fixed fatal error when installing or updating due to custom script initiation on some hosts.

= 1.1 =
* New: Added support for Google locales to allow fetching reviews in any supported translation. Go to the Settings page -> Lanague & Translation tab to set a global language for your Google reviews.
* Fix: Fixed a bug with local image creating and storing that would cause the images to be recreated every time the feed refreshed.

= 1.0.1 =
* Fix: Facebook reviews were not filtering by star rating correctly.
* Fix: Connecting a TripAdvisor source would not work if using the full URL and the ID of the place was greater than 6 digits.
* Fix: Fixed some conflicts with other Smash Balloon products that would cause some visual issues with buttons and icons.
* Fix: Removing a source from the plugin will also remove it from the Reviews API to allow changing sources without an API Key if needed.

= 1.0 =
* Launched the Reviews Feed Pro plugin
