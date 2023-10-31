<?php

use Metabolism\WordpressBundle\Routing\Permastruct;
use Symfony\Component\Routing\RouteCollection;

use function Env\env;

$collection = new RouteCollection();

if (!isset($_SERVER['SERVER_NAME']) && (!isset($_SERVER['WP_INSTALLED']) || !$_SERVER['WP_INSTALLED']))
    return $collection;

if (env('WP_MULTISITE') && !env('SUBDOMAIN_INSTALL')) {
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

