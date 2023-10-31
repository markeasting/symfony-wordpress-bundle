<?php

namespace Metabolism\WordpressBundle\Factory;

use Metabolism\WordpressBundle\Entity\Entity;

class Factory {

	/**
	 * Generate classname from string
	 * @param $str
	 * @return string
	 */
	public static function getClassname($str)
	{
		// non-alpha and non-numeric characters become spaces
		$str = preg_replace('/[^a-z0-9]+/i', ' ', $str);
		$str = trim($str);
		// uppercase the first character of each word
		$str = ucwords($str);

		return str_replace(" ", "", $str);
	}


	/**
	 * Retrieves the cache contents from the cache by key and group.
	 * @param $id
	 * @param string $type
	 * @param array $args
	 * @return bool|mixed
	 */
	protected static function loadFromCache($id, $type='object', $args=[]){

		if( $id == null || is_array($id) )
			return false;

        $key = $id;

        if( !empty($args) )
            $key .= crc32(json_encode($args));

		return wp_cache_get( $key, $type.'_factory' );
	}


	/**
	 * Saves the data to the cache.
	 * @param $id
	 * @param $object
	 * @param $type
	 * @param array $args
	 * @return bool
	 */
	protected static function saveToCache($id, $object, $type, $args=[]){

		if( $id == null || is_array($id) )
			return false;

        $key = $id;

        if( !empty($args) )
            $key .= crc32(json_encode($args));

		return wp_cache_set( $key, $object, $type.'_factory' );
	}


	/**
	 * Create entity
	 * @param $id
	 * @param $class
	 * @param bool $default_class
	 * @return Entity|mixed
	 */
	public static function create($id, $class, $default_class=false){

		if(empty($id))
			return false;

		$item = self::loadFromCache($id, $class);

		if( $item )
            return $item;

		$classname = self::getClassname($class);

		$app_classname = 'App\Entity\\'.$classname;
        $bundle_classname = $default_classname = 'Metabolism\WordpressBundle\Entity\\'.$classname;

        if( $default_class )
            $default_classname = 'Metabolism\WordpressBundle\Entity\\'.self::getClassname($default_class);

		if( class_exists($app_classname) && is_subclass_of($app_classname, $default_classname)  ){

            $item = new $app_classname($id);
		}
		else{

            if( class_exists($bundle_classname) )
				$item = new $bundle_classname($id);
			elseif( $default_class )
				$item = self::create($id, $default_class);
		}

		if( is_wp_error($item) || !$item || !$item->exist() )
			$item = false;

		self::saveToCache($id, $item, $class);

		return $item;
	}
}
