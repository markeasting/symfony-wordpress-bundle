<?php

namespace Metabolism\WordpressBundle\Shortcode;

use Metabolism\WordpressBundle\Loader\WordpressRegisterable;
use Twig\Environment as Twig;

abstract class Shortcode implements WordpressRegisterable
{
    
    public function __construct(
        protected Twig $twig
    ) {}

    /**
     * The name of the shortcode
     *
     * @return string
     */
    public abstract function getName(): string;

    /**
     * The stringified / html output of the shortcode
     *
     * @param mixed $atts
     * @return string
     */
    public abstract function output(mixed $atts): string;

    public function register(): void
    {
        add_shortcode($this->getName(), [$this, 'output']);
    }

}
