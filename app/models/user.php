<?php
/**
 *
 *
 * @package App
 * @author danybmx <dany@dpstudios.es>
 */
class User extends Model
{
	protected $_table = 'users';

	public $id, $rol_id, $rate_id, $username, $password, $decoded_password, $name, $comment, $auto_invoice_comment, $address, $country, $code, $phone, $mail, $created_at, $modified_at;

	public function relations() {
		return array(
			'Rol' => array(
				'type' => ActiveRecord::RELATION_BELONGSTO
			)
		);
	}

	public function validations() {
		return array(
			'name' => array('lenght', array('max' => 20, 'min' => 10))
		);
	}
}
