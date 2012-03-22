<?php
/**
 * Migration class for magenta framework
 *
 * Save data to mysql database
 *
 * @author danybmx <dany@dpstudios.es>
 * @package Console
 */
class Console_Migrate
{
	public static $_con, $_files;

	public static function init($args) {
		global $con;
		self::$_con = $con;
		self::$_files = $args;
		self::execute();
	}

	public static function help() {
		self::$_con->write('For migrate run:');
		self::$_con->write('script/mangeta m modelName');
	}
	
	public static function execute()
	{
		foreach (self::$_files as $f) {
			$s = Inflector::tableize($f);
			if (file_exists(ROOT.DS.'database'.DS.$s.'.sql')) {
				$file_content = file_get_contents(ROOT.DS.'database'.DS.$s.'.sql');
				ActiveRecord::query($file_content);
				self::$_con->write('Database '.$s.' for model '.$f.' created');
			} else 
				self::$_con->write('The model '.$f.' does not exists');
		}
	}
}