<?php

namespace Metabolism\WordpressBundle\Attribute;

use \Attribute;

/**
 * @see \Metabolism\WordpressBundle\Traits\SerializableTrait
 * 
 * @TODO support Attribute::TARGET_METHOD
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Serializable
{
    //
}
