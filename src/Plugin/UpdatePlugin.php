<?php

namespace Metabolism\WordpressBundle\Plugin;

use Metabolism\WordpressBundle\WordpressBundle;

class UpdatePlugin
{

    public array $pluginUpdateBlacklist = [
        'elementor',
        'elementor-pro',
    ];

    public function __construct()
    {
        $this->disableIncompatiblePlugins();
        $this->enableAutoUpdates();
        // $this->disablePluginUpdates();

        add_action('admin_head', function () {
            if (!current_user_can('update_core')) {
                remove_action('admin_notices', 'update_nag', 3);
            }

            global $pagenow;
            if ($pagenow === 'plugins.php' && DISALLOW_FILE_MODS === true) {
                ?>
                <div class="notice notice-info">
                    <p>Plugins in the Wildpress framework are managed via <a target="_blank"
                            href="https://getcomposer.org">Composer</a>. Contact your developer to add plugins.</p>
                </div>
                <?php
            }
        }, 1);
    }

    private function enableAutoUpdates()
    {
        add_filter('auto_core_update_send_email', '__return_false');	
        add_filter('auto_update_theme', '__return_false');
        add_filter('auto_update_plugin', '__return_true');

        add_filter('auto_update_plugin', function ($update, $item) {
            if (in_array($item->slug, $this->pluginUpdateBlacklist)) {
                return false;
            } else {
                return $update;
            }
        }, 10, 2);
    }

    // /**
    //  * Disable WordPress auto update and checks
    //  */
    // private function disablePluginUpdates()
    // {
    //     wp_clear_scheduled_hook('wp_update_themes');

    //     remove_action('admin_init', '_maybe_update_core');
    //     remove_action('wp_version_check', 'wp_version_check');
    //     remove_action('load-plugins.php', 'wp_update_plugins');
    //     remove_action('load-update.php', 'wp_update_plugins');
    //     remove_action('load-update-core.php', 'wp_update_plugins');
    //     remove_action('admin_init', '_maybe_update_plugins');
    //     remove_action('wp_update_plugins', 'wp_update_plugins');
    //     remove_action('load-themes.php', 'wp_update_themes');
    //     remove_action('load-update.php', 'wp_update_themes');
    //     remove_action('load-update-core.php', 'wp_update_themes');
    //     remove_action('admin_init', '_maybe_update_themes');
    //     remove_action('wp_update_themes', 'wp_update_themes');
    //     remove_action('update_option_WPLANG', 'wp_clean_update_cache');
    //     remove_action('wp_maybe_auto_update', 'wp_maybe_auto_update');
    //     remove_action('init', 'wp_schedule_update_checks');

    //     add_filter('plugins_auto_update_enabled', '__return_false');
    // }

    public function disableIncompatiblePlugins()
    {
        if (!is_admin() && !WordpressBundle::isLoginUrl()) {
            if (is_multisite())
                add_filter('site_option_active_sitewide_plugins', [$this, 'disableWP2FA']);

            add_filter('option_active_plugins', [$this, 'disableWP2FA']);
        }
    }

    /**
     * Disable wp-2fa because of a Symfony class collision
     *
     * @param [type] $plugins
     * @return void
     */
    public function disableWP2FA($plugins)
    {
        $wp_2fa = "wp-2fa/wp-2fa.php";

        if ($k = array_search($wp_2fa, $plugins))
            unset($plugins[$k]);

        if (isset($plugins[$wp_2fa]))
            unset($plugins[$wp_2fa]);

        return $plugins;
    }
}
