<?php
/**
 * Creation class for magenta framework
 *
 * Create an empty App
 *
 * @author danybmx <dany@dpstudios.es>
 * @package Console
 */
class Console_Create
{
	public static $_con, $_name;

	public static function init($args) {
		global $con;
		self::$_con = $con;
		self::$_name = $args[0];
		self::execute();
	}

	public static function help() {
		self::$_con->write('For create run:');
		self::$_con->write('script/mangeta create appName');
	}
	
	public static function execute()
	{
		if (file_exists(WORKING_DIR.DS.strtolower(self::$_name)) && is_dir(WORKING_DIR.DS.strtolower(self::$_name))) {
			self::$_con->write('The folder '.WORKING_DIR.DS.strtolower(self::$_name).' already exists. Remove it before create a new app');
		} else {
			$dir = WORKING_DIR.DS.strtolower(self::$_name);
			$folders = array('app', 'config', 'database', 'magenta', 'public', 'script', array('tmp', 0777), 'vendors');
			$files = array('.htaccess');
			
			self::$_con->execute('mkdir '.$dir);
			
			foreach ($folders as $f) {
				$n = is_array($f) ? $f[0] : $f;
				self::$_con->write('Creating folder '.$n);
				self::$_con->execute('cp -rv '.ROOT.DS.$n.' '.$dir.DS.$n);
				
				if (is_array($f) && array_key_exists('1', $f)) {
					chmod($dir.DS.$n, $f[1]);
				}
			}
			
			foreach ($files as $f) {
				$n = is_array($f) ? $f[0] : $f;
				self::$_con->write('Creating file '.$n);
				self::$_con->execute('cp -v '.ROOT.DS.$n.' '.$dir.DS.$n);
				
				if (is_array($f) && array_key_exists('1', $f)) {
					chmod($dir.DS.$n, $f[1]);
				}
			}
		}
		
		Console_Utilities::init(array('clean_cache'), $dir);
		Console_Utilities::init(array('set_permissions'), $dir);
	}
}