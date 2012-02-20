<?php
/**
 * Magenta, PHP Lightweight and easy to use MVC Framework
 *
 * @version 0.1
 * @package Template
 * @author dpStudios Development Team
 * @copyright dpStudios 2009-2011
 * @link http://magenta.dpstudios.es
 */

class Template_Renderer_Var implements Template_RendererInterface
{
	protected $content;

	public function __construct($content)
	{
		$this->content = $content;
	}

	public function parse()
	{
		$content = $this->content;
		preg_match_all('/\#\#\#VAR\#\#\#([%a-zA-Z0-9\-\_].*?)\#\#\#VAR\#\#\#/', $content, $matches);
		foreach ($matches[1] as $k => $match) {
			if (preg_match('/-/', $match)) {
				$exploded = explode('-', $match);
				$root = $exploded[0];
				$pieces = array();

				array_shift($exploded);
				while(count($exploded) > 0) {
					$pieces[] = $exploded[0];
					array_shift($exploded);
				}

				$match = $root;
				foreach ($pieces as $piece) {
					$match .= '[\''.$piece.'\']';
				}
			}

			if (strpos($match, '%') === 0)
				$match = substr($match, 1);
			else
				$match = '$'.$match;

			if (strpos($match, '.') > 0)
				$match = str_replace('.', '->', $match);

			if (strpos($match, ':') > 0) {
				$match = str_replace(':', '::', $match);
				$match = substr($match, 1);
			}

			$matches[1][$k] = $match;
		}

		$content = str_replace($matches[0], $matches[1], $content);
		return $content;
	}

	public static function create($content)
	{
		$v = new self($content);
		return $v->parse();
	}
}