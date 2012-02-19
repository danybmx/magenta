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

class Template_Renderer_Helpers implements Template_RendererInterface
{
	protected $content;
	
	public function __construct($content)
	{
		$this->content = $content;
	}

	public function parse()
	{
		$matches = array();
		$replacement = array();

		preg_match_all('/\<\%(.*?)\%\>/', $this->content, $matches);
		foreach ($matches[1] as $k => $match) {
			$tmp = '<?php ';
			$tmp .= (strpos($match, '=') === 0) ? 'echo ' : '';

			/* save arrays from this */
			$arrays = array();
			preg_match_all('/\[(.*)\]/', $match, $arrs);
			foreach ($arrs[0] as $key => $value) {
				$arrays[$key] = '###ARRAY###'.$value.'###ARRAY###';
				$match = str_replace($value, '###ARRAY'.$key.'###', $match);
			}

			/* remove whitespaces from init and end */
			$match = preg_replace('/^\=/', '', $match);
			$match = trim($match);

			/* get class */
			$class = preg_replace('/^(.*?)\ (.*)/', '$1', $match);
			$match = str_replace($class, '', $match);
			$tmp .= '###VAR###'.$class.'###VAR###(';
			
			/* get pieces of string */
			$pieces = explode(', ', $match);
			$pieces = array_map('trim', $pieces);
			$class = $pieces[0];
			
			foreach ($pieces as $p => $piece) {
				switch (substr($piece, 0, 1)) {
					case ':':
						$piece = trim($piece, ':');
						$tmp .= '###VAR###'.$piece.'###VAR###';
						break;
					default:
						$tmp .= $piece;
						break;
				}
				if ($p < count($pieces)-1) {
					$tmp .= ', ';
				}
			}
			
			/* rescue arrays */
			preg_match_all('/\#\#\#ARRAY([0-9]*?)\#\#\#/', $tmp, $arrs);
			foreach ($arrs[0] as $key => $value) {				
				$tmp = str_replace($arrs[0][$key], $arrays[$key], $tmp);
			}
			$tmp .= ')';
			$tmp .= ' ?>';
			$replacement[$k] = $tmp;
		}

		$this->content = str_replace($matches[0], $replacement, $this->content);
		
		return $this->content;
	}
}