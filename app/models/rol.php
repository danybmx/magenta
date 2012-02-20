<?php
/**
 *
 *
 * @package App
 * @author danybmx <dany@dpstudios.es>
 */
class Rol extends Model
{
	protected $_table = 'roles';
	public $id, $key, $name;

	function relations() {
		return array(
			'Users' => array(
				'type' => ActiveRecord::RELATIONS_HASMANY
			)
		);
	}
}
