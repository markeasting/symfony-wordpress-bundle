<?php

namespace Metabolism\WordpressBundle\Loader;

use Metabolism\WordpressBundle\Loader\WordpressRegisterable;

class WordpressLoader
{
    
    /**
     * Services implementing WordpressRegisterable will be injected automatically 
     * 
     * @see config/services.yaml
     * @param iterable<WordpressRegisterable> $registerables
     */
    public function __construct(
        private iterable $registerables
    ) {
        // dd(iterator_to_array($registerables));
    }

    /**
     * Will be called during the 'init' hook
     *
     * @return void
     */
    public function register()
    {
        foreach ($this->registerables as $item) {
            $item->register();
        }
    }

}
