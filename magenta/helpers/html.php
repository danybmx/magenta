<?php
/**
 * Html Helper
 *
 * - create links
 * - insert images
 * - load css, js, sass
 *
 * @author danybmx <dany@dpstudios.es>
 * @package Helpers
 */
class Html {

	/**
	 * Function for create a html link tag and compilate the sass to css if it not was compiled before
	 *
	 * @static
	 * @return string with HTML tag
	 */
	public static function sass() {
		$parser = null;

		$args = func_get_args();
		$html = '';
		foreach ($args as $file) {
			if (preg_match('/^(.*)\.sass$/', $file)) {
				$file = str_replace('.sass', '', $file);
			}
			$css_file = ASSETS.DS.'css'.DS.$file.'.css';
			$sass_file = ASSETS.DS.'css'.DS.'sass'.DS.$file.'.sass';

			$css_time = 0;
			$sass_time = filemtime($sass_file);
			if (file_exists($css_file)) $css_time = filemtime($css_file);

			if ($sass_time > $css_time) {
				if ( ! $parser) {
					require_once VENDORS.DS.'Phamlp'.DS.'sass'.DS.'SassParser.php';
					$parser = new SassParser(array(
					'cache' => Config::load('app.sass.cache'),
					'cache_location' => CACHE.DS.'sass',
					'css_location' => ASSETS.DS.'css',
					'load_paths' => array('./', MASSETS.DS.'css'.DS.'sass'),
					'style' => Config::load('app.sass.style')
					));
				}

				try {
					$css = $parser->toCss($file);
					file_put_contents($css_file, $css);
				} catch (Exception $e) {
					trigger_error($e->getMessage(), E_USER_ERROR);
				}
			}

			$html .= self::css($file);
		}

		return $html;
	}

	/**
	 * Function for create a html stylesheet link tag
	 *
	 * @static
	 * @return string with HTML tag
	 */
	public static function css() {
		$args = func_get_args();
		$html = '';
		foreach ($args as $file) {
			$file = explode(':', $file);
			$media = isset($file['1']) ? $file['1'] : 'screen';
			$file = $file['0'];

			if ( ! preg_match('/^(.*)\.css$/', $file))
				$file .= '.css';

			if ( ! preg_match('/^(http|https):\/\/(.*)$/', $file))
				$file = BASE_PATH.'/assets/css/'.$file;

			$html .= '<link rel="stylesheet" type="text/css" href="'.$file.'" media="'.$media.'">';
		}

		return $html;
	}

	/**
	 * Function for create a html text/javascript script tag
	 *
	 * @static
	 * @return string with HTML tag
	 */
	public static function js() {
		$args = func_get_args();
		$html = '';
		foreach ($args as $file) {
			if ( ! preg_match('/^(.*)\.js$/', $file))
				$file .= '.js';

			if ( ! preg_match('/^(http|https):\/\/(.*)$/', $file))
				$file = BASE_PATH.'/assets/js/'.$file;

			$html .= '<script type="text/javascript" src="'.$file.'"></script>';
		}

		return $html;
	}

	/**
	 * Function for create a HTML string to include a favicon in website
	 *
	 * @static
	 * @return string with HTML favicon tags
	 */
	public static function favicon($file) {
		$html = '';

		if ( ! preg_match('/^(http|https):/\/\//', $file))
			$file = BASE_PATH.'/assets/'.$file;

		$html .= '<link rel="icon" href="'.$file.'" type="image/x-icon">';
		$html .= '<link rel="shortcut icon" href="'.$file.'" type="image/x-icon">';

		return $html;
	}

	/**
	 * Function for create a HTML image tag
	 *
	 * @static
	 * @param $src
	 * @param array $options
	 * @return string with HTML image tag
	 */
	public static function image($src, $options = array()) {
		$html = '';
		if ( ! preg_match('/^(http|https):\/\//', $src))
			$src = BASE_PATH.'/assets/img/'.$src;

		$opt = self::_makeOptionsString($options);
		$html .= '<img src="'.$src.'"'.$opt.' />';

		return $html;
	}
	
	/**
	 * Función para crear un enlace
	 *
	 * @param string $text Texto para mostrar (o HTML)
	 * @param string $url URL de destino
	 * @param array $options Opciones para el elemento
	 * @return void
	 * @author Daniel Rodríguez Gil
	 */
	public static function link($text, $url, $options = array())
	{
		if (array_key_exists('rel', $options))
			$options['url'] = $url;
			
		$html = '';
		$url = make_url($url);
		$opt = self::_makeOptionsString($options);
		$html .= '<a href="'.$url.'"'.$opt.'>'.__($text).'</a>';
		return $html;
	}

	/**
	 * Function for parse options in a string from an array
	 *
	 * @static
	 * @param array $options
	 * @return string with options parset in a string
	 */
	private static function _makeOptionsString($options = array()) {
		$string = '';
		if (is_array($options)) {
			foreach ($options as $option => $value) {
				if ($value !== null)
					$string .= ' '.$option.'="'.$value.'"';
			}
		} 

		return $string;
	}
}
