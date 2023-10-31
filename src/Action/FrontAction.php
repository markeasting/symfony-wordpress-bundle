<?php

namespace Metabolism\WordpressBundle\Action;


/**
 * Class Metabolism\WordpressBundle Framework
 */
class FrontAction {

	/**
	 * @var string plugin domain name for translations
	 */
	public static $languages_folder;

	public static $domain_name = 'default';

	/**
	 * Init placeholder
	 */
	public function init(){}

	/**
	 * Loaded placeholder
	 */
	public function loaded(){}

	public function __construct()
	{
		if( defined('WP_INSTALLING') && WP_INSTALLING )
			return;

        add_action( 'kernel_loaded', [$this, 'loaded']);
        add_action( 'init', [$this, 'init']);
		add_action( 'init', '_wp_admin_bar_init', 0 );
	}
}
