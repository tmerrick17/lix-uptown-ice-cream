<?php

namespace SmashBalloon\Reviews\Pro;

class Parser extends \SmashBalloon\Reviews\Common\Parser{

    public function get_reviewer_avatar_url($post)
    {
        if (!empty($post['reviewer']['avatar_local']) && $post['reviewer']['avatar_local'] !== null) {
            return $post['reviewer']['avatar_local'];
        } elseif (!empty($post['reviewer']['avatar']) && $post['reviewer']['avatar'] !== null) {
            return $post['reviewer']['avatar'];
        }
        return SB_COMMON_ASSETS . 'sb-customizer/assets/images/avatar.jpg';
    }

    public function get_media($post)
    {
        if (!empty($post['media'])) {
            return $post['media'];
        }
        return array();
    }



    public function get_media_url($media, $resolution = 150)
    {
        if (!empty($media['local'][$resolution])) {
            return $media['local'][$resolution];
        }
        if (!empty($media['url'])) {
            return $media['url'];
        }
        return '';
    }

}