<?php

namespace Metabolism\WordpressBundle\Hook;
use Metabolism\WordpressBundle\Loader\WordpressRegisterable;

abstract class Hook implements WordpressRegisterable
{
    
    /**
     * Add your hooks here. For example `add_action('save_post', [$this, 'saveHook'], 10, 1)`
     *
     * @return void
     */
    // public abstract function register(): void;

    public function register(): void
    {
        dd('asd');
    }

}
