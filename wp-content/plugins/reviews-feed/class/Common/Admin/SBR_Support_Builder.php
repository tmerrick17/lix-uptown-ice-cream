<?php
/**
 * Reviews Feed Saver
 *
 * @since 1.0
 */

namespace SmashBalloon\Reviews\Common\Admin;

use Smashballoon\Customizer\V2\Support_Builder;
use SmashBalloon\Reviews\Common\Builder\Config\Proxy;
use SmashBalloon\Reviews\Common\Customizer\DB;
use SmashBalloon\Reviews\Common\Util;

class SBR_Support_Builder extends Support_Builder
{

    protected $config_proxy;

    /**
     *  MEnu Slug
     * @since 1.0
     */
    protected $builder_menu_slug;
    protected $current_plugin;
    protected $add_to_menu;

    protected $menu;

    public function __construct(Proxy $config_proxy)
    {
        $this->menu = [
            'parent_menu_slug' => "sbr",
            'page_title' => "Support",
            'menu_title' => "Support",
            'menu_slug' => "sbr-support",
        ];
        $this->config_proxy = $config_proxy;
        $this->builder_menu_slug = SBR_CUSTOMIZER_MENU_SLUG;
        $this->add_to_menu = ! Util::sbr_is_pro() ? true : check_license_valid();
    }

    public function custom_support_data()
    {
        $aboutus_data = [
            'nonce' => wp_create_nonce('sbr-admin'),
            'assetsURL' => SB_COMMON_ASSETS,
            'feedsList' => DB::get_feeds_list(),
            'supportContent' => $this->get_support_content(),
            'supportInfo' => $this->get_support_info(),
            'isPro' => Util::sbr_is_pro()
        ];

        return $aboutus_data;
    }


    public function get_support_content(){
        $utm_source = Util::sbr_is_pro() ? 'reviews-pro' : 'reviews-free';
	    return [
		    [
			    'heading'       => __('Getting Started', 'sb-reviews'),
			    'description'   => __( 'Some helpful resources to get you started', 'sb-reviews' ),
			    'icon'          => 'rocketicon',
			    'content'       => [
				    [
					    'text' => __('Getting Started with Reviews Feed', 'sb-reviews'),
					    'link' => 'https://smashballoon.com/docs/getting-started/?reviews&utm_campaign=' .  $utm_source . '&utm_source=support&utm_medium=docs&utm_content=Create a Feed'
				    ],
				    [
					    'text' => __('How to Create a Yelp API Key', 'sb-reviews'),
					    'link' => 'https://smashballoon.com/doc/creating-a-yelp-api-key/?reviews&utm_campaign=' .  $utm_source . '&utm_source=support&utm_medium=docs&utm_content=Yelp API Key'
				    ],
				    [
					    'text' => __('How to Create a Google API Key', 'sb-reviews'),
					    'link' => 'https://smashballoon.com/doc/creating-a-google-api-key/?reviews&utm_campaign=' .  $utm_source . '&utm_source=support&utm_medium=docs&utm_content=Google API Key'
				    ],
			    ],
			    'button' => [
				    'text' => __('More help on Getting started', 'sb-reviews'),
				    'link' => 'https://smashballoon.com/docs/getting-started/?reviews&utm_campaign=' .  $utm_source . '&utm_source=support&utm_medium=docs&utm_content=Getting Started'
			    ]
		    ],
		    [
			    'heading' => __('Docs & Troubleshooting', 'sb-reviews'),
			    'description' => __('Run into an issue? Check out our help docs.', 'sb-reviews'),
			    'icon' => 'bookopen',
			    'content' => [
				    [
					    'text' => __('Guide to GDPR Compliance', 'sb-reviews'),
					    'link' => 'https://smashballoon.com/docs/reviews-feed--gdpr-compliance/?reviews&utm_campaign=' .  $utm_source . '&utm_source=support&utm_medium=docs&utm_content=GDPR Compliance'
				    ],
				    [
					    'text' => __('How to Resolve Error Messages', 'sb-reviews'),
					    'link' => 'https://smashballoon.com/doc/reviews-feed-error-message-reference/?reviews&utm_campaign=' . $utm_source . '&utm_source=front-end-error&utm_medium=no-posts-found&utm_content=ErrorMessageReference'
				    ],
				    [
					    'text' => __('Translating Google Reviews', 'sb-reviews'),
					    'link' => 'https://smashballoon.com/doc/language-reviews-feed/?reviews&utm_campaign=' . $utm_source . '&utm_source=support&utm_medium=help-docs-language&utm_content=Translating'
				    ],
			    ],
			    'button' => [
				    'text' => __('View Documentation', 'sb-reviews'),
				    'link' => 'https://smashballoon.com/docs/?reviews&utm_campaign=' .  $utm_source . '&utm_source=support&utm_medium=docs&utm_content=View Documentation'
			    ]
		    ],
		    [
			    'heading' => __('Additional Resources', 'sb-reviews'),
			    'description' => __('To help you get the most out of the plugin', 'sb-reviews'),
			    'icon' => 'bookplus',
			    'content' => [
				    [
					    'text' => __('Can I Display Multiple Reviews on One Page?', 'sb-reviews'),
					    'link' => 'https://smashballoon.com/doc/can-i-display-multiple-reviews-feeds-on-my-site-or-on-the-same-page/?reviews&utm_campaign=' .  $utm_source . '&utm_source=support&utm_medium=docs&utm_content=Google API Key'
				    ],
				    [
					    'text' => __('License Tiers Explained (additional review providers)', 'sb-reviews'),
					    'link' => 'https://smashballoon.com/docs/license-tiers-explained/?reviews&utm_campaign=' .  $utm_source . '&utm_source=support&utm_medium=docs&utm_content=License Tiers Explained'
				    ],
				    [
					    'text' => __('How do I Embed My Feed Directly in a Template?', 'sb-reviews'),
					    'link' => 'https://smashballoon.com/doc/how-do-i-embed-the-reviews-feed-directly-into-a-theme-template/?reviews&utm_campaign=' .  $utm_source . '&utm_source=support&utm_medium=docs&utm_content=Embed in Template'
				    ],
			    ],
			    'button' => [
				    'text' => __('View Blog', 'sb-reviews'),
				    'link' => 'https://smashballoon.com/blog/?reviews&utm_campaign=' .  $utm_source . '&utm_source=support&utm_medium=docs&utm_content=View Blog'
			    ]
		    ]
	    ];
    }



    public function get_support_info(){
        $output = '';
        // Build the output strings
        $output .= Util::get_site_n_server_info();
        $output .= Util::get_active_plugins_info();
        $output .= Util::get_global_settings_info();
        $output .= Util::get_sources_settings_info();
        $output .= Util::get_api_settings_info();
        $output .= Util::get_feeds_settings_info();
        $output .= Util::get_posts_table_info();

        return $output;
    }
}
