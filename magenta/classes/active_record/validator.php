<?php
/**
 * Class for validate data from models easily
 *
 * @package ActiveRecord
 * @author danybmx <dany@dpstudios.es>
 */
class ActiveRecord_Validator
{
	/**
	 * Model of validation
	 * @var Model
	 */
	protected $_model;

	/**
	 * Validation from the model
	 * @var array
	 */
	protected $_validations;

	/**
	 * Insert or Update
	 * @var int
	 */
	protected $_operation;

	/**
	 * All data from model
	 * @var array
	 */
	protected $_data = array();

	/**
	 * Errors on validation
	 * @var array
	 */
	protected $_errors = array();

	/**
	 * Available validators
	 * @var array
	 */
	protected $_validators = array('required', 'length', 'numeric', 'regexp', 'mail', 'url', 'confirm', 'unique');

	/**
	 * Default messages
	 * @var array
	 */
	protected $_default_messages = array(
		'required' => 'Required',
		'length' => array(
			'bigger' => 'Must not exceed :max characters',
			'smaller' => 'Must be at least :min characters',
			'between' => 'Must be between :min and :max characters'
		),
		'numeric' => array(
			'nonumeric' => 'Must be numeric',
			'bigger' => 'Must not exceed :max',
			'smaller' => 'Must be at least :min',
			'between' => 'Must be between :min and :max'
		),
		'regexp' => 'Does not match the required format',
		'mail' => 'Must be a mail',
		'url' => 'Must be a url',
		'confirm' => 'Does not match',
		'unique' => 'The :field is in use'
	);

	/**
	 * Function for construct the Validator
	 *
	 * @param Model $model
	 */
	public function __construct(Model $model) {
		$this->_model = $model;
		$this->_validations = $model->validations();
		$this->_data = $model->getData();
		$pk = $model->getPK();

		if ($model->$pk)
				$this->_operation = ActiveRecord::UPDATE;
		else
				$this->_operation = ActiveRecord::INSERT;
	}

	/**
	 * Function for validate
	 *
	 * Validation must be in format:
	 * 'field' => array('type', 'param1' => 'value1', 'paramN' => 'valueN', ['on' => 'insert|update']);
	 *
	 * The 'on' param is optional if not exists it works in all operations if not
	 * it only work on insert or update (ActiveRecord::INSERT, ActiveRecord::UPDATE
	 *
	 * @return bool true if not errors false if errors
	 */
	public function validate() {
		foreach ($this->_data as $k => $d) {
			if (array_key_exists($k, $this->_validations)) {
				$v = $this->_validations[$k];
				if (array_key_exists('on', $v)) {
					if ($v['on'] != $this->_operation)
						continue;
				}
				$validator = $v[0];
				unset($v[0]);
				switch ($validator) {
					case 'custom':
						$this->_model->$validator($k, $v);
						break;
					default:
						if (in_array($validator, $this->_validators)) {
							$this->$validator($k, $v);
						} else {
							trigger_error('The validator '.$validator.' does not exists', E_USER_ERROR);
						}
						break;
				}
			}
		}

		if ($this->_errors)
			return false;
		else
			return true;
	}

	/**
	 * Function for add an error to errors array
	 *
	 * @param $field field of error
	 * @param $string error message
	 * @param array $params params for replace in error
	 */
	public function addError($field, $string, $params = array()) {
		$params = array_merge($params, array('field' => $field));
		$this->_errors[$field] = __($string, $params);
	}

	/**
	 * Function for get errors array
	 * @return array
	 */
	public function getErrors() {
		return $this->_errors;
	}

	# Validators

	/**
	 * Function for required fields
	 *
	 * @param $field
	 * @param $params (message)
	 * @return bool
	 */
	public function required($field, $params) {
		$message = array_key_exists('message', $params) ? $params['message'] : $this->_default_messages['required'];
		if ( ! $this->_data[$field]) {
			$this->addError($field, $message);
			return false;
		}
	}

	/**
	 * Function for required length
	 *
	 * @param $field
	 * @param $params (min, max, allow_empty, confirm_field, confirm, message)
	 * @return bool
	 */
	public function length($field, $params) {
		$data = $this->_data[$field];

		if (array_key_exists('allow_empty', $params) && $params['allow_empty'] == true)
			if( ! $data) return true;

		if (array_key_exists('confirm', $params) && $params['confirm'] == true) {
			if ( ! $this->confirm($field, array('field' => array_key_exists('confirm_field', $params) ? $params['confirm_field'] : $field.'_confirm')))
				return false;
		}

		if (array_key_exists('unique', $params) && $params['unique'] == true) {
			if ( ! $this->unique($field, array()));
				return false;
		}

		if (array_key_exists('max', $params) && array_key_exists('min', $params))
			$type = 'between';
		else
			$type = array_key_exists('min', $params) ? 'min' : 'max';

		switch ($type) {
			case 'min':
				if (strlen($data) < $params['min']) {
					$message = array_key_exists('message', $params) ? $params['message'] : $this->_default_messages['length']['smaller'];
					$this->addError($field, $message, $params);
					return false;
				}
				break;
			case 'max':
				if (strlen($data) > $params['max']) {
					$message = array_key_exists('message', $params) ? $params['message'] : $this->_default_messages['length']['bigger'];
					$this->addError($field, $message, $params);
					return false;
				}
				break;
			case 'between':
				if (strlen($data) > $params['max'] || strlen($data) < $params['min']) {
					$message = array_key_exists('message', $params) ? $params['message'] : $this->_default_messages['length']['between'];
					$this->addError($field, $message, $params);
					return false;
				}
				break;
		}
	}

	/**
	 * Function for numeric values
	 *
	 * @param $field
	 * @param $params (min, max, allow_empty, confirm_field, confirm, message)
	 * @return bool
	 */
	public function numeric($field, $params) {
		$data = $this->_data[$field];

		if (array_key_exists('allow_empty', $params) && $params['allow_empty'] == true)
			if( ! $data) return true;

		if (array_key_exists('confirm', $params) && $params['confirm'] == true) {
			if ( ! $this->confirm($field, array('field' => array_key_exists('confirm_field', $params) ? $params['confirm_field'] : $field.'_confirm')))
				return false;
		}

		if (array_key_exists('unique', $params) && $params['unique'] == true) {
			if ( ! $this->unique($field, array()));
				return false;
		}

		if ( ! is_numeric($data)) {
			$message = array_key_exists('message', $params) ? $params['message'] : $this->_default_messages['numeric']['nonumeric'];
			$this->addError($field, $message);
		} else {
			if (array_key_exists('max', $params) && array_key_exists('min', $params))
				$type = 'between';
			else
				$type = array_key_exists('min', $params) ? 'min' : 'max';

			switch ($type) {
				case 'min':
					if ($data < $params['min']) {
						$message = array_key_exists('message', $params) ? $params['message'] : $this->_default_messages['numeric']['smaller'];
						$this->addError($field, $message, $params);
						return false;
					}
					break;
				case 'max':
					if ($data > $params['max']) {
						$message = array_key_exists('message', $params) ? $params['message'] : $this->_default_messages['numeric']['bigger'];
						$this->addError($field, $message, $params);
						return false;
					}
					break;
				case 'between':
					if ($data > $params['max'] || $data < $params['min']) {
						$message = array_key_exists('message', $params) ? $params['message'] : $this->_default_messages['numeric']['between'];
						$this->addError($field, $message, $params);
						return false;
					}
					break;
			}
		}
	}

	/**
	 * Function for check string with regular expression
	 *
	 * @param $field
	 * @param $params (pattern, allow_empty, confirm_field, confirm, message)
	 * @return bool
	 */
	public function regexp($field, $params) {
		$data = $this->_data[$field];

		if (array_key_exists('allow_empty', $params) && $params['allow_empty'] == true)
			if( ! $data) return true;

		if (array_key_exists('confirm', $params) && $params['confirm'] == true) {
			if ( ! $this->confirm($field, array('field' => array_key_exists('confirm_field', $params) ? $params['confirm_field'] : $field.'_confirm')))
				return false;
		}

		if (array_key_exists('unique', $params) && $params['unique'] == true) {
			if ( ! $this->unique($field, array()));
				return false;
		}

		if ( ! @preg_match($params['pattern'], $data)) {
			$message = array_key_exists('message', $params) ? $params['message'] : $this->_default_messages['regexp'];
			$this->addError($field, $message, $params);
			return false;
		}
	}

	/**
	 * Function for check mail format
	 *
	 * @param $field
	 * @param $params (allow_empty, confirm, confirm_field, message)
	 * @return bool
	 */
	public function mail($field, $params) {
		$params = array_merge(array(
			'pattern' => '/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/iD',
			'message' => $this->_default_messages['mail']
		), $params);
		return $this->regexp($field, $params);
	}

	/**
	 * Function for check url format
	 *
	 * @param $field
	 * @param $params (allow_empty, confirm, confirm_field, message)
	 * @return bool
	 */
	public function url($field, $params) {
		$params = array_merge(array(
			'pattern' => '/(ftp|http|https):\/\/([a-z0-9.-]*)\.[a-z]{2,4}([a-zA-Z0-9\/-_#?&:!@]*)?/',
			'message' => $this->_default_messages['url']
		), $params);
		return $this->regexp($field, $params);
	}

	/**
	 * Function for check mail format
	 *
	 * @param $field
	 * @param $params (allow_empty, confirm, confirm_field, message)
	 * @return bool
	 */
	public function confirm($field, $params) {
		$data = $this->_data[$field];

		if (array_key_exists('allow_empty', $params) && $params['allow_empty'] == true)
			if( ! $data) return true;

		if (array_key_exists('unique', $params) && $params['unique'] == true) {
			if ( ! $this->unique($field, array()));
				return false;
		}

		$confirm_field = array_key_exists('field', $params) ? $params['field'] : $field.'_confirm';

		if ($data != $this->_data[$confirm_field]) {
			$message = array_key_exists('message', $params) ? $params['message'] : $this->_default_messages['confirm'];
			$this->addError($field, $message);
			$this->addError($confirm_field, $message);
			return false;
		}
		return true;
	}

	/**
	 * Function for check if the field value is unique
	 *
	 * @param $field
	 * @param $params
	 * @return bool
	 */
	public function unique($field, $params) {
		$data = $this->_data[$field];
		$pk = $this->_model->getPK();
		$pk_value = $this->_data[$pk];

		$m = ActiveRecord::get($this->_model->getName())->find(array('condition' => "{$field} = ?", 'params' => $data));
		if ($m && $m->$pk != $pk_value) {
			$message = array_key_exists('message', $params) ? $params['message'] : $this->_default_messages['unique'];
			$this->addError($field, $message);
			return false;
		}
		return true;
	}
}