<?php

namespace Metabolism\WordpressBundle\Plugin;

class MaintenancePlugin
{

    public function __construct()
    {
        add_action('init', [$this, 'addMaintenanceMode']);
    }

    public function addMaintenanceMode()
    {
        if (!current_user_can('editor') && !current_user_can('administrator'))
            return;

        if (is_admin()) {
            add_action('admin_init', function () {

                add_settings_field('maintenance_field', __('Maintenance', 'wp-steroids'), function () {

                    echo '<input type="checkbox" id="maintenance_field" name="maintenance_field" value="1" ' . checked(1, get_option('maintenance_field'), false) . ' />' . __('Enable maintenance mode', 'wp-steroids');

                }, 'general');

                register_setting('general', 'maintenance_field');
            });
        }

        if ($this->isMaintenance(true)) {

            add_action('admin_bar_menu', function ($wp_admin_bar) {
                $args = [
                    'id' => 'maintenance',
                    'title' => '<span style="position: fixed; left: 0; top: 0; width: 100%; background: #ff7600; height: 2px; z-index: 99999"></span>' . __('Disable maintenance mode', 'wp-steroids'),
                    'href' => get_admin_url(null, '/options-general.php#maintenance_field'),
                ];

                $wp_admin_bar->add_node($args);

            }, 999);
        }

    }


    /**
     * @param bool $strict
     * @return bool
     */
    static function isMaintenance($strict = false)
    {
        if ($strict)
            return get_option('maintenance_field', false);
        else
            return !current_user_can('editor') && !current_user_can('administrator') && get_option('maintenance_field', false);
    }
}
