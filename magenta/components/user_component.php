<?php

class UserComponent
{
	/**
	 * Función para crear una sesión
	 *
	 * @param string $username 
	 * @param string $password 
	 * @return void
	 * @author Daniel Rodríguez Gil
	 */
	public static function login($username, $password, $options = array(), $encode = true) {
		if ($encode)
			$password = md5($password);
			
		$criteria = array();
		$criteria['condition'] = 'username = ? AND password = ?';
		$criteria['params'] = array($username, $password);
		if (array_key_exists('condition', $options))
			$criteria['condition'] .= ' AND '.$options['condition'];
		
		if (array_key_exists('params', $options))
			$criteria['params'] = array_merge($criteria['params'], $options['params']);
		
		$user = ActiveRecord::get('User')->first($criteria);

		if ($user) {
			$_SESSION['user'] = $user->id;
			$_SESSION['rol'] = $user->Rol->key;
			$_SESSION['username'] = $user->username;
			$_SESSION['password'] = $user->password;
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Función para cerrar una sesión
	 *
	 * @return void
	 * @author Daniel Rodríguez Gil
	 */
	public static function logout() {
		$_SESSION['user'] = null;
		$_SESSION['rol'] = null;
		$_SESSION['username'] = null;
		$_SESSION['password'] = null;
		return true;
	}
	
	/**
	 * Función para comprobar si el usuario está logueado o no
	 *
	 * @return void
	 * @author Daniel Rodríguez Gil
	 */
	public static function check() {
		if (isset($_SESSION['user'])) {
			$user = $_SESSION['user'];
			$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
			$password = (isset($_SESSION['password'])) ? $_SESSION['password'] : '';
			
			$criteria = array();
			$criteria['condition'] = '`id` = ? AND `username` = ? AND `password` = ?';
	    $criteria['params'] = array($user, $username, $password);
			if (ActiveRecord::get('User')->first($criteria)) {
				return true;
			} else {
				self::Logout();
			}
		}
		return false;
	}
	
	/**
	 * Función para comprobar si el usuario tiene acceso a un sitio que requiere cierto rol
	 *
	 * @param string $requiredRol 
	 * @return void
	 * @author Daniel Rodríguez Gil
	 */
	public static function checkRol($required_roles) {
		if ( ! is_array($required_roles)) $required_roles = array($required_roles);
		
		if (in_array('guest', $required_roles) && ! self::check()) return true;
		
		if ( ! array_key_exists('rol', $_SESSION))
			return false;

		if (in_array($_SESSION['rol'], $required_roles) || $_SESSION['rol'] == 'owner') {
			return true;
		} else {
			return false;
		}
	}
	
	public static function get() {
		if (array_key_exists('user', $_SESSION))
			return User::findByPK($_SESSION['user']);
			
		return false;
	}
}
