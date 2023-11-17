<?php

namespace Metabolism\WordpressBundle\Plugin;


/**
 * Class
 */
class EditorPlugin
{

    public function __construct()
    {
        if (is_admin()) {
            add_action('admin_head', [$this, 'adminHead']);
        }

        add_action('wp_before_admin_bar_render', function () {
            global $wp_admin_bar;
            $wp_admin_bar->remove_menu('customize');
        });

        if (is_admin()) {
            add_action('admin_menu', [$this, 'cleanAdminMenu']);
            add_action('wp_dashboard_setup', [$this, 'disableDashboardWidgets']);
            add_action('admin_init', [$this, 'addThemeOptionsCap']);

            add_filter('post_row_actions', [$this, 'rowActions'], 10, 2);
            add_filter('page_row_actions', [$this, 'rowActions'], 10, 2);
        }

        add_action('admin_bar_menu', [$this, 'updateAdminBar'], 80);
    }

    public function adminHead()
    {

        $entrypoints = BASE_URI . PUBLIC_DIR . '/build/entrypoints.json';

        if (file_exists($entrypoints)) {

            $entrypoints = json_decode(file_get_contents($entrypoints), true);

            if ($entrypoints = $entrypoints['entrypoints']['backoffice'] ?? false) {

                foreach ($entrypoints['js'] ?? [] as $file)
                    echo '<script src="' . $file . '"></script>'; foreach ($entrypoints['css'] ?? [] as $file)
                    echo '<link rel="stylesheet" href="' . $file . '" media="all"/>';
            }

            echo "\n";
        }

        echo "<style>.form-table.permalink-structure, .form-table.permalink-structure+h2{ display:none }</style>";
    }


    /**
     * @param $wp_admin_bar
     */
    public function updateAdminBar($wp_admin_bar)
    {
        $wp_admin_bar->remove_menu('customize');
        $wp_admin_bar->remove_node('themes');
        $wp_admin_bar->remove_node('updates');
        $wp_admin_bar->remove_node('wp-logo');
        $wp_admin_bar->remove_node('comments');

        $object = get_queried_object();

        if (is_post_type_archive() && !is_admin()) {
            $args = [
                'id' => 'edit',
                'title' => __t('Edit ' . $object->label),
                'href' => get_admin_url(null, '/edit.php?post_type=' . $object->name),
                'meta' => ['class' => 'ab-item'],
            ];

            $wp_admin_bar->add_node($args);
        }

        global $pagenow;

        if (is_admin() && 'edit.php' === $pagenow && isset($_GET['post_type'], $_GET['page']) && $_GET['page'] == "options_" . $_GET['post_type']) {

            $object = get_post_type_object($_GET['post_type']);

            $args = [
                'id' => 'archive',
                'title' => __t($object->labels->view_items),
                'href' => get_post_type_archive_link($_GET['post_type']),
                'meta' => ['class' => 'ab-item'],
            ];

            $wp_admin_bar->add_node($args);
        }
    }

    /**
     * Filter admin menu entries
     */
    public function cleanAdminMenu()
    {
        $remove_menu_page = [
            'edit-comments.php',
            'jetpack'
        ];

        $remove_submenu_page = [
            'themes.php' => [
                'nav-menus.php',
                'widgets.php'
            ]
        ];
        
        foreach ((array) $remove_menu_page as $menu) {
            remove_menu_page($menu);
        }

        foreach ((array) $remove_submenu_page as $menu => $submenu) {
            remove_submenu_page($menu, $submenu);
        }

        if (isset($submenu['themes.php'])) {
            foreach ($submenu['themes.php'] as $index => $menu_item) {
                if (in_array('customize', $menu_item))
                    unset($submenu['themes.php'][$index]);
            }

            if (empty($submenu['themes.php']))
                remove_menu_page('themes.php');
        }
    }


    /**
     * Disable widgets
     */
    function disableDashboardWidgets()
    {
        remove_meta_box('dashboard_site_health', 'dashboard', 'normal'); // Site Health Status
        remove_meta_box('dashboard_activity', 'dashboard', 'normal');         // Activity
        remove_meta_box('dashboard_right_now', 'dashboard', 'normal'); // At a Glance
        remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');   // Incoming Links
        remove_meta_box('dashboard_plugins', 'dashboard', 'normal');          // Plugins
        remove_meta_box('dashboard_quick_press', 'dashboard', 'side');        // Quick Press
        remove_meta_box('dashboard_primary', 'dashboard', 'side');            // WordPress blog
        remove_meta_box('dashboard_secondary', 'dashboard', 'side');          // Other WordPress News
        remove_action('welcome_panel', 'wp_welcome_panel');                   // Remove WordPress Welcome Panel
    }

    /**
     * Update editor role
     */
    public function addThemeOptionsCap()
    {
        $role_object = get_role('editor');

        if (!$role_object->has_cap('edit_theme_options'))
            $role_object->add_cap('edit_theme_options');

    }

    /**
     * @param $actions
     * @param $post
     * @return mixed
     */
    public function rowActions($actions, $post)
    {
        $post_type_object = get_post_type_object(get_post_type($post));

        if (!$post_type_object->query_var && !$post_type_object->_builtin)
            unset($actions['view']);

        return $actions;
    }

}
