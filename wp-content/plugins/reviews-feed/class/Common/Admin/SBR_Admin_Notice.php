<?php

namespace SmashBalloon\Reviews\Common\Admin;

use Smashballoon\Stubs\Services\ServiceProvider;

class SBR_Admin_Notice extends ServiceProvider{

    function register() {
        $this->init();
    }

    public function init() {
        if ( ! is_admin() ) {
            return;
        }

        add_action('admin_notices', [$this, 'apikey_limit_notice']);

    }


    public static function check_menu_notice_bubble(){
        $is_admin_bubble = false;
        $api_limits = get_option('sbr_apikeys_limit', []);

        if( is_array( $api_limits )  && sizeof( $api_limits ) > 0 ){
            $is_admin_bubble = true;
        }


        return $is_admin_bubble;
    }


    public function apikey_limit_notice(){
        $api_limits = get_option('sbr_apikeys_limit', []);
        $should_show_notice = is_array( $api_limits )  && sizeof( $api_limits ) > 0;
        if ( ! $should_show_notice ) {
            return;
        }
        ?>
        <div class="notice notice-error">
            <p>
                <?php echo sprintf(
                    __('You have reached the maximum sources limit for (%s), Please enter a valid API key to retrieve new sources. Please add API Key %shere%s', 'sb-reviews'),
                    ucfirst( join( ", ", $api_limits ) ),
                    '<a href="' . admin_url('admin.php?page=sbr-settings') . '">',
                    '</a>');
                ?>
            </p>
        </div>
        <?php
    }

}