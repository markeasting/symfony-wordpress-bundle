<?php

namespace Metabolism\WordpressBundle\Plugin;

class PerformancePlugin
{

    public function __construct()
    {
        if (!is_admin()) {
            remove_action('init', 'check_theme_switched', 99);
        }

        // Disable custom fields meta box dropdown (very slow)
        add_filter( 'postmeta_form_keys', '__return_false' );

        // Disable feeds.
        add_action('do_feed', [$this, 'disableFeeds'], 1);
        add_action('do_feed_rdf', [$this, 'disableFeeds'], 1);
        add_action('do_feed_rss', [$this, 'disableFeeds'], 1);
        add_action('do_feed_rss2', [$this, 'disableFeeds'], 1);
        add_action('do_feed_atom', [$this, 'disableFeeds'], 1);

        // Disable comments feeds.
        add_action('do_feed_rss2_comments', [$this, 'disableFeeds'], 1);
        add_action('do_feed_atom_comments', [$this, 'disableFeeds'], 1);

        // Disable comments.
        add_filter('comments_open', '__return_false');

        // Remove language dropdown on login screen.
        add_filter('login_display_language_dropdown', '__return_false');

        // Remove generated icons.
        remove_action('wp_head', 'wp_site_icon', 99);

        // Remove shortlink tag from <head>.
        remove_action('wp_head', 'wp_shortlink_wp_head', 10);

        // Remove shortlink tag from HTML headers.
        remove_action('template_redirect', 'wp_shortlink_header', 11);

        // Remove Really Simple Discovery link.
        remove_action('wp_head', 'rsd_link');

        // Remove RSS feed links.
        remove_action('wp_head', 'feed_links', 2);

        // Remove all extra RSS feed links.
        remove_action('wp_head', 'feed_links_extra', 3);

        // Remove wlwmanifest.xml.
        remove_action('wp_head', 'wlwmanifest_link');

        // Remove meta rel=dns-prefetch href=//s.w.org
        remove_action('wp_head', 'wp_resource_hints', 2);

        // Remove relational links for the posts.
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);

        // Remove REST API link tag from <head>.
        remove_action('wp_head', 'rest_output_link_wp_head', 10);

        // Remove REST API link tag from HTML headers.
        remove_action('template_redirect', 'rest_output_link_header', 11);

        // Remove emojis.
        // WordPress 6.4 deprecated the use of print_emoji_styles() function, but it has
        // been retained for backward compatibility purposes.
        // https://make.wordpress.org/core/2023/10/17/replacing-hard-coded-style-tags-with-wp_add_inline_style/
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_filter('the_content_feed', 'wp_staticize_emoji');
        remove_filter('comment_text_rss', 'wp_staticize_emoji');
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

        // Remove oEmbeds.
        remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);
        remove_action('wp_head', 'wp_oembed_add_host_js');

        // Update login page image link URL.
        add_filter('login_headerurl', function () {
            return home_url(); });

        // Update login page link title.
        add_filter('login_headertext', function () {
            return get_bloginfo('name'); });

        // Remove Gutenberg's front-end block styles.
        add_action('wp_enqueue_scripts', [$this, 'removeBlockLibrary']);

        // Remove Gutenberg's global styles.
        // https://github.com/WordPress/gutenberg/pull/34334#issuecomment-911531705
        add_action('wp_enqueue_scripts', function () {
            wp_dequeue_style('global-styles');
        });

        // Remove classic theme styles.
        // https://github.com/WordPress/WordPress/commit/143fd4c1f71fe7d5f6bd7b64c491d9644d861355
        add_action('wp_enqueue_scripts', function () {
            wp_dequeue_style('classic-theme-styles');
        });

        // Remove the SVG Filters that are mostly if not only used in Full Site Editing/Gutenberg
        // Detailed discussion at: https://github.com/WordPress/gutenberg/issues/36834
        add_action('init', function () {
            remove_action('wp_body_open', 'gutenberg_global_styles_render_svg_filters');
            remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
        });

        // // Remove ?ver= query from styles and scripts.
        // function remove_script_version(string $url): string
        // {
        //     if (is_admin()) {
        //         return $url;
        //     }
        //     if ($url) {
        //         return esc_url(remove_query_arg('ver', $url));
        //     }
        //     return $url;
        // }

        // add_filter('script_loader_src', [$this, 'remove_script_version'], 15, 1);
        // add_filter('style_loader_src', [$this, 'remove_script_version'], 15, 1);

        // Remove contributor, subscriber and author roles.
        add_action('init', function () {
            remove_role('author');
            remove_role('contributor');
            remove_role('subscriber');
        });

        // Disable attachment pages and canonical redirect links.
        add_filter('template_redirect', [$this, 'attachment_redirect']);
        add_filter('redirect_canonical', function (string $url) {
            $this->attachment_redirect();
            return $url;
        }, 0, 1);

        // Disable attachment links.
        add_filter('attachment_link', function (string $url, int $id) {
            if ($attachment_url = wp_get_attachment_url($id)) {
                return $attachment_url;
            }
            return $url;
        }, 10, 2);

        // Discourage search engines from indexing in non-production environments.
        add_action('pre_option_blog_public', function () {
            return wp_get_environment_type() === 'production' ? 1 : 0;
        });
    }

	public function removeBlockLibrary()
	{
        wp_dequeue_style( 'core-block-supports' );
	    wp_dequeue_style( 'wp-block-library' );
	    wp_dequeue_style( 'wp-block-library-theme' );
	    wp_dequeue_style( 'wc-block-style' ); // Remove woocommerce block css
	    wp_dequeue_style( 'global-styles' ); // Remove theme.json
    }

    public function disableFeeds()
    {
        wp_redirect(home_url());
        exit;
    }

    /**
     * Disable attachment template loading and redirect to 404.
     * WordPress 6.4 introduced an update to disable attachment pages, but this
     * implementation is not as robust as the current one.
     * https://github.com/joppuyo/disable-media-pages/issues/41
     * https://make.wordpress.org/core/2023/10/16/changes-to-attachment-pages/
     * 
     * @return void
     */
    public function attachment_redirect(): void
    {
        if (is_attachment()) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            nocache_headers();
        }
    }
}
