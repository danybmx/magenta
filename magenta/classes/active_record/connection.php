<?php
/**
 *
 *
 * @package
 * @author danybmx <dany@dpstudios.es>
 */
class ActiveRecord_Connection
{
	protected static $_instances = array();
	protected static $_options = array(
		PDO::ATTR_CASE								=> PDO::CASE_NATURAL,
		PDO::ATTR_ERRMODE							=> PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_ORACLE_NULLS				=> PDO::NULL_NATURAL,
		PDO::ATTR_STRINGIFY_FETCHES		=> false,
		PDO::MYSQL_ATTR_INIT_COMMAND	=> "SET NAMES utf8"
	);

	public static function get($connection) {
		if ( ! array_key_exists($connection, self::$_instances))
			self::$_instances[$connection] = new self($connection);

		return self::$_instances[$connection];
	}

	private $_PDO;

	public function __construct($connection) {
		$connection_string = Config::load('app.database.'.$connection);
		if ( ! $connection_string)
			trigger_error('The connection '.$connection.' does not exists in configuration.', E_USER_ERROR);

		$connection_url = parse_url($connection_string);
		$dsn = $connection_url['scheme'].':host='.$connection_url['host'].';dbname='.substr($connection_url['path'], 1);
		$user = $connection_url['user'];
		$password = array_key_exists('pass', $connection_url) ? $connection_url['pass'] : '';

		try {
			$this->_PDO = new PDO($dsn, $user, $password, self::$_options);
		} catch (PDOException $e) {
			trigger_error(ucfirst($e->getMessage()), E_USER_ERROR);
		}
	}

	public function getPDO() {
		return $this->_PDO;
	}
}
