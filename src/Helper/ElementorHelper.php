<?php

namespace Metabolism\WordpressBundle\Helper;

use Metabolism\WordpressBundle\Entity\Post;
use Twig\Environment as Twig;

class ElementorHelper 
{

    /**
     * Renders the Elementor template / post content for the *current* post
     *
     * @TODO check if caching would help here
     * 
     * @return string
     */
    public function render(): string
    {
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
            the_content();
        }

        return ob_get_clean();
    }

}
