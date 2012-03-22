<?php
/**
 * Class Core of the active record
 *
 * Manage instances and set some constants
 *
 * @package ActiveRecord
 * @author danybmx <dany@dpstudios.es>
 */
class ActiveRecord_Core
{
	const RELATION_BELONGSTO = 1;
	const RELATION_HASONE = 2;
	const RELATION_HASMANY = 3;
	const RELATION_HASMANYANDBELONGSTOMANY = 4;

	const FETCH_ONE = 1;
	const FETCH_ALL = 2;

    const INSERT = 1;
    const UPDATE = 2;

	/**
	 * Array of instances
	 * @var array
	 */
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
	
	/**
	 * Function for execute sql code
	 **/
	public static function query($sql, $params = array(), $connection = null, $string = false) {
		try {
			$connection = ActiveRecord_Connection::get($connection, $string)->getPDO();
			$statement = $connection->prepare($sql);
			$statement->setFetchMode(PDO::FETCH_OBJ);
			$statement->execute($params);
			return $statement;
		} catch (PDOException $e) {
			trigger_error($e->getMessage().' <br /><p class="details">'.$sql.'</p>', E_USER_ERROR);
		}
	}
}
