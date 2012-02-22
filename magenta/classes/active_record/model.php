<?php
/**
 *
 *
 * @package ActiveRecord
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
	 * Array for store errors from validator
	 * @var array
	 */
	protected $_errors = array();

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
			#TODO
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
		return ActiveRecord_Connection::get($this->_connection)->getPDO();
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
	 * Function for get errors on validation
	 *
	 * @return array Array with errors
	 */
	public function getErrors() {
		return $this->_errors;
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
		$this->beforeLoad();
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
	 * Function for get all data of model
	 *
	 * @param array $relations
	 * @return array all vars of model in array
	 */
	public function getData($relations = array()) {
		$arr = array();

		foreach (get_object_vars($this) as $k => $f) {
			if (substr($k, 0, 1) == '_') continue;
			$arr[$k] = $f;
		}

		if ($relations) {
			foreach ($relations as $r) {
				$arr[$r] = $this->$r->toArray();
			}
		}

		return $arr;
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
				$arr[$r] = $this->$r->toArray();
			}
		}

		return $arr;
	}

	/**
	 * Function for query the database
	 *
	 * @param string $sql the query
	 * @param array $params the params
	 * @return PDOStatement
	 */
	public function query($sql, $params = array()) {
		try {
			$statement = $this->getConnection()->prepare($sql);
			$statement->setFetchMode(PDO::FETCH_OBJ);
			$statement->execute($params);
			return $statement;
		} catch (PDOException $e) {
			trigger_error($e->getMessage().' <br /><p class="details">'.$sql.'</p>', E_USER_ERROR);
		}
	}

	/**
	 * Function for load a relation
	 *
	 * @param string $relation Name of the relation
	 * @return mixed
	 */
	public function load($relation) {
		$rs = $this->getRelations();
		$r = $rs[$relation];

		$class = array_key_exists('class', $r) ? $r['class'] : Inflector::classify($relation);
		$options = array_key_exists('options', $r) ? $r['options'] : array();

		switch ($r['type']) {
			case ActiveRecord::RELATION_BELONGSTO:
				# Fetch One
				$local_key = array_key_exists('local', $r) ? $r['local'] : strtolower($class).'_id';
				$foreign_key = array_key_exists('foreign', $r) ? $r['foreign'] : 'id';
				$data = ActiveRecord::get($class)->findBy($foreign_key, $this->$local_key, $options);
			break;
			case ActiveRecord::RELATION_HASMANY:
			break;
			case ActiveRecord::RELATION_HASMANYANDBELONGSTOMANY:
			break;
			default:
				trigger_error('The relation type setted does not exists', E_USER_ERROR);
			break;
		}

		return $data;
	}

	/**
	 * Function for find in database
	 *
	 * @param array $options
	 * @return mixed fetched object/s
	 */
	public function find($options = array()) {
		$qb = new ActiveRecord_QueryBuilder($this);
		$query = $qb->select($options);

		$statement = $this->query($query['sql'], $query['params']);
		if ($query['fetch'] == ActiveRecord::FETCH_ALL)
			return $this->fetchAll($statement);
		else
			return $this->fetchOne($statement);
	}

	/**
	 * Function for get first item (the oldest)
	 *
	 * @param array $options
	 * @return mixed
	 */
	public function first($options = array()) {
		return $this->find(array_merge(array('limit' => 1, 'order' => $this->getPK().' ASC'), $options));
	}

	/**
	 * Function for get last item (the newest)
	 *
	 * @param array $options
	 * @return mixed
	 */
	public function last($options = array()) {
		return $this->find(array_merge(array('limit' => 1, 'order' => $this->getPK().' DESC'), $options));
	}

	/**
	 * Function for find one by a param
	 *
	 * @param $field
	 * @param $value
	 * @param array $options
	 * @return mixed
	 */
	public function findBy($field, $value, $options = array()) {
		$options = array_merge(array('condition' => $field.' = ?', 'params' => $value, 'limit' => 1), $options);
		return $this->find($options);
	}

	/**
	 * Function for find all by a param
	 *
	 * @param $field
	 * @param $value
	 * @param array $options
	 * @return mixed
	 */
	public function findAllBy($field, $value, $options = array()) {
		$options = array_merge(array('condition' => $field.' = ?', 'params' => $value), $options);
		return $this->find($options);
	}

	/**
	 * Function for find a item by PK
	 *
	 * @param $pk
	 * @param array $options
	 * @return mixed
	 */
	public function findByPK($pk, $options = array()) {
		return $this->find(array_merge(array('limit' => 1, 'condition' => $this->getPK().' = ?', 'params' => $pk), $options));
	}

	/**
	 * Function for delete a item
	 *
	 * @param $value
	 * @param bool $cascade
	 * @param null $field
	 * @return PDOStatement
	 */
	public function delete($value, $cascade = false, $field = null)
	{
		$field = $field ? $field : $this->getPK();
		$qb = new ActiveRecord_QueryBuilder($this);
		$query = $qb->delete($field, $value, $cascade);
		return $this->query($query['sql'], $query['params']);
	}

	/**
	 * Function for fetch all items and return a collection with all
	 *
	 * @param $statement
	 * @return ActiveRecord_Collection
	 */
	public function fetchAll($statement) {
		$items_for_collection = array();
		foreach ($statement->fetchAll() as $r) {
			$items_for_collection[] = ActiveRecord::get($this->_name)->create($r);
		}
		$collection = new ActiveRecord_Collection();
		$collection->addItems($items_for_collection);
		return $collection;
	}

	/**
	 * Function for create a combo with data of two fields (one the key and other the value)
	 *
	 * @param $key
	 * @param $value
	 * @param array $options
	 */
	public function createCombo($key, $value, $options = array()) {
		$items = $this->find(array_merge(array('order' => $value.' ASC', 'select' => $key.', '.$value), $options));
		$arr = array('' => '-');
		foreach ($items as $i) {
			$arr[$i->$key] = $i->$value;
		}
	}

	/**
	 * Function for insert and update all data of the model
	 *
	 * @param bool $validate
	 * @return ActiveRecord_Model
	 */
	public function save($validate = true) {
		if ($this->_read_only) trigger_error('Readonly model, you can not save changes', E_USER_ERROR);
		$pk = $this->getPK();

		$this->beforeValidate();
		$v = new ActiveRecord_Validator($this);
		if ( ! $v->validate()) {
			$this->_errors = $v->getErrors();
			Error::reportFormErrors($this->_errors);
			return false;
		}
		$this->afterValidate();

		$this->beforeSave();
		$qb = new ActiveRecord_QueryBuilder($this);
		if ($this->$pk == null) {
			/** INSERT **/
			if (property_exists($this, 'created_at')) $this->created_at = date('Y-m-d H:i:s', time());
			$query = $qb->insert();
			$this->query($query['sql'], $query['params']);
			$this->$pk = $this->getLastId();
		} else {
			/** UPDATE **/
			if (property_exists($this, 'modified_at')) $this->modified_at = date('Y-m-d H:i:s', time());
			$query = $qb->update();
			$this->query($query['sql'], $query['params']);
		}
		$this->afterSave();

		return $this;
	}

	/**
	 * Function for get one item of the statement
	 *
	 * @param $statement
	 * @return mixed
	 */
	public function fetchOne($statement) {
		return ActiveRecord::get($this->_name)->create($statement->fetch());
	}

	/**
	 * Fucntion for set relations of model
	 *
	 * @return array
	 */
	public function relations(){
		return array();
	}

	/**
	 * Function for set validations of model
	 *
	 * @return array
	 */
	public function validations(){
		return array();
	}

	/**
	 * Callback for call before populate data
	 */
	public function beforeLoad(){}

	/**
	 * Callback for call after populate data
	 */
	public function afterLoad(){}

	/**
	 * Callback for call before validate data
	 */
	public function beforeValidate(){}

	/**
	 * Callback for call after validate data
	 */
	public function afterValidate(){}

	/**
	 * Callback for call before save data
	 */
	public function beforeSave(){}

	/**
	 * Callback for call after save data
	 */
	public function afterSave(){}

}