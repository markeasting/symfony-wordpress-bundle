<?php
/**
 * Plugin Name: Wildpress loader
 * Description: Load Wordpress in Symfony
 * Version: 2.2.0
 * Author: Wildsea
 * Author URI: https://wildsea.nl
 */

use Metabolism\WordpressBundle\WordpressBundle;
use Symfony\Component\ErrorHandler\Debug;

/**
 * NOTE: THIS FILE WILL BE OVERWRITTEN BY WORDPRESS BUNDLE
 */
if (is_admin() || is_login()) {

    $debug = $_SERVER['APP_ENV'] == 'dev' && (isset($_SERVER['ENABLE_ADMIN_EXCEPTION_HANDLER']) && $_SERVER['ENABLE_ADMIN_EXCEPTION_HANDLER'] === 'true');

    if ($debug) {
        Debug::enable();
    }

    $kernel = new \App\Kernel($_SERVER['APP_ENV'], $debug);
    $kernel->boot(); // Set up minimal DI container in admin interface
}

WordpressBundle::bootstrap();
