<?php
/**
 * Magenta, PHP Lightweight and easy to use MVC Framework
 * 
 * @version 0.1
 * @package magenta
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
	
	public static function get($key) {
		if (array_key_exists($key, self::$data))
			return self::$data[$key];
			
		return array();
	}
}

?>