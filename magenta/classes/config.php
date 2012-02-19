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
 * Config class for get config parameters from /config files
 *
 * Singleton pattern for don't need load most of one time each file
 */
class Config
{
	/**
	 * Create a new item of array for each file.
	 * @var array
	 */
	protected static $_instances = array();

	/**
	 * Function for get the instance or create if not exists
	 *
	 * @example Config::load('app.base_path');
	 *
	 * @static
	 * @param string String for get the config in file
	 * @return void
	 */
	public static function load($string)
	{
		$file = preg_replace('/(.*?)\.(.*)/', '$1', $string);
		if ( ! array_key_exists($file, self::$_instances)) {
			self::$_instances[$file] = new self($file);
		}
		$class = self::$_instances[$file];
		return $class->getValue($string);
	}

	private $file = null;
	private $data = array();
	public function __construct($file)
	{
		$this->file = $file;
		if ( ! file_exists(CONFIG.DS.$file.'.php'))
			trigger_error('The config file '.$file.' does not exists');
		$this->data = include(CONFIG.DS.$file.'.php');
	}

	public function getValue($string)
	{
		$tree = explode('.', $string);
		array_shift($tree);

		$tmp = $this->data;
		foreach ($tree as $key) {
			if ( ! array_key_exists($key, $tmp))
				return false;
			$tmp = $tmp[$key];
		}
		return $tmp;
	}
}