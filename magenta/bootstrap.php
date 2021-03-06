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

ini_set('display_errors', 'On');
error_reporting(E_ALL);

/**
 * Set default timezone
 */
date_default_timezone_set('Europe/Berlin');

/**
 * Bootstrap file, define more paths and load the framework and launch the web application
 */
define('Magenta', true);
define('TMP', ROOT.DS.'tmp');
define('CACHE', ROOT.DS.'tmp'.DS.'cache');
define('APP', ROOT.DS.'app');
define('CORE', ROOT.DS.'magenta');
define('CLASSES', CORE.DS.'classes');
define('SKELS', CORE.DS.'skels');
define('MODELS', APP.DS.'models');
define('VIEWS', APP.DS.'views');
define('CONTROLLERS', APP.DS.'controllers');
define('VENDORS', ROOT.DS.'vendors');
define('CONFIG', ROOT.DS.'config');
define('HELPERS', CORE.DS.'helpers');
define('COMPONENTS', CORE.DS.'components');
define('WEBROOT', ROOT.DS.'public');
define('ASSETS', WEBROOT);
define('LOCALES', APP.DS.'locale');

/* Magenta Paths */
define('MVIEWS', CORE.DS.'views');

require_once CORE.DS.'basics.php';
require_once CLASSES.DS.'magenta.php';

spl_autoload_register(array('Magenta', 'autoload'));

/**
 * Check and create needed folders
 */
Magenta::checkAndCreateFolders(array('tmp/cache/templates', 'tmp/cache/sass', 'database'));

/**
 * Launch framework for WEB or CLI
 */
if (MAGENTA_TYPE == 'WEB') {
	/**
	 * Set error and exception handlers
	 */
	set_error_handler(array('Error', 'errorHandler'));
	set_exception_handler(array('Error', 'exceptionHandler'));

	if (array_key_exists('locale', $_SESSION)) {
		define('LOCALE', $_SESSION['locale']);
	} else {
		define('LOCALE', Config::load('app.locale'));
		$_SESSION['locale'] = LOCALE;
	}

	/**
	 * Define BASE_PATH
	 */
	define('BASE_PATH', Config::load('app.base_path'));

	/**
	 * Launch the framework
	 */
	Magenta::run();
} else if (MAGENTA_TYPE == 'CLI') {
	
}