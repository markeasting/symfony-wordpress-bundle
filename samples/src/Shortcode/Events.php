<?php

namespace App\Shortcode;

use Metabolism\WordpressBundle\Entity\PostCollection;
use Metabolism\WordpressBundle\Shortcode\Shortcode;

class Events extends Shortcode
{

    public function getName(): string
    {
        return 'my_events';
    }

    public function output(mixed $atts): string
    {
        // $attributes = shortcode_atts([
        //     'archived' => false,
        // ], $atts);
    
        // $events = new PostCollection();
    
        // $output = $this->twig->render('events/events.html.twig', [
        //     'events' => $events,
        //     'showMonths' => true,
        //     'archived' => $archived
        // ]);
    
        return 'Hello World!';
    }

}
