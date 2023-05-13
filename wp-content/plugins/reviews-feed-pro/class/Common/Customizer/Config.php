<?php

namespace SmashBalloon\Reviews\Common\Customizer;

class Config extends \Smashballoon\Customizer\V2\Config{
    public $plugin_slug = 'sbr';
    public $statuses_option = 'sbr_statuses';

    public function isPro(){
        return sbr_is_pro_version();
    }
}