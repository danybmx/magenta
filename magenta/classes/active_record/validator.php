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
	protected $_data = array();
	protected $_errors = array();

	public function __construct(Model $model) {
		$this->_model = $model;
		$this->_validations = $model->validations();
		$this->_data = $model->toArray();
	}

	public function validate() {
		foreach ($this->_data as $k => $d) {
			if (array_key_exists($k, $this->_validations)) {

			}
		}
	}
}
