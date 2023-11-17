<?php

namespace Metabolism\WordpressBundle\Plugin;

class UpdatePlugin
{

    public function __construct()
    {
        $this->disableUpdate();

        add_action('admin_head', function() {
            if (!current_user_can('update_core')) {
                remove_action('admin_notices', 'update_nag', 3);
            }
        }, 1);
    }

    /**
     * Disable WordPress auto update and checks
     */
    protected function disableUpdate()
    {

        remove_action('admin_init', '_maybe_update_core');
        remove_action('wp_version_check', 'wp_version_check');
        remove_action('load-plugins.php', 'wp_update_plugins');
        remove_action('load-update.php', 'wp_update_plugins');
        remove_action('load-update-core.php', 'wp_update_plugins');
        remove_action('admin_init', '_maybe_update_plugins');
        remove_action('wp_update_plugins', 'wp_update_plugins');
        remove_action('load-themes.php', 'wp_update_themes');
        remove_action('load-update.php', 'wp_update_themes');
        remove_action('load-update-core.php', 'wp_update_themes');
        remove_action('admin_init', '_maybe_update_themes');
        remove_action('wp_update_themes', 'wp_update_themes');
        remove_action('update_option_WPLANG', 'wp_clean_update_cache');
        remove_action('wp_maybe_auto_update', 'wp_maybe_auto_update');
        remove_action('init', 'wp_schedule_update_checks');

        add_filter('plugins_auto_update_enabled', '__return_false');
    }
}
