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
 * Magenta Core Class
 */
class Core
{
	/**
	 * Autoload function for all classes of the framework, including the next folders
	 * Defined in the bootstrap.php file
	 * 
	 * CLASSES
	 * MODELS
	 * CONTROLLERS
	 * VENDORS
	 * HELPERS
	 * COMPONENTS
	 * 
	 * @param string $class Name of the class for load it
	 * @return void
	 */
	public static function autoload($class)
	{
		if ( !class_exists('Inflector')) {
			$classfile = strtolower($class);
		} else {
			$file = str_replace('_', DS, $class);
			$files = explode('/', $file);
			$classfile = Inflector::underscore($files[0]);
			array_shift($files);

			if ($files) {
				foreach ($files as $k => $file) {
					$files[$k] = Inflector::underscore($file);
				}
			$classfile .= DS.implode('/', $files);
			}
		}
		$classfile .= '.php';
		
		$directories = array(
			CLASSES,
			MODELS,
			CONTROLLERS,
			VENDORS,
			HELPERS,
			COMPONENTS
		);

		foreach ($directories as $directory) {
			if (file_exists($directory.DS.$classfile)) {
				require_once $directory.DS.$classfile;
				break;
			}
		}
	}

	/**
	 * Function to launch the webApp
	 *
	 * - Check for routes
	 * - Load Controller
	 * - Launch Controller Filters
	 * - Launch Action
	 *
	 * @static
	 * @return void
	 */
	static function run()
	{
		Request::create();

		$controllerClass = ucfirst(Request::$controller).'Controller';
		if ( ! class_exists($controllerClass)) {
			header('Status: 404 Not Found');
			trigger_error('The controller "'.$controllerClass.'" does not exits');
		}

		if ( ! method_exists($controllerClass, Request::$action)) {
			header('Status: 404 Not Found');
			trigger_error('The action "'.Request::$action.'" of controller "'.$controllerClass.'" does not exists');
		}

		$dispatcher = new $controllerClass;
		$action = Request::$action;
		call_user_func_array(array($dispatcher, $action), Request::$params);
	}
}