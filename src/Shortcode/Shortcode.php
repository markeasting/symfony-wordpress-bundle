<?php

namespace Metabolism\WordpressBundle\Shortcode;

use Metabolism\WordpressBundle\Loader\WordpressRegisterable;
use Twig\Environment as Twig;

/**
 * Base Shortcode class.
 * 
 * @TODO output() should get it's arguments from the service container, like a controller
 * 
 * https://codex.wordpress.org/Shortcode_API
 */
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
     * The stringified / html output of the shortcode. 
     * 
     * Using twig: `return $this->twig->render('someview.twig', [...])`
     * 
     * Using Plain php: 
     * ```
     * ob_start();
     * echo "<p>moi</p>"
     * return ob_get_clean();
     * ```
     * 
     * @param mixed $atts
     * @return string
     */
    public abstract function output(mixed $atts): string;

    /**
     * @inheritDoc
     *
     * @return void
     */
    public function register(): void
    {
        add_shortcode($this->getName(), [$this, 'output']);
    }

}
