<?php
/**
 * Magenta, PHP Lightweight and easy to use MVC Framework
 * 
 * @version 0.1
 * @package Magenta
 * @author dpStudios Development Team
 * @copyright dpStudios 2009-2011
 * @link http://magenta.dpstudios.es
 */

/**
 * Magenta Data Class
 */
class Data
{
	private static $data = array();
	
	public static function set($key, $data) {
		self::$data[$key] = $data;
	}
	
	public static function get($key, $sub = null) {
		if (array_key_exists($key, self::$data))
			if ($sub) {
				if (array_key_exists($sub, self::$data[$key]))
					return self::$data[$key][$sub];
			} else
				return self::$data[$key];
			
		return array();
	}
}

?>