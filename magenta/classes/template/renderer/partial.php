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

class Template_Renderer_Partial implements Template_RendererInterface
{
	protected $content;

	public function __construct($content)
	{
		$this->content = $content;
	}

	public function parse()
	{
		$content = $this->content;
		preg_match_all('/\<\?php[ ]?echo[ ]\#\#\#VAR\#\#\#partial\#\#\#VAR\#\#\#\((.*?)\)[ ]\?\>/', $content, $matches);
		foreach ($matches[1] as $k => $match) {
			$content = str_replace($matches[0][$k], '<?php echo Template::partial('.$match.'); ?>', $content);
		}
		
		return $content;
	}
}