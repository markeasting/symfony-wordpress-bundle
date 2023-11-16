<?php

namespace Metabolism\WordpressBundle\Loader;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Interface that can be added to anything that has to be registered during the 
 * 'init' hook. For example, `register_post_type()`, `add_shortcode()`, etc.
 * 
 * The register() method will be called by the WordpressBundle automatically.
 */
// #[AutoconfigureTag('wordpress.registerable')]
interface WordpressRegisterable
{

    /**
     * Will be called during the 'init' hook. 
     * Place anything here, like `register_post_type()` or `add_shortcode()`
     *
     * @return void
     */
    function register(): void;

}
