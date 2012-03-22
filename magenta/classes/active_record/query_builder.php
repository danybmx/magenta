<?php
/**
 * Class for create the sql queries
 *
 * Basic CRUD options
 * - insert
 * - update
 * - delete
 * - select
 *
 * @package ActiveRecord
 * @author danybmx <dany@dpstudios.es>
 */
class ActiveRecord_QueryBuilder
{
	/**
	 * Model for make the queries
	 * @var Model
	 */
	protected $_model;

	/**
	 * Default options for select
	 * @var array
	 */
	protected $_default_options = array(
		'condition' => null,
		'params' => array(),
		'order' => null,
		'limit' => null,
		'page' => null,
		'offset' => null,
		'select' => null,
		'fetch' => ActiveRecord::FETCH_ALL
 	);

	/**
	 * Function for construct the QueryBuilder
	 *
	 * @param Model $model
	 */
	public function __construct(Model $model) {
		$this->_model = $model;
	}

	/**
	 * Function for create sql code for insert data
	 *
	 * @return array
	 */
	public function insert() {
		$data = $this->_model->toArray();
		$fields = '';
		$values = '';
		$params = array();
		foreach ($data as $k => $v) {
			if ($v !== null) {
				$fields .= "`{$k}`, ";
				$values .= "?, ";
				$params[] = $v;
			}
		}
		$fields = substr($fields, 0, -2);
		$values = substr($values, 0, -2);
		$sql = "INSERT INTO `{$this->_model->getTable()}` ({$fields}) VALUES ({$values});";

		return array('sql' => $sql, 'params' => $params);
	}

	/**
	 * Class for make an update sql
	 *
	 * @return array
	 */
	public function update() {
		$data = $this->_model->toArray();
		$pk = $this->_model->getPK();
		$query = '';
		$params = array();
		foreach ($data as $k => $v) {
			if ($v !== null && $k != $pk) {
				$query .= "`{$k}` = ?, ";
				$params[] = $v;
			}
		}
		$query = substr($query, 0, -2);
		$params[] = $data[$pk];

		$sql = "UPDATE `{$this->_model->getTable()}` SET {$query} WHERE `{$pk}` = ?;";
		return array('sql' => $sql, 'params' => $params);
	}

	public function delete($field, $value, $cascade = false) { #TODO Cascade
		$sql = "DELETE FROM `{$this->_model->getTable()}` WHERE `{$field}` = ?;";
		$params = array($value);

		return array('sql' => $sql, 'params' => $params);
	}

	/**
	 * Class for make a select query
	 *
	 * @param array $options
	 * @return array
	 */
	public function select($options = array()) {
		$sql = '';

		$options = array_merge($this->_default_options, $options);

		if ( ! $options['select'])
			$options['select'] = '*';

		$sql .= "SELECT {$options['select']} FROM {$this->_model->getTable()}";

		if ($options['condition']) {
			$sql .= ' WHERE '.$options['condition'];
			if ( ! is_array($options['params']))
				$options['params'] = array($options['params']);
		}

		if ($options['order'])
			$sql .= " ORDER BY {$options['order']}";

		if ($options['page']) {
			if ( ! $options['limit']) $options['limit'] = Config::load('app.page_items');
			$options['offset'] = ($options['page'] - 1) * $options['limit'];
		}

		if ($options['limit']) {
			$sql .= " LIMIT {$options['limit']}";
			if ($options['limit'] == 1)
				$options['fetch'] = ActiveRecord::FETCH_ONE;
		}

		if ($options['offset'])
			$sql .= " OFFSET {$options['offset']}";

		return array('sql' => $sql, 'params' => $options['params'], 'fetch' => $options['fetch']);
	}
}
