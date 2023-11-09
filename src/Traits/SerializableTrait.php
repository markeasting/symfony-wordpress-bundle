<?php

namespace Metabolism\WordpressBundle\Traits;

use Metabolism\WordpressBundle\Attribute\Serializable;

trait SerializableTrait
{

    public function jsonSerialize(): mixed
    {

        $r = new \ReflectionClass($this);
        $s = [];

        foreach($r->getProperties() as $prop) {
            $attr = $prop->getAttributes(Serializable::class);
            
            if ($attr) {
                $key = $prop->getName();
                $s[$key] = $this->{$key};
            }
        }

        // foreach($r->getMethods() as $method) {
        //     $attr = $method->getAttributes(Serializable::class);
            
        //     if ($attr) {
        //         if (
        //             $method->getReturnType() !== 'static' && 
        //             $method->getReturnType() !== 'void' &&
        //             $method->getNumberOfParameters() === 0
        //         ) {
        //             $key = $method->getName();
        //             $s[$key] = $method->invoke($this);
        //         }
        //     }
        // }

        return $s;
    }

}
