<?php
/**
 * Class for manage PDO connections for ActiveRecord
 *
 * @package ActiveRecord
 * @author danybmx <dany@dpstudios.es>
 */
class ActiveRecord_Connection
{
	/**
	 * Instances of diferent connections
	 * @var array
	 */
	protected static $_instances = array();

	/**
	 * Options for PDO Object
	 * @var array
	 */
	protected static $_options = array(
		PDO::ATTR_CASE								=> PDO::CASE_NATURAL,
		PDO::ATTR_ERRMODE							=> PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_ORACLE_NULLS						=> PDO::NULL_NATURAL,
		PDO::ATTR_STRINGIFY_FETCHES					=> false,
		PDO::MYSQL_ATTR_INIT_COMMAND				=> "SET NAMES utf8"
	);

	/**
	 * Function for create or get connection
	 *
	 * @static
	 * @param $connection
	 * @return mixed
	 */
	public static function get($connection = null, $string = false) {
		if ($connection == null) $connection = Config::load('app.database.connection');
		if ( ! array_key_exists($connection, self::$_instances))
			self::$_instances[$connection] = new self($connection, $string);

		return self::$_instances[$connection];
	}

	/**
	 * PDO Object
	 * @var PDO
	 */
	private $_PDO;

	/**
	 * Function for construct the connection and create the PDO Object
	 *
	 * @param $connection
	 */
	public function __construct($connection, $string = false) {
		$connection_string = $string ? $connection : Config::load('app.database.'.$connection);
		
		if ( ! $connection_string)
			trigger_error('The connection '.$connection.' does not exists in configuration.', E_USER_ERROR);

		$connection_url = parse_url($connection_string);
		$dsn = $connection_url['scheme'].':host='.$connection_url['host'];
		if (array_key_exists('path', $connection_url) && $connection_url['path'])
			$dsn .= ';dbname='.substr($connection_url['path'], 1);
		$user = $connection_url['user'];
		$password = array_key_exists('pass', $connection_url) ? $connection_url['pass'] : '';

		try {
			$this->_PDO = new PDO($dsn, $user, $password, self::$_options);
		} catch (PDOException $e) {
			trigger_error(ucfirst($e->getMessage()), E_USER_ERROR);
		}
	}

	/**
	 * Function for get the PDO Object
	 *
	 * @return PDO
	 */
	public function getPDO() {
		return $this->_PDO;
	}
}
