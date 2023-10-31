<?php


namespace Metabolism\WordpressBundle\Helper;

use ArrayAccess;

class OptionsHelper implements ArrayAccess
{
	private $objects;

    /**
     * Magic method to check properties
     *
     * @param $id
     * @return bool
     */
	public function __isset($id) {

		return $this->has($id);
	}


    /**
     * Magic method to load properties
     *
     * @param $id
     * @return null|string|array|object
     */
	public function __get($id) {

		return $this->getValue($id);
	}


    /**
     * Magic method to load properties
     *
     * @param $id
     * @param $args
     * @return null|string|array|object
     */
	public function __call($id, $args) {
		return $this->getValue($id);
	}


	/**
	 * @param $id
	 * @return bool
	 */
	public function has($id): bool
    {

        return (bool)$this->getValue($id);
	}


	/**
	 * @param $key
	 * @return null|string|array|object
	 */
	public function getValue($key){

        if( isset($this->objects[$key]) )
            return $this->objects[$key];

        $this->objects[$key] = get_option($key);

        return $this->objects[$key];
	}


	/**
	 * @param $key
	 * @param $value
	 * @param bool $updateField
	 * @param bool $autoload
	 * @return null|string|array|object
	 */
	public function setValue($key, $value, $updateField=true, $autoload=true){

		if( $updateField ){

			update_option($key, $value, $autoload);
			unset($this->objects[$key]);

			$value = $this->getValue($key);
		}
		else{

			$this->objects[$key] = $value;
		}

		return $value;
	}


	/**
	 * @param $offset
	 * @return bool
	 */
	public function offsetExists($offset): bool
    {
       return $this->has($offset);
    }

	/**
	 * @param $offset
	 * @return mixed
	 */
    #[\ReturnTypeWillChange]
	public function offsetGet($offset)
    {
        return $this->getValue($offset);
    }

	/**
	 * @param $offset
	 * @param $value
	 * @return void
	 */
	public function offsetSet($offset, $value): void
    {
        $this->setValue($offset, $value);
    }

	/**
	 * @param $offset
	 * @return void
	 */
	public function offsetUnset($offset): void
    {
        if( $this->has($offset) )
            unset($this->objects[$offset]);
    }
}
