<?php

namespace Metabolism\WordpressBundle\Hook;
use Metabolism\WordpressBundle\Loader\WordpressRegisterable;

/**
 * Base class for Wordpress hooks. You may use dependency injection in the constructor to add any services you require inside your hook functions. 
 * 
 * Example, add the following to register(): `add_action('save_post', [$this, 'saveHook'], 10, 1)`
 * 
 * https://developer.wordpress.org/reference/functions/add_action/
 */
abstract class Hook implements WordpressRegisterable
{
    
    /**
     * Add your hooks here. For example `add_action('save_post', [$this, 'saveHook'], 10, 1)`
     *
     * https://developer.wordpress.org/reference/functions/add_action/
     * 
     * @return void
     */
    public abstract function register(): void;

}
