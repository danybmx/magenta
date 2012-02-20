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
 * In templates, using {var} this make <?php echo $var; ?>.
 */
class Template_Renderer_Translate implements Template_RendererInterface
{
	protected $content = null;

	public function __construct($content)
	{
		$this->content = $content;
	}

	public function parse()
	{
		$matches = array();
		$inside_matches = array();
		$tmp = $this->content;

		preg_match_all('/\_\{([\'a-zA-Z0-9\.\-\_\ ]*?)\}/', $tmp, $matches);
		foreach ($matches[0] as $k => $m) {
			$value = strpos($matches[1][$k], '\'') === 0 ? $matches[1][$k] : '###VAR###'.$matches[1][$k].'###VAR###';
			$string = '<?php echo __('.$value.'); ?>';
			$tmp = str_replace($m, $string, $tmp);
		}
		
		preg_match_all('/###TRANS###(.*?)###TRANS###/', $tmp, $inside_matches);
		foreach ($inside_matches[0] as $k => $m) {
			$string = '__('.$inside_matches[1][$k].')';
			$tmp = str_replace($m, $string, $tmp);
		}
		
		$this->content = $tmp;
		
		return $this->content;
	}
}