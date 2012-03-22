<?php
/**
 * Class for User Model
 *
 * @package App
 * @author danybmx <dany@dpstudios.es>
 */
class User extends Model
{
	/**
	 * Table
	 */
	protected $_table = "users";

	/**
	 * Fields
	 */
	public $id, $rol_id, $username, $password, $mail, $name, $lastname, $created_at, $modified_at;

	/**
	 * Relations
	 */
	public function relations() {
		return array(
			'Rol' => array(
				'type' => ActiveRecord::RELATION_BELONGSTO
			)
		);
		/*return array(
			'Relation' => array(
				'type' => [ActiveRecord::RELATION_BELONGSTO|ActiveRecord::RELATION_HASMANY|ActiveRecord::RELATION_HASMANYANDBELONGSTOMANY],
				'class' => 'Model',
				'local' => 'local_id',
				'foreign' => 'foreign_id',
			)
		);*/
	}

	/**
	 * Validations
	 *
	 * Available validators
	 * required [message]
	 * length [min:max:message:confirm:unique:allow_empty]
	 * numeric [min:max:message:confirm:unique:allow_empty]
	 * regexp [pattern:message:confirm:unique:allow_empty]
	 * mail [message:confirm:unique:allow_empty]
	 * url [message:confirm:unique:allow_empty]
	 * confirm [message:confirm:unique:allow_empty]
	 * unique [message:allow_empty]
	 */
	public function validations() {
		return array(
			/*'field' => array('validator', 'param' => 'value', 'params2' => 'value');*/
		);
	}
	
	public function beforeSave()
	{
		if ($this->password)
			$this->password = md5($this->password);
		else
			$this->password = null;
	}
}