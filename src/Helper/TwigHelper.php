<?php

namespace Metabolism\WordpressBundle\Helper;

use App\Twig\AppExtension;
use Metabolism\WordpressBundle\Twig\WordpressTwigExtension;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\FilesystemLoader;

class TwigHelper {

    private static $env;

	/**
	 * Todo: use real symfony twig env
	 *
	 * @return TwigEnvironment
	 */
	public static function getEnvironment(): TwigEnvironment
    {
		if( !is_null(self::$env) )
			return self::$env;

	    $loader = new FilesystemLoader(BASE_URI.'/templates');

	    $options = [];

	    if( WP_ENV != 'dev' && is_dir( BASE_URI.'/var/cache') )
		    $options['cache'] = BASE_URI.'/var/cache/'.WP_ENV.'/twig';

	    $twig = new TwigEnvironment($loader, $options);

	    if( class_exists('App\Twig\AppExtension'))
		    $twig->addExtension(new AppExtension());

	    if( class_exists('\Twig\Extra\Intl\IntlExtension'))
		    $twig->addExtension(new \Twig\Extra\Intl\IntlExtension());

	    $twig->addExtension(new WordpressTwigExtension());

		self::$env = $twig;

		return self::$env;
    }
}
