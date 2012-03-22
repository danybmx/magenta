<?php
/**
 * Class for Rol Model
 *
 * @package App
 * @author danybmx <dany@dpstudios.es>
 */
class Rol extends Model
{
	/**
	 * Table
	 */
	protected $_table = "roles";

	/**
	 * Fields
	 */
	public $id, $name, $key;

	/**
	 * Relations
	 */
	public function relations() {
		return array(
			'Users' => array(
				'type' => ActiveRecord::RELATION_HASMANY
			)
			/*'Relation' => array(
				'type' => [ActiveRecord::RELATION_BELONGSTO|ActiveRecord::RELATION_HASMANY|ActiveRecord::RELATION_HASMANYANDBELONGSTOMANY],
				'class' => 'Model',
				'local' => 'local_id',
				'foreign' => 'foreign_id',
			)*/
		);
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
}