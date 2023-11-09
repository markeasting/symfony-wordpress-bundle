<?php

namespace Metabolism\WordpressBundle\Action;

/**
 * Class Metabolism\WordpressBundle Framework
 */
class AdminAction {

	/**
	 * Init placeholder
	 */
	public function init(){}


	/**
	 * Prevent Backend access based on ip whitelist
	 */
	private function lock()
	{
		$whitelist = getenv('ADMIN_IP_WHITELIST');

		if( $whitelist ){

			$whitelist = array_map('trim', explode(',', $whitelist));

			if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) )
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			else
				$ip = $_SERVER['REMOTE_ADDR'];

			if( !in_array($ip, $whitelist) )
				wp_die('Sorry, you are not allowed to access this page. Your IP: '.$ip);
		}
	}

	public function deploymentBadge()
	{
		if (isset($_ENV['FORGE_BUILD_BADGE'])){

			add_action('wp_dashboard_setup', function() {
				wp_add_dashboard_widget('deployment-state', 'Wildpress deployment state', function() {
					echo '<span class="ab-label"><img src="'.$_ENV['FORGE_BUILD_BADGE'].'&v='.uniqid().'"/></span>';
				});
			});

			// add_action('admin_bar_menu', function(\WP_Admin_Bar $wp_admin_bar)	{

			// 	$args = [
			// 		'id'    => 'forge-deployment',
			// 		'title' => '<span class="ab-icon">
			// 			<span class="ab-label"><img src="'.$_ENV['FORGE_BUILD_BADGE'].'&v='.uniqid().'"/></span>
			// 		</span>',
			// 		'href'  => '#'
			// 	];

			// 	$wp_admin_bar->add_node( $args );

			// }, 999);
		}
	}

	public function __construct()
	{
		if( defined('WP_INSTALLING') && WP_INSTALLING )
			return;

		$this->lock();

		add_action( 'admin_init', [$this, 'init'], 99 );
		add_action( 'admin_init', [$this, 'deploymentBadge'], 99 );
	}
}
