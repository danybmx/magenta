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

/**
 * Renderer class for echo
 *
 * {?var}text if ok{end}
 * {var?} => printed if exist
 */
class Template_Renderer_Conditionals implements Template_RendererInterface
{
	protected $content = null;

	public function __construct($content)
	{
		$this->content = $content;
	}

	public function parse()
	{
		$matches = array();
		$tmp = $this->content;

		preg_match_all('/\{\?([\'a-zA-Z0-9\.\-\_\ ]*?)\}/', $tmp, $matches);
		foreach ($matches[0] as $k => $m) {
			$value = strpos($matches[1][$k], '\'') === 0 ? $matches[1][$k] : '###VAR###'.$matches[1][$k].'###VAR###';
			$string = '<?php if (isset('.$value.') && '.$value.') { ?>';
			$tmp = str_replace($m, $string, $tmp);
		}
		
		preg_match_all('/\{([\'a-zA-Z0-9\.\-\_\ ]*?)\?\}/', $tmp, $matches);
		foreach ($matches[0] as $k => $m) {
			$value = strpos($matches[1][$k], '\'') === 0 ? $matches[1][$k] : '###VAR###'.$matches[1][$k].'###VAR###';
			$string = '<?php if (isset('.$value.') && '.$value.') { echo '.$value.'; } ?>';
			$tmp = str_replace($m, $string, $tmp);
		}
		
		$tmp = str_replace('{else}', '<?php } else { ?>', $tmp);
		
		return $tmp;
	}
}