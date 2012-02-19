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

class Template_Renderer_Array implements Template_RendererInterface
{
	protected $content;

	public function __construct($content)
	{
		$this->content = $content;
	}

	public function parse()
	{
		$content = $this->content;
		
		preg_match_all('/\#\#\#ARRAY\#\#\#\[(.*)\]\#\#\#ARRAY\#\#\#/', $content, $matches);

		foreach ($matches[0] as $key => $match) {
			$match = str_replace(array('###ARRAY###[', ']###ARRAY###'), '', $match);

			$tmp = 'array(';
			$items = explode(',', $match);
			
			foreach ($items as $item) {
				$item_array = explode(': ', $item);
				
				if (count($item_array) > 1) {
					$item_array[0] = trim($item_array[0]);
					$tmp .= "'{$item_array[0]}' => ";
					array_shift($item_array);
				}
				
				$item_array[0] = implode(': ', $item_array);
				
				$item_array[0] = trim($item_array[0]);
				switch (substr($item_array[0], 0, 1)) {
					case '\'':
						$tmp .= $item_array[0];						
						break;
					case ':':
						$tmp .= '###VAR###'.substr($item_array[0], 1).'###VAR###';
						break;
					case ('_'):
						$tmp .= preg_replace('/\_\{(.*)\}/', '###TRANS###$1###TRANS###', $item_array[0]);
						break;
					default:
						if (is_numeric($item_array[0]) || is_bool($item_array[0]) == true || $item_array[0] == true)
							$tmp .= $item_array[0];
						else {
							$tmp .= '###VAR###'.$item_array[0].'###VAR###';
						}
						break;
				}
				$tmp .= ', ';
			}
			$tmp = substr($tmp, 0, -2);
			$tmp .= ')';
			
			$content = str_replace($matches[0][$key], $tmp, $content);
		}
		
		return $content;
	}

	public static function create($content)
	{
		$v = new self($content);
		return $v->parse();
	}
}
