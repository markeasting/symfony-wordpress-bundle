<?php

namespace Metabolism\WordpressBundle\Plugin;

use Metabolism\WordpressBundle\WordpressBundle;

class PerformancePlugin
{

    public function __construct()
    {
        $this->generalEnhancements();
        $this->disablePlugins();
    }

    public function generalEnhancements()
    {
        if (!is_admin()) {
            remove_action('init', 'check_theme_switched', 99);
        }
    }

    public function disablePlugins()
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
