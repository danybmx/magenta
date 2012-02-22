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

	public function __construct(Model $model) {
		$this->_model = $model;
		$this->_validations = $model->validations();
		$this->_data = $model->toArray();
        $pk = $model->getPK();
        if ($model->$pk)
            $this->_operation = ActiveRecord::UPDATE;
        else
            $this->_operation = ActiveRecord::INSERT;
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
				switch ($v[0]) {
					case 'custom':

						break;
					default:

						break;
				}
			}
		}
	}
}