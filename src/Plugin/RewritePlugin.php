<?php

namespace Metabolism\WordpressBundle\Plugin;

class RewritePlugin
{

    public function __construct()
    {
        // if (is_admin()) {
        //     add_action('load-options-permalink.php', [$this, 'loadPermalinks']);
        // }

        // add_action('generate_rewrite_rules', [$this, 'remove']);

        /* @TODO this doesn't seem to work */
        // add_action('update_option_permalink_structure', function() {
        //     // global $wp_rewrite;
        //     // $wp_rewrite->flush_rules(false);
        //     do_action('reset_cache');
        // });
    }

    // public function remove($wp_rewrite)
    // {
    //     $remove = [
    //         'author',
    //         'attachment',
    //         'embed',
    //         'trackback',
    //         'comment',
    //         'feed',
    //     ];

    //     foreach (['rules', 'extra_rules_top'] as $item) {

    //         foreach ($wp_rewrite->$item as $rule => $rewrite) {

    //             if (in_array('attachment', $remove) && (strpos($rule, '/attachment/') !== false || strpos($rewrite, 'attachment=') !== false))
    //                 unset($wp_rewrite->$item[$rule]);

    //             if (in_array('embed', $remove) && strpos($rule, '/embed/') !== false)
    //                 unset($wp_rewrite->$item[$rule]);

    //             if (in_array('feed', $remove) && (strpos($rule, '/(feed|rdf|rss|rss2|atom)/') !== false || strpos($rule, '/feed/') !== false))
    //                 unset($wp_rewrite->$item[$rule]);

    //             if (in_array('trackback', $remove) && strpos($rule, '/trackback/') !== false)
    //                 unset($wp_rewrite->$item[$rule]);

    //             if (in_array('comment', $remove) && strpos($rule, '/comment-page-') !== false)
    //                 unset($wp_rewrite->$item[$rule]);
    //         }
    //     }
    // }


    // public function loadPermalinks()
    // {
    //     $updated = false;

    //     add_settings_section('page_rewrite', '', '__return_empty_string', 'permalink');

    //     if (isset($_POST['page_rewrite_slug']) && !empty($_POST['page_rewrite_slug'])) {
    //         update_option('page_rewrite_slug', $_POST['page_rewrite_slug'], true);
    //         $updated = true;
    //     }

    //     add_settings_field('page_rewrite_slug', 'Page base', function () {
    //         $value = get_option('page_rewrite_slug');
    //         echo '<input type="text" value="' . esc_attr($value) . '" name="page_rewrite_slug" placeholder="page" id="page_rewrite_slug" class="regular-text" />';

    //     }, 'permalink', 'page_rewrite');

    //     add_settings_section('search_rewrite', '', '__return_empty_string', 'permalink');

    //     if (isset($_POST['search_rewrite_slug']) && !empty($_POST['search_rewrite_slug'])) {
    //         update_option('search_rewrite_slug', $_POST['search_rewrite_slug'], true);
    //         $updated = true;
    //     }

    //     add_settings_field('search_rewrite_slug', 'Search base', function () {
    //         $value = get_option('search_rewrite_slug');
    //         echo '<input type="text" value="' . esc_attr($value) . '" name="search_rewrite_slug" placeholder="search" id="search_rewrite_slug" class="regular-text" />';

    //     }, 'permalink', 'search_rewrite');

    //     add_settings_section('custom_post_type_rewrite', 'Custom post type', '__return_empty_string', 'permalink');

    //     foreach (get_post_types(['public' => true, '_builtin' => false], 'objects') as $post_type => $args) {
    //         foreach (['slug', 'archive'] as $type) {
    //             if (($type == 'slug' && is_post_type_viewable($post_type)) || ($type == 'archive' && $args->has_archive)) {
    //                 if (isset($_POST[$post_type . '_rewrite_' . $type]) && !empty($_POST[$post_type . '_rewrite_' . $type])) {
    //                     update_option($post_type . '_rewrite_' . $type, $_POST[$post_type . '_rewrite_' . $type], true);
    //                     $updated = true;
    //                 }

    //                 add_settings_field($post_type . '_rewrite_' . $type, __(ucfirst(str_replace('_', ' ', $post_type)) . ' ' . $type), function () use ($post_type, $type) {
    //                     $value = get_option($post_type . '_rewrite_' . $type);

    //                     // if(empty($value))
    //                     //     $value = $this->config->get('post_type.'.$post_type.($type=='slug'?'.rewrite.slug':'has_archive'), $post_type);

    //                     echo '<input type="text" value="' . esc_attr($value) . '" name="' . $post_type . '_rewrite_' . $type . '" placeholder="' . $post_type . '" id="' . $post_type . '_rewrite_' . $type . '" class="regular-text" />';

    //                     if ($type == 'slug') {

    //                         $taxonomy_objects = get_object_taxonomies($post_type);
    //                         if (!empty($taxonomy_objects))
    //                             echo '<p class="description">You can add %' . implode('%, %', $taxonomy_objects) . '%</p>';
    //                     }

    //                 }, 'permalink', 'custom_post_type_rewrite');
    //             }
    //         }
    //     }

    //     add_settings_section('custom_taxonomy_rewrite', 'Custom taxonomy', '__return_empty_string', 'permalink');

    //     foreach (get_taxonomies(['public' => true, '_builtin' => false], 'objects') as $taxonomy => $args) {
    //         if (!is_taxonomy_viewable($taxonomy))
    //             continue;

    //         if (isset($_POST[$taxonomy . '_rewrite_slug']) && !empty($_POST[$taxonomy . '_rewrite_slug'])) {
    //             update_option($taxonomy . '_rewrite_slug', $_POST[$taxonomy . '_rewrite_slug'], true);
    //             $updated = true;
    //         }

    //         add_settings_field($taxonomy . '_rewrite_slug', __(ucfirst(str_replace('_', ' ', $taxonomy)) . ' base'), function () use ($taxonomy) {
    //             $value = get_option($taxonomy . '_rewrite_slug');

    //             // if(empty($value))
    //             //     $value = $this->config->get('taxonomy.'.$taxonomy.'.rewrite.slug', $taxonomy);

    //             echo '<input type="text" value="' . esc_attr($value) . '" name="' . $taxonomy . '_rewrite_slug" placeholder="' . $taxonomy . '" id="' . $taxonomy . '_rewrite_slug" class="regular-text" />';
    //             echo '<p class="description">You can add %parent% or use %empty%</p>';

    //         }, 'permalink', 'custom_taxonomy_rewrite');
    //     }

    //     if ($updated) {

    //         global $wp_rewrite;
    //         $wp_rewrite->flush_rules(false);

    //         do_action('reset_cache');

    //     }
    // }

}
