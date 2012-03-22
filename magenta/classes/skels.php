<?php
/**
 * Class for generate documents from skels
 *
 * @author danybmx <dany@dpstudios.es>
 * @package Console
 */
class Skels
{
	public static function parse($file, $params) {
		$file = SKELS.DS.$file.'.skel';
		$content = file_get_contents($file);

		$find = array();
		$replace = array();
		foreach ($params as $p => $v) {
			$find[] = '{:'.$p.'}';
			$replace[] = $v;
		}

		$parsed = str_replace($find, $replace, $content);
		return $parsed;
	}
}
