<?php

namespace Metabolism\WordpressBundle\Routing;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

class Permastruct
{

    public RouteCollection $collection;
    private \WP_Rewrite $wp_rewrite;

    private string $controller_name;
    private string $locale;

    /**
     * Permastruct constructor.
     *
     * @param RouteCollection $collection
     * @param string $locale
     * @param string $controller_name
     */
    public function __construct(RouteCollection $collection, $locale = '', $controller_name = 'BlogController')
    {
        global $wp_rewrite;

        $this->collection = $collection;
        $this->controller_name = $controller_name;
        $this->locale = $locale;
        $this->wp_rewrite = $wp_rewrite;

        $this->addRoutes();
    }

    /**
     * Define all routes from extra_permastructs and post type archive
     */
    public function addRoutes()
    {

        $this->addRoute('robots', 'robots.txt', [], false, \Metabolism\WordpressBundle\Helper\RobotsHelper::class . '::doAction');
        $this->addRoute('_site_health', '_site-health', [], false, \Metabolism\WordpressBundle\Helper\SiteHealthHelper::class . '::check');

        if (empty($this->locale)) {
            // $this->addRoute('_cache_purge', '_cache/purge', [], false, \Metabolism\WordpressBundle\Helper\CacheHelper::class . '::purge');
            // $this->addRoute('_cache_clear', '_cache/clear', [], false, \Metabolism\WordpressBundle\Helper\CacheHelper::class . '::clear');
        }

        global $_config;
        $remove_rewrite_rules = $_config ? $_config->get('rewrite_rules.remove', []) : [];

        if (!in_array('feed', $remove_rewrite_rules))
            $this->addRoute('feed', '{feed}', ['feed' => 'feed|rdf|rss|rss2|atom'], false, \Metabolism\WordpressBundle\Helper\FeedHelper::class . '::doAction');

        $this->addRoute('home', '', [], get_option('show_on_front') == 'posts');

        global $wp_post_types;

        foreach ($wp_post_types as $post_type) {
            if ($post_type->public && $post_type->publicly_queryable && $post_type->has_archive) {

                $base_struct = is_string($post_type->has_archive) ? $post_type->has_archive : $post_type->name;
                $translated_slug = get_option($post_type->name . '_rewrite_archive');
                $struct = empty($translated_slug) ? $base_struct : $translated_slug;

                $this->addRoute($post_type->name . '_archive', $struct, [], true);
            }
        }

        foreach ($this->wp_rewrite->extra_permastructs as $name => $params) {

            if (!$params['with_front'])
                continue;

            preg_match_all('/%([^%]+)%/m', $params['struct'], $matches, PREG_SET_ORDER, 0);

            $requirements = [];

            foreach ($matches as $match) {

                $position = array_search($match[0], $this->wp_rewrite->rewritecode, true);

                if (false !== $position)
                    $requirements[$match[1]] = $this->wp_rewrite->rewritereplace[$position];
            }

            $this->addRoute($name, $params['struct'], $requirements, $params['paged']);
        }

        if (isset($this->wp_rewrite->author_structure) && !in_array('author', $remove_rewrite_rules))
            $this->addRoute('author', $this->wp_rewrite->author_structure);

        if (isset($this->wp_rewrite->search_structure)) {

            $this->addRoute('search', $this->wp_rewrite->search_structure, [], true);
            $this->addRoute('empty_search', str_replace('/%search%', '', $this->wp_rewrite->search_structure), [], false, $this->getControllerName('search'));
        }

        if (isset($this->wp_rewrite->page_structure))
            $this->addRoute('page', $this->wp_rewrite->page_structure, ['pagename' => '[a-zA-Z0-9]{2}[^/].*']);

        if (isset($this->wp_rewrite->permalink_structure) && substr($this->wp_rewrite->page_structure ?? '', 0, 1) != '%')
            $this->addRoute('post', $this->wp_rewrite->permalink_structure, ['postname' => '[a-zA-Z0-9]{2}[^/].*']);
    }


    /**
     * @param $name
     * @return string
     */
    private function getControllerName($name)
    {
        $methodName = str_replace('_parent', '', $name);
        $methodName = str_replace(' ', '', lcfirst(ucwords(str_replace('_', ' ', $methodName))));
        $method = $methodName . 'Action';
        
        $class = '\App\Controller\\' . $this->controller_name;

        $fallback = str_contains($name, 'archive') 
            ? 'fallbackArchiveAction' 
            : 'fallbackAction';

        return method_exists($class, $method)
            ? $class . '::' . $method
            : $class . '::' . $fallback;
    }

    /**
     * @param $struct
     * @return array
     */
    private function getPaths($struct)
    {

        $path = str_replace('%/', '}/', str_replace('/%', '/{', $struct));
        $path = preg_replace('/\%$/', '}/', preg_replace('/^\%/', '/{', $path));
        $path = trim($path, '/');
        $path = !empty($this->locale) ? $this->locale . '/' . $path : $path;

        return ['singular' => $path, 'archive' => $path . (substr($path, -1, 1) == '/' ? '' : '/') . $this->wp_rewrite->pagination_base . '/{page}'];
    }

    /**
     * @param string $name
     * @param string $struct
     * @param array $requirements
     * @param bool $paginate
     * @param string $controllerName
     */
    public function addRoute(string $name, string $struct, array $requirements = [], bool $paginate = false, string|bool $controllerName = false)
    {
        $struct = apply_filters('routing_struct', $struct, $name);
        $requirements = apply_filters('routing_requirements', $requirements, $struct, $name);

        $name = str_replace('_structure', '', $name);

        $controller = $controllerName ?: $this->getControllerName($name);
        $paths = $this->getPaths($struct);

        $locale = $this->locale ? '.' . $this->locale : '';

        $route = new Route($paths['singular'], ['_controller' => $controller], $requirements);
        $route->setMethods('GET');

        $this->collection->add($name . $locale, $route);

        if ($paginate && !empty($paths['archive'])) {
            $route = new Route($paths['archive'], ['_controller' => $controller], $requirements);
            $route->setMethods('GET');

            $this->collection->add($name . '_paged' . $locale, $route);
        }
    }
}