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
 * Basic functions file, here define the more basic functions for use in the rest of app
 */

function d($var) {
	echo '<pre>';
	print_r($var);
	echo '</pre>';
}

function make_url($url, $get = '')
{
	if (is_array($url)) {
		/* Controller */
		$url = BASE_PATH.'/'.$url[0];
		
		/* Action */
		array_shift($url);
		if ($url)
			$url .= $url[0];
			
		/* Params */
		array_shift($url);
		if ($url)
			$url .= '/'.implode('/', $url);
	} else {
		if (substr($url, 0, 1) == '/')
			$url = BASE_PATH.$url;
	}
	
	if ($get)
		$url .= '?'.$get;

	return $url;
}

function __($string, $params) {
	$vars = array();
	preg_match_all('/\:([a-zA-Z0-9]+)/', $string, $vars);
	foreach ($vars[0] as $k => $v) {
		$string = str_replace($v, $params[$vars[1][$k]], $string);
	}
	return $string;
}

function redirect($url)
{
	Header('location: '.make_url($url));
}