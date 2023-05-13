<?php

namespace SmashBalloon\Reviews\Pro;
use SmashBalloon\Reviews\Common\PostAggregator;
use SmashBalloon\Reviews\Pro\SinglePostCache;

class Feed extends \SmashBalloon\Reviews\Common\Feed
{

    public function get_posts_for_media_finding_and_resizing()
    {
        $settings = $this->get_settings();
        $aggregator = new PostAggregator();
        $posts = $aggregator->db_posts_for_media_finding_and_resizing_set($settings['sources']);
        $posts = $aggregator->normalize_db_post_set($posts);
        $post_set = $this->filter_posts($posts, $settings);
        return $post_set;
    }

    public function find_and_resize_media($posts)
    {
        $return = array();
        $aggregator = new PostAggregator();

        foreach ($posts as $single_review) {
            $single_post_cache = new SinglePostCache($single_review, new MediaFinder($single_review['source']));
            $single_post_cache->set_provider_id($single_review['source']['id']);

            if (!$single_post_cache->media_supplied()) {
                $single_post_cache->media_finder_init();
            }
            $single_post_cache->set_lang($this->get_db_lang($single_review['source']['id']));

            $single_post_cache->resize_images(array(640, 150));
            $single_post_data = $single_post_cache->get_post_data();
            $single_post_storage_data = $single_post_cache->get_storage_data();
            $single_post_data = $aggregator->add_local_image_urls($single_post_data, $single_post_storage_data);
            $return[] = $single_post_data;
            $single_post_cache->update(
                array(
                    array('images_done', 1, '%d'),
                    array('json_data', wp_json_encode($single_post_data), '%s')
                ));
        }

        return $return;
    }



    public function cache_single_posts_from_set($posts, $provider_id)
    {
        foreach ($posts as $single_review) {
            $single_post_cache = new SinglePostCache($single_review, new MediaFinder($single_review['source']));
            $single_post_cache->set_provider_id($provider_id);

            $single_post_cache->set_lang($this->get_db_lang($provider_id));

            if (!$single_post_cache->db_record_exists()) {
                $single_post_cache->resize_avatar(150);
                if (in_array($this->provider_for_provider_id($provider_id), $this->providers_no_media, true)) {
                    $single_post_cache->set_storage_data('images_done', 1);
                }
                $single_post_cache->store();
            } else {
                $single_post_cache->update_single();
            }
        }
    }


}

?>