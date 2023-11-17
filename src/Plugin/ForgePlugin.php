<?php

namespace Metabolism\WordpressBundle\Plugin;

use function Env\env;

class ForgePlugin
{

    /**
     * Laravel Forge build badge
     */
    public function __construct()
    {
        $badge = env('FORGE_BUILD_BADGE');

		if (!empty($badge)){
			add_action('wp_dashboard_setup', function() use ($badge) {

                if (!current_user_can('editor') && !current_user_can('administrator'))
                    return;

				wp_add_dashboard_widget('deployment-state', 'Wildpress deployment state', function() use ($badge) {
					echo '<span class="ab-label"><img src="'.$badge.'&v='.uniqid().'"/></span>';
				});
			});
		}
    }
}
