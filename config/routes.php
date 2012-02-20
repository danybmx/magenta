<?php
/**
 * Routes config file
 *
 * - Set the default controller / action
 * - Set alias for routing
 *
 * We can use (:segment) for get a url segment /segment/
 * We can use (:any) for get all url
 *
 * @package App
 * @author danybmx <dany@dpstudios.es>
 */

return array(
	'root' => array(
		'controller' => 'home',
		'action' => 'index',
		'params' => array()
	),
	'routes' => array(
		/* Admin routes, ¡don't touch! */
		'admin/login' => 'admin/login',
		'admin/logout' => 'admin/logout',
		'admin/index' => 'admin/index',
		'admin/(:segment)/(:segment)(:any)' => '$1/admin_$2$3',
		'admin/(:segment)' => '$1/admin_index',
	)
);