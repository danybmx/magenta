<?php
/**
 * Magenta, PHP Lightweight and easy to use MVC Framework
 *
 * @version 0.1
 * @package Magenta
 * @author dpStudios Development Team
 * @copyright dpStudios 2009-2011
 * @link http://magenta.dpstudios.es
 */

/**
 * Error Class
 *
 * Use this for handle errors and exceptions of Magenta Framework
 */
class Error {
	static public $_parsed = false;
	static public $_errors = array();
	static public $_form_errors = array();

	public static function getParsed() {
		return self::$_parsed;
	}

	public static function parse($title, $file, $line, $context)
	{
		$file_contents = highlight_file($file, true);

		$file_lines = explode("<br />", $file_contents);
		
		$init_line = $line - 15 < 0 ? 0 : $line - 15;
		$file_data = '<div class="magenta_debug_code"><div class="header line">Lines</div><div class="header code">Code</div><div class="content">';
		$used_lines = array_slice($file_lines, $init_line, 31, true);

		$cls = 'even';
		foreach ($used_lines as $k => $l) {
			$cls = $cls == 'even' ? 'odd' : 'even';
			if ($k+1 == $line) $cls = 'target';
			$file_data .= '<div class="line_container '.$cls.'"><div class="line">'.($k+1).'</div><div class="rawcode">'.$l.'</div><div class="clear"></div></div>';
		}
		$file_data .= '</div></div>';
		
		$template = new Template('error');
		$template->setParams(array(
			'title' => $title,
			'file' => $file,
			'file_data' => $file_data,
			'line' => $line,
			'context' => $context,
			'backtrace' => debug_backtrace()
			));
		$html = $template->render(false);
		
		// Limpiamos el buffer de salida para que no aparece el mensaje "feo" de PHP
		ob_clean();
		// Mostramos el error
		echo $html;
		// Seteamos la varíable estática $_parsed para poder ver que ha sucedido un error
		self::$_parsed = true;
		// Salimos para que se detenga la ejecución
		exit();
	}

	public static function errorHandler($no, $string, $file, $line, $context)
	{
		self::parse($string, $file, $line, $context);
	}

	public static function exceptionHandler($exception)
	{
	}
	
	public static function getErrors()
	{
		self::update();
		if (Data::get('error')) {
			$error = Data::get('error');
			return '<div class="magenta-error '.$error['type'].'">'.$error['message'].'</div>';
		} else {
			return '';
		}
	}
	
	public static function update()
	{
		if (array_key_exists('error', $_SESSION) && $_SESSION['error']) {
			if ($_SESSION['error']['wait'] == 0) {
				Data::set('error', array('message' => $_SESSION['error']['message'], 'type' => $_SESSION['error']['type']));
				$_SESSION['error'] = false;
			} else {
				$_SESSION['error']['wait']--;
			}
		}
	}
	
	public static function report($message, $type = 'warning')
	{
		Data::set('error', array('message' => __($message), 'type' => $type));
	}
	
	public static function flash($url, $message, $type = 'warning', $wait = 1)
	{
		$_SESSION['error'] = array('message' => __($message), 'type' => $type, 'wait' => $wait);
		redirect($url);
	}

	public static function reportFormErrors($errors) {
		self::$_form_errors = $errors;
	}

	public static function getFormError($field) {
		if (array_key_exists($field, self::$_form_errors)) {
			return self::$_form_errors[$field];
		}
	}

	public static function getFormErrors() {
		return self::$_form_errors;
	}
}
