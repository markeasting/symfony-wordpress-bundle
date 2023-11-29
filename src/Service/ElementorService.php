<?php

namespace Metabolism\WordpressBundle\Service;

use Metabolism\WordpressBundle\Entity\Post;
use Twig\Environment as Twig;

class ElementorService 
{

    /**
     * Renders the Elementor template / post content for the *current* post
     *
     * @TODO check if caching would help here
     * 
     * @param Post|null $post;
     * @return string
     */
    public function render(?Post $post = null): string
    {

        if ($post) {
            setup_postdata($post->getPostObject());
        }

        ob_start();

        $template_loaded = false;

        if (function_exists('elementor_theme_do_location')) {
            if (is_singular()) {
                $template_loaded = elementor_theme_do_location('single');
            } elseif (is_archive() || is_home() || is_search()) {
                $template_loaded = elementor_theme_do_location('archive');
            } else {
                $template_loaded = elementor_theme_do_location('single');
            }
        }

        if (!$template_loaded) {
            if ($post) {
                echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display($post->ID);
            } else {
                the_content();
            }
        }

        return ob_get_clean();
    }

}
