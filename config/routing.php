<?php

use Metabolism\WordpressBundle\Routing\Permastruct;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

use function Env\env;

$collection = new RouteCollection();

if (!isset($_SERVER['SERVER_NAME']) && (!isset($_SERVER['WP_INSTALLED']) || !$_SERVER['WP_INSTALLED']))
    return $collection;

if (function_exists('pll_languages_list')) {

    flush_rewrite_rules();

    foreach (pll_languages_list() as $locale) {
        new Permastruct($collection, $locale);
    }

    $root = new Route('/', ['_controller' => '\App\Controller\BlogController::fallbackAction']);
    $collection->add('root', $root->setMethods(['GET']));

} else if (class_exists('TRP_Translate_Press') && function_exists('trp_get_languages')) {

    throw new Exception('TranslatePress is currently not supported');

    // flush_rewrite_rules();

    // // $trp_obj = TRP_Translate_Press::get_trp_instance();
    // // $settings_obj = $trp_obj->get_component('settings');

    // // /** @var TRP_Languages */
    // // $lang_obj = $trp_obj->get_component('languages');

    // // $default_lang_labels = $settings_obj->get_setting('default-language');
    // // $published_lang = $settings_obj->get_setting('publish-languages');
    // // $published_lang_labels = $lang_obj->get_language_names($published_lang);

    // foreach (trp_get_languages() as $iso_code => $locale) {
    //     $code = strtok($iso_code, '_');
    //     new Permastruct($collection, $code);
    // }

    // $root = new Route('/', ['_controller' => '\App\Controller\BlogController::fallbackAction']);
    // $collection->add('root', $root->setMethods(['GET']));

} else if (env('WP_MULTISITE') && !env('SUBDOMAIN_INSTALL')) {
    $current_site_id = get_current_blog_id();

    foreach (get_sites() as $site) {
        switch_to_blog($site->blog_id);
        flush_rewrite_rules();

        $locale = trim($site->path, '/');
        new Permastruct($collection, $locale);
    }

    switch_to_blog($current_site_id);

} else {
    flush_rewrite_rules();
    new Permastruct($collection);
}

return $collection;

