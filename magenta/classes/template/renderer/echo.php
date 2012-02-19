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
 * Renderer class for echo
 *
 * In templates, using {var} this make <?php echo $var; ?>.
 */
class Template_Renderer_Echo implements Template_RendererInterface
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

		preg_match_all('/\{([%a-zA-Z0-9\.\-\_]*?)\}/', $tmp, $matches);
		foreach ($matches[0] as $k => $m) {
			if (strpos($matches[1][$k], '.') === 0) {
				$string = '\'.###VAR###'.substr($matches[1][$k], 1).'###VAR###.\'';
			} else
				$string = '<?php echo ###VAR###'.$matches[1][$k].'###VAR###; ?>';
			$tmp = str_replace($m, $string, $tmp);
		}
		$this->content = $tmp;
		
		return $this->content;
	}
}