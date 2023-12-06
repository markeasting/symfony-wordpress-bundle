<?php

namespace Metabolism\WordpressBundle\Plugin;

use function Env\env;

class NoticePlugin
{

    public function __construct()
    {

        add_action('init', [$this, 'environmentInfo']);

        if (is_admin()) {

            add_action('admin_notices', [$this, 'adminNotices']);

        } else {

            // if (defined('HEADLESS') && HEADLESS)
            //     add_action('init', function() {
            //         global $wpdb;
            //         $wpdb->suppress_errors = true;
            //     });
        }
    }

    /**
     * Check symlinks and folders
     */
    public function adminNotices()
    {
        $currentScreen = get_current_screen();

        if (!current_user_can('administrator') || $currentScreen->base != 'dashboard')
            return;

        $notices = [];
        $errors = [];

        //check folder right
        $folders = [PUBLIC_DIR . '/wp-bundle/languages', PUBLIC_DIR . '/uploads', PUBLIC_DIR . '/wp-bundle/upgrade', '/var/cache', '/var/log'];
        $folders = apply_filters('wp-bundle/admin_notices', $folders);

        foreach ($folders as $folder) {

            $path = BASE_URI . $folder;

            if (!file_exists($path) && !@mkdir($path, 0755, true))
                $errors[] = 'Can\' create folder : ' . $folder;

            if (file_exists($path) && !is_writable($path))
                $errors[] = $folder . ' folder is not writable';
        }

        // Moved to MailPlugin
        // $mailer_dsn = env('MAILER_DSN');
        // if (!isset($mailer_dsn) || $mailer_dsn === 'null://localhost')
        //     $errors[] = 'Mail delivery disabled: no <code>MAILER_DSN</code> configured.';

        if (is_blog_installed() && !env('WP_INSTALLED'))
            $notices[] = 'Wordpress is now installed, please add <code>WP_INSTALLED=1</code> to your environment.';

        // if( !file_exists(BASE_URI.'/src/Controller/BlogController.php') )
        //     $errors[] = 'BlogController is missing!';

        if (!empty($errors))
            echo '<div class="error"><p>' . implode('<br/>', $errors) . '</p></div>';

        if (!empty($notices))
            echo '<div class="updated"><p>' . implode('<br/>', $notices) . '</p></div>';
    }


    /**
     * Add environment to admin bar
     */
    public function environmentInfo()
    {
        add_action('admin_bar_menu', function ($wp_admin_bar) {

            $ENV = env('APP_ENV');

            $color = match ($ENV) {
                'dev' => '#4ebd0d',
                // 'prod' => '#df0f0f',
                default => false,
            };

            if (!$color)
                return;

            $args = [
                'id' => 'environment',
                'title' => '<style>.wp-environment-badge { background-color: '.$color.' !important; }</style><span>'.strtoupper($ENV).'</span>',
                'href' => '#',
                'meta'  => array(
                    'class' => 'wp-environment-badge',
                    'style' => 'background-color: '.$color.';',
                ),
            ];

            $wp_admin_bar->add_node($args);
            
            $args = [
                'id' => 'debug',
                'title' => '<span style="position: fixed; left: 0; top: 0; width: 100%; background: '.$color.'; height: 2px; z-index: 99999"></span>',
                'href' => '#',
            ];

            $wp_admin_bar->add_node($args);

        }, 9999);
    }
}
