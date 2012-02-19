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
 * Template Engine Class
 */
class Template {
	protected $layout, $view, $vars = array();
	protected $layout_file, $view_file, $compiled_file;
	protected $content, $parsed;

	protected $renderers = array(
		'helpers', 'partial', 'conditionals', 'loop', 'echo', 'array', 'translate', 'var'
	);

	protected $protectedStrings = array(
		'{', '}', '<%', '%>'
	);
	protected $replacedString = array(
		'##OPENBRACKETS##', '##CLOSEBRAKETS##', '##OPENCODE##', '##CLOSECODE##'
	);

	function __construct($layout, $view = null)
	{
		$this->layout = $layout;
		$this->view = $view;
		$this->vars = array(
			'current_url' => Request::$uri 
		);
	}
	
	public static function partial($name, $params = array())
	{
		$template = new self('partial', Request::$controller.'/_'.$name);
		$template->setParams($params, true);
		$template->render(true);
	}

	public function set($param, $value, $force = true)
	{
		if ( ! array_key_exists($param, $this->vars) || ($this->vars[$param] && $force)) {
			$this->vars[$param] = $value;
		}
	}

	public function setParams($params, $force = true)
	{
		foreach ($params as $key => $value) {
			$this->set($key, $value, $force);
		}
	}

	public function render($show = true)
	{
		extract($this->vars);
		
		$this->searchLayoutAndView();
		$this->getContent();
		$this->parseContent();
		
		if ($show) {
			require_once $this->compiled_file;
		} else {
			ob_start();
			require_once $this->compiled_file;
			return ob_get_clean();
		}
	}

	private function searchLayoutAndView()
	{
		if (file_exists(VIEWS.DS.'layouts'.DS.$this->layout.'.mtp')) {
			$this->layout_file = VIEWS.DS.'layouts'.DS.$this->layout.'.mtp';
		}	else if (file_exists(MVIEWS.DS.'layouts'.DS.$this->layout.'.mtp')) {
			$this->layout_file = MVIEWS.DS.'layouts'.DS.$this->layout.'.mtp';
		} else {
			trigger_error('The layout '.$this->layout.' does not exists');
		}

		if ($this->view !== null) {
			if (file_exists(VIEWS.DS.$this->view.'.mtp')) {
				$this->view_file  = VIEWS.DS.$this->view.'.mtp';
			}	else if (file_exists(MVIEWS.DS.$this->view.'.mtp')) {
				$this->view_file = MVIEWS.DS.$this->view.'.mtp';
			} else {
				trigger_error('The view '.$this->view.' does not exists');
			}
		}
	}

	private function getContent()
	{
		$content_for_layout = null;
		if ($this->view) {
			$content_for_layout = file_get_contents($this->view_file);
		}
		
		$layout = file_get_contents($this->layout_file);
		$this->content = str_replace('{content_for_layout}', $content_for_layout, $layout);
	}

	private function parseContent()
	{
		$this->compiled_file = CACHE.DS.'templates'.DS.$this->layout.'.cached';
		if ($this->view)
			$this->compiled_file = CACHE.DS.'templates'.DS.$this->layout.'.'.str_replace('/', '.', $this->view).'.cached';
		
		if (Config::load('app.template.cache')) {
			if (file_exists($this->compiled_file)) {
				if ( Config::load('app.template.refresh') !== false && filemtime($this->compiled_file) < (time()+Config::load('app.template.refresh')*3600)) {
					$this->parsed = file_get_contents($this->compiled_file);
					return 'cached';
				}
			}
		}
		
		$this->parsed = $this->content;
		if (Config::load('app.template.compress'))
			$this->compress();
		
		$this->removeEscapedChars();
		foreach ($this->renderers as $renderer) {
			$r_class = 'Template_Renderer_'.Inflector::camelize($renderer);
			$r = new $r_class($this->parsed);
			$this->parsed = $r->parse();
		}
		$this->addEscapedChars();
	
		if ( ! is_writable(dirname($this->compiled_file))) {
			trigger_error('<span style="font-weight: bold; font-size: 15px; color: #F22">The cache directory /tmp/cache is not writable, make it writable and try again</span>');
			exit();
		}
	
		file_put_contents($this->compiled_file, $this->parsed);
		chmod($this->compiled_file, 0777);
	}

	private function removeEscapedChars()
	{
		$protected = array();
		foreach ($this->protectedStrings as $p) {
			$protected[] = '\\'.$p;
		}
		$this->parsed = str_replace($protected, $this->replacedString, $this->parsed);
	}

	private function addEscapedChars()
	{
		$this->parsed = str_replace($this->replacedString, $this->protectedStrings, $this->parsed);
	}

	private function compress()
	{
		$i = array('#<!-[^\[].+->#', '/[\r\n\t]+/', '/>[\s]+</', '/[\s]+/');
		$ii = array('', ' ', '><', ' ');
		$this->parsed = preg_replace($i, $ii, $this->parsed);
	}
}
