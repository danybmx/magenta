<?php
/**
 *
 *
 * @package
 * @author danybmx <dany@dpstudios.es>
 */
class ActiveRecord_Validator
{
	protected $_model;
	protected $_validations;
    protected $_operation;
	protected $_data = array();
	protected $_errors = array();
	protected $_validators = array('required', 'length', 'numeric', 'regexp', 'mail', 'url', 'confirm', 'unique');
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

	public function __construct(Model $model) {
		$this->_model = $model;
		$this->_validations = $model->validations();
<<<<<<< HEAD
		$this->_data = $model->toArray();
        $pk = $model->getPK();
        if ($model->$pk)
            $this->_operation = ActiveRecord::UPDATE;
        else
            $this->_operation = ActiveRecord::INSERT;
=======
		$this->_data = $model->getData();
>>>>>>> Validation working
	}

	/**
	 * Validations
	 *
	 * 'field' => array('type', array('param1' => 'value1', 'paramN' => 'valueN'));
	 */
	public function validate() {
		foreach ($this->_data as $k => $d) {
			if (array_key_exists($k, $this->_validations)) {
				$v = $this->_validations[$k];
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

	public function addError($field, $string, $params = array()) {
		$params = array_merge($params, array('field' => $field));
		$this->_errors[$field] = magentaSprintf($string, $params);
	}

	public function getErrors() {
		return $this->_errors;
	}

	# Validators
	public function required($field, $params) {
		$message = array_key_exists('message', $params) ? $params['message'] : $this->_default_messages['required'];
		if ( ! $this->_data[$field]) {
			$this->addError($field, $message);
			return false;
		}
	}

	public function length($field, $params) {
		$data = $this->_data[$field];

		if (array_key_exists('allow_empty', $params) && $params['allow_empty'] == true)
			if( ! $data) return true;

		if (array_key_exists('confirm', $params) && $params['confirm'] == true) {
			if ( ! $this->confirm($field, array('field' => array_key_exists('confirm_field', $params) ? $params['confirm_field'] : $field.'_confirm')))
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

	public function numeric($field, $params) {
		$data = $this->_data[$field];

		if (array_key_exists('allow_empty', $params) && $params['allow_empty'] == true)
			if( ! $data) return true;

		if (array_key_exists('confirm', $params) && $params['confirm'] == true) {
			if ( ! $this->confirm($field, array('field' => array_key_exists('confirm_field', $params) ? $params['confirm_field'] : $field.'_confirm')))
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

	public function regexp($field, $params) {
		$data = $this->_data[$field];

		if (array_key_exists('confirm', $params) && $params['confirm'] == true) {
			if ( ! $this->confirm($field, array('field' => array_key_exists('confirm_field', $params) ? $params['confirm_field'] : $field.'_confirm')))
				return false;
		}

		if (array_key_exists('allow_empty', $params) && $params['allow_empty'] == true)
			if( ! $data) return true;

		if ( ! @preg_match($params['pattern'], $data)) {
			$message = array_key_exists('message', $params) ? $params['message'] : $this->_default_messages['regexp'];
			$this->addError($field, $message, $params);
			return false;
		}
	}

	public function mail($field, $params) {
		$params = array_merge(array(
			'pattern' => '/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/iD',
			'message' => $this->_default_messages['mail']
		), $params);
		return $this->regexp($field, $params);
	}

	public function url($field, $params) {
		$params = array_merge(array(
			'pattern' => '/(ftp|http|https):\/\/([a-z0-9.-]*)\.[a-z]{2,4}([a-zA-Z0-9\/-_#?&:!@]*)?/',
			'message' => $this->_default_messages['url']
		), $params);
		return $this->regexp($field, $params);
	}

	public function confirm($field, $params) {
		$data = $this->_data[$field];

		if (array_key_exists('allow_empty', $params) && $params['allow_empty'] == true)
			if( ! $data) return true;

		$confirm_field = array_key_exists('field', $params) ? $params['field'] : $field.'_confirm';

		if ($data != $this->_data[$confirm_field]) {
			$message = array_key_exists('message', $params) ? $params['message'] : $this->_default_messages['confirm'];
			$this->addError($field, $message, $params);
			$this->addError($confirm_field, $message, $params);
			return false;
		}
		return true;
	}

}