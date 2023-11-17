<?php

namespace Metabolism\WordpressBundle\Plugin;

class ThemePlugin
{

    /**
     * Update theme and stylesheet
     */
    public function __construct()
    {
        add_filter('update_right_now_text', function () {
            return 'WordPress %1$s'; // Remove theme name from 'at a glance' widget
        });

        add_action('init', function () {
            $template = get_option('template');

            if (!is_dir(WP_CONTENT_DIR . '/themes/' . $template) && $template != 'void') {
    
                update_option('template', 'void');
                update_option('stylesheet', 'void');
            }
    
            add_action('admin_menu', function () {
                remove_submenu_page('themes.php', 'themes.php');
            });
        });
    }
}
