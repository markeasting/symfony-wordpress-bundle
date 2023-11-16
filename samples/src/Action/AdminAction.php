<?php

namespace App\Action;

use Metabolism\WordpressBundle\Action\AdminAction as WordpressAdminAction;

/**
 * Loaded only on Wordpress admin panel
 */
class AdminAction extends WordpressAdminAction 
{

	public function init() 
    {

        /* Remove admin dashboard widgets. */
        add_action('wp_dashboard_setup', function () {
            remove_meta_box('dashboard_activity', 'dashboard', 'normal'); // Activity
            remove_meta_box('dashboard_right_now', 'dashboard', 'normal'); // At a Glance
            remove_meta_box('dashboard_site_health', 'dashboard', 'normal'); // Site Health Status
            remove_meta_box('dashboard_primary', 'dashboard', 'side'); // WordPress Events and News
            remove_meta_box('dashboard_quick_press', 'dashboard', 'side'); // Quick Draft
        });
    }

}
