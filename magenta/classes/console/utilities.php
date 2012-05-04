<?php
/**
 * Utilities class for magenta framework
 *
 * Some utilities for make the live more easy
 * create database
 * set permissions
 * clean cache
 *
 * @author danybmx <dany@dpstudios.es>
 * @package Console
 */
class Console_Utilities
{
	public static $_con, $_base;

	public static function init($args, $base = null) {
		global $con;
		self::$_base = $base ? $base : ROOT;
		self::$_con = $con;
		if (count($args) < 1) {
			self::help();
			exit();
		}
		self::execute($args[0]);
	}

	public static function help() {
		self::$_con->write('Utilities available:');
		self::$_con->write('Database creator: script/mangeta u create_database');
		self::$_con->write('Permissions setter: script/mangeta u set_permissions');
		self::$_con->write('Cache cleaner: script/mangeta u clean_cache');
		self::$_con->write('Created default rols and owner: script/magenta/ u create_rols_and_owner');
	}
	
	public static function execute($util)
	{
		switch ($util) {
			case 'create_database':
					$connection = Config::load('app.database.'.Config::load('app.database.connection'));
					$connection_url = parse_url($connection);
					$database = substr($connection_url['path'], 1);
					ActiveRecord::query('CREATE DATABASE '.$database, array(), str_replace('/'.$database, '', $connection), true);
					self::$_con->write('Database '.$database.' created');
				break;
				
			case 'create_rols_and_owner':
					// Rols
					$own = ActiveRecord::get('Rol')->create(array(
						'key' => 'owner',
						'name' => 'Owner'
					))->save();
					self::$_con->write('Created owner rol');
					
					ActiveRecord::get('Rol')->create(array(
						'key' => 'admin',
						'name' => 'Admin'
					))->save();
					self::$_con->write('Created admin rol');
					
					ActiveRecord::get('Rol')->create(array(
						'key' => 'user',
						'name' => 'User'
					))->save();
					self::$_con->write('Created user rol');
					
					ActiveRecord::get('User')->create(array(
						'rol_id' => $own->id,
						'username' => 'owner',
						'password' => 'owner',
						'mail' => 'owner@example.com',
						'name' => 'Owner',
						'lastname' => 'Magenta'
					))->save();
					self::$_con->write('Created owner user (owner:owner)');
				break;
			
			case 'set_permissions':
					self::$_con->execute('chmod -R a+rw '.self::$_base.DS.'tmp');
					self::$_con->execute('chmod -R a+rw '.self::$_base.DS.'public'.DS.'sass');
					self::$_con->execute('chmod -R a+rw '.self::$_base.DS.'public'.DS.'img'.DS.'uploads');
					self::$_con->write('Permissions setted to a+rw on ('.self::$_base.')');
				break;
					
			case 'clean_cache':
					self::$_con->execute('rm -r '.self::$_base.DS.'tmp'.DS.'*');
					self::$_con->write('Cache cleaned');
				break;
			
			default:
				self::help();
				break;
		}
	}
}