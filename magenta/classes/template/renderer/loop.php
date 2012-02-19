<?php
/**
 * Magenta, PHP Lightweight and easy to use MVC Framework
 *
 * @version 0.1
 * @package template
 * @author dpStudios Development Team
 * @copyright dpStudios 2009-2011
 * @link http://magenta.dpstudios.es
 */

/**
 * Renderer class for echo
 *
 * In templates, using {var} this make <?php echo $var; ?>.
 */
class Template_Renderer_Loop implements Template_RendererInterface
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

		preg_match_all('/\{loop (.*?)\}/', $tmp, $matches);
		foreach ($matches[0] as $k => $m) {
			$as = explode('.', strtolower($matches[1][$k]));
			$tmp = '<?php $_pos = -1; foreach (###VAR###'.$matches[1][$k].'###VAR### as $_key => ###VAR###'.Inflector::singularize($as[count($as)-1]).'###VAR###) { $_pos++; ?>';
			$this->content = str_replace($m, $tmp, $this->content);
		}

		$this->content = str_replace('{end}', '<?php } ?>', $this->content);
		
		return $this->content;
	}
}