<?php
/**
 *
 *
 * @package ActiveRecord
 * @author danybmx <dany@dpstudios.es>
 */
class ActiveRecord_Core
{
	const RELATION_BELONGSTO = 1;
	const RELATION_HASMANY = 2;
	const RELATION_HASMANYANDBELONGSTOMANY = 3;

	const FETCH_ONE = 1;
	const FETCH_ALL = 2;

	protected static $_instances = array();

	/**
	 * Function for get a new instance of model
	 *
	 * @static
	 * @param string $model model name
	 * @return object
	 */
	public static function get($model) {
		return new $model($model);
	}
}
