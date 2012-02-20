<?php
/**
 * Index file
 *
 * @package Magenta
 * @author danybmx <dany@dpstudios.es>
 */

/**
 * Init session
 */
session_start();

/**
 * Init output buffer
 */
ob_start();

/**
 * Define ROOT and alias DIRECTORY_SEPARATOR as DS
 */
define('ROOT', dirname(dirname(__FILE__)));
define('DS', DIRECTORY_SEPARATOR);

/**
 * Set default timezone
 */
date_default_timezone_set('Europe/Berlin');

/**
 * Define MAGENTA_TYPE
 */
define('MAGENTA_TYPE', 'WEB');

/**
 * Require bootstrap file
 */
require_once ROOT.DS.'magenta'.DS.'bootstrap.php';