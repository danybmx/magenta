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
 * Class for manage, prepare and check if routes match
 */
class Router
{
	public static function checkIfMatch($route, $url)
	{
		$route = self::prepare($route);
		if (preg_match($route, $url)) {
			return true;
		}
		return false;
	}

	public static function getRoute($key, $route, $url)
	{
		$key = self::prepare($key);
		return preg_replace($key, $route, $url);
	}

	private static function prepare($route)
	{
		$route = addcslashes($route, '/._-');
		$from = array('(:segment)', '(:any)');
		$to = array('(.*?)(?=\/|$)', '(.*)');
		$route = str_replace($from, $to, $route);
		return '/'.$route.'/';
	}
}