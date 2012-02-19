<?php
/**
 *
 *
 * @package
 * @author danybmx <dany@dpstudios.es>
 */
class ActiveRecord_Model
{
	/**
	 * Model name
	 * @var string
	 */
	protected $_name;

	/**
	 * Used connection
	 * @var string
	 */
	protected $_connection;

	/**
	 * Table
	 * @var string
	 */
	protected $_table;

	/**
	 * PK
	 * @var string
	 */
	protected $_pk = 'id';

	/**
	 * Read only model (can not set values)
	 * @var bool
	 */
	protected $_read_only = false;

	/**
	 * Function for create a new instance of the model
	 *
	 * @param $model Model name
	 */
	public function __construct($model) {
		$this->_name = $model;
		$this->_connection = $this->_connection ? $this->_connection : Config::load('app.database.connection');
	}

	/**
	 * Getter
	 *
	 * Used for get value of fields and relations
	 *
	 * @param string $key Field name or relation to be loaded
	 * @return mixed Value of field or relation model
	 */
	public function __get($key) {
		if (property_exists($this, $key)) {
			return $this->$key;
		} else if (array_key_exists($key, $this->relations())) {
			return $this->load($key);
		} else {
			trigger_error($key.' is not defined in '.$this->_name);
		}
	}

	/**
	 * Setter
	 *
	 * Used for set values for the fields
	 *
	 * @param string $key key of field
	 * @param string $value value for the field
	 */
	public function __set($key, $value) {
		if ($this->_read_only) trigger_error('Read only model, can not make changes', E_USER_ERROR);

		if (array_key_exists($key, $this->relations())) {
			/* TODO */
		} else {
			$this->$key = $value;
		}
	}

	/**
	 * Function for get PDO Object of the connection
	 *
	 * @return PDO
	 */
	public function getConnection() {
		return Database_Connection::get($this->_connection)->getPDO();
	}

	/**
	 * Function for get the model name
	 *
	 * @return string Model name
	 */
	public function getName() {
		return $this->_name;
	}

	/**
	 * Function for get the primary key field name
	 *
	 * @return string pk field
	 */
	public function getPK() {
		return $this->_pk;
	}

	/**
	 * Function for get the Model table name
	 *
	 * @return string Model table name
	 */
	public function getTable() {
		return $this->_table;
	}

	/**
	 * Function for get the relations of the Model
	 *
	 * @return array Array with relations if setted or empty if not
	 */
	public function getRelations() {
		return $this->relations();
	}

	/**
	 * Function for get the fields of the model
	 *
	 * @return array Array with fields
	 */
	public function getFields() {
		$vars = get_class_vars($this->_name);
		$fields = array();
		foreach ($vars as $k => $v) {
			if (strpos($k, '_') !== 0)
				$fields[] = $k;
		}

		return $fields;
	}

	/**
	 * Function for get last id used
	 *
	 * @return string
	 */
	private function getLastId() {
		return $this->getConnection()->lastInsertId();
	}

	/**
	 * Function for populate model with data
	 *
	 * @param array $data Used for populate the model
	 * @param bool $fields If true, only set value if field exists
	 * @return Model
	 */
	public function create($data = array(), $fields = false) {
		foreach ($data as $k => $v) {
			if (( ! $fields || ($fields && array_key_exists($k, $fields))) && key_exists($k, $this)) {
				$this->$k = $v;
			}
		}
		$this->afterLoad();
		return $this;
	}

	/**
	 * Function for get the model in json
	 *
	 * @param array $relations
	 * @return string model in json
	 */
	public function toJson($relations = array()) {
		return json_encode($this->toArray($relations));
	}

	/**
	 * Function for get the model in array
	 *
	 * @param array $relations
	 * @return array model in array
	 */
	public function toArray($relations = array()) {
		$arr = array();

		foreach ($this->getFields() as $k => $f) {
			$arr[$f] = $this->$f;
		}

		if ($relations) {
			foreach ($relations as $r) {
				$arr[$r] = $this->load($r)->toArray();
			}
		}

		return $arr;
	}
}
