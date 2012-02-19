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
 * Class for obtain the data of the reguest (real URL, GET, POST, FILES)
 */
class Request {
	static $uri, $url, $route, $get, $post, $files;

	static $method = null;
	static $secure = false;
	static $ajax = false;
	static $admin = false;
	static $controller = null;
	static $action = null;
	static $params = array();

	/**
	 * Function for create the request
	 *
	 * - Get url
	 * - Get method
	 *
	 * @static
	 */
	static function create()
	{
		self::$uri = substr(str_replace(dirname(dirname($_SERVER['SCRIPT_NAME'])), '', $_SERVER['REQUEST_URI']), 1);
		$get = explode('?', self::$uri);

		if (count($get) > 1) {
			self::$url = $get[0];
			$get = $get[1];
			parse_str($get, self::$get);
		} else {
			self::$url = self::$uri;
		}
		if ( ! self::$url) self::$url = Config::load('routes.root.controller').'/'.
																		Config::load('routes.root.action').'/'.
																		implode('/', Config::load('routes.root.params'));
		self::$url = preg_replace('/\/$/', '', self::$url);

		self::$post = $_POST;
		self::$files = $_FILES;

		/**
		 * Get method
		 */
		self::$method = $_SERVER['REQUEST_METHOD'];

		/**
		 * Check if SSL
		 */
		if (array_key_exists('HTTPS', $_SERVER) && strtolower($_SERVER['HTTPS']) == 'on')
			self::$secure = true;

		/**
		 * Check if Ajax
		 */
		if (array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] == 'xmlhttprequest'))
				self::$ajax = true;

		$routes = Config::load('routes.routes');
		foreach ($routes as $key => $route) {
			if (Router::checkIfMatch($key, self::$url)) {
				self::$route = Router::getRoute($key, $route, self::$url);
				break;
			} else {
				self::$route = self::$url;
			}
		}

		/**
		 * Check if is admin
		 */
		if (preg_match('/^admin/', self::$route))
			self::$admin = true;

		$route = explode('/', self::$route);
		self::$controller = $route[0];
		array_shift($route);
		self::$action = count($route) > 0 ? $route[0] : 'index';
		array_shift($route);
		self::$params = $route;
	}
}
