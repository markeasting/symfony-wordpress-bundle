<?php

namespace Metabolism\WordpressBundle\Action;

/**
 * Loaded only on Wordpress admin panel
 */
class AdminAction 
{

    /**
     * Called on the 'init' hook
     */
    public function init() {}

	public function deploymentBadge()
	{
		if (isset($_ENV['FORGE_BUILD_BADGE'])){
			add_action('wp_dashboard_setup', function() {
				wp_add_dashboard_widget('deployment-state', 'Wildpress deployment state', function() {
					echo '<span class="ab-label"><img src="'.$_ENV['FORGE_BUILD_BADGE'].'&v='.uniqid().'"/></span>';
				});
			});
		}
	}
}
