<?php

namespace Metabolism\WordpressBundle\Plugin;

class SecurityPlugin
{

    public function __construct() 
    {
        // Hide login error - don't expose existing accounts
        add_filter('login_errors', function(){
            return __('Invalid credentials', 'wp-steroids');
        });

        // Secure REST API
        add_filter('rest_endpoints', [$this, 'disable_rest_endpoints']);
        add_filter('rest_jsonp_enabled', '__return_false');

        // Disable XMLRPC
        add_filter('xmlrpc_enabled', '__return_false');
        add_filter('xmlrpc_methods', '__return_false');
        add_filter('pings_open', '__return_false');
        add_filter('x_redirect_by', '__return_false' );

        // Remove WordPress version
        remove_action('wp_head', 'wp_generator');

        add_action('wp_footer', function() {
            wp_deregister_script('wp-embed');
        });
    }

    /** 
     * Disable default users API endpoints for security. 
     * https://www.wp-tweaks.com/hackers-can-find-your-wordpress-username/
     */
    function disable_rest_endpoints(array $endpoints): array
    {
        if (!is_user_logged_in()) {
            if (isset($endpoints['/wp/v2/users'])) {
                unset($endpoints['/wp/v2/users']);
            }
    
            if (isset($endpoints['/wp/v2/users/(?P<id>[\d]+)'])) {
                unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
            }
        }
    
        return $endpoints;
    }


}
