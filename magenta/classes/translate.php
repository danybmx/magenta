<?php
/**
 * Class for manage i18n
 *
 * @author danybmx <dany@dpstudios.es>
 * @package Magenta
 */
class Translate
{
	public static $translations = array();

	public static function getTranslations($locale = null)
	{
		if ( ! $locale)
			$locale = LOCALE;

		if ( ! array_key_exists($locale, self::$translations))
			self::loadTranslations($locale);

		return self::$translations[$locale];
	}

	public static function JSi18n($locale = null)
	{
		if ( ! $locale)
			$locale = LOCALE;
		
		if ( ! array_key_exists($locale, self::$translations))
			self::loadTranslations($locale);
		
		return '<script type="text/javascript">var i18n = '.json_encode(self::$translations[$locale]).';</script>';
	}

	public static function loadTranslations($locale)
	{
		$file = LOCALES.DS.$locale.'.json';
		if ( ! file_exists($file))
			trigger_error('The locale for '.$locale.' does not exists');

		$content = file_get_contents($file);
		self::$translations[$locale] = json_decode($content, true);
	}

	public static function forKey($key, $locale = null)
	{
		$translations = self::getTranslations($locale);
		if (array_key_exists($key, $translations))
			if ($translations[$key] != '')
				$key = $translations[$key];

		return $key;
	}
}
