<?php

/**
 * NOTE: THIS FILE WILL BE OVERWRITTEN BY WORDPRESS BUNDLE
 */

if (!class_exists('App') && !defined('ABSPATH')) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

use Env\Env;
use Symfony\Component\Dotenv\Dotenv;
use Metabolism\WordpressBundle\Loader\ConfigLoader;
use function Env\env;

if (!class_exists('App'))
    require dirname(__DIR__) . '/vendor/autoload.php';

if (!env('APP_ENV')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}

Env::$options = Env::USE_ENV_ARRAY;

$loader = new ConfigLoader();
$loader->import(dirname(__DIR__), '/config/packages/wordpress.yaml');

global $table_prefix;

$table_prefix = env('TABLE_PREFIX') ?: 'wp_';

require_once(ABSPATH . 'wp-settings.php');
