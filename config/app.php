<?php
/**
 * APP Config file
 *
 * This file returns an array that them can be accessed for example by
 * <code>
 * Config::load('app.base_path');
 * # Or
 * Config::load('app.template.layout');
 * </code>
 *
 * @package App
 * @author danybmx <dany@dpstudios.es>
 */

return array(
	'base_path' => 'http://localhost/magenta', // base_url of website
	'webtitle' => 'Magenta', // title of website
	'meta_title' => 'Framework', // default meta_title
	'meta_description' => '', // default meta_description
	'meta_tags' => '', // default meta_tags
	'template' => array(
		'layout' => 'application', // default layout for app
		'cache' => false, // cache database fields and template files
		'refresh' => false, // hours or false for never
		'compress' => false, // compress all html
	),
	'sass' => array(
		'cache' => true,
		'style' => 'compressed' // nested|expanded|compact|compressed
	),
	'database' => array(
		'charset' => 'utf8',
		'connection' => 'development', // using development connection by default
		'development' => 'mysql://root@localhost/flybikesManagement',
		#'production' => 'mysql://user:pwd@host/database'
	)
);