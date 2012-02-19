<?php
/**
 * Template Engine Class
 *
 * @author danybmx <dany@dpstudios.es>
 * @package template
 */
class Template {
	/**
	 * Layout to use
	 * @var string
	 */
	protected $layout;

	/**
	 * View to use
	 * @var string
	 */
	protected $view;

	/**
	 * File of Layout
	 * @var string
	 */
	protected $layout_file;

	/**
	 * File of view
	 * @var string
	 */
	protected $view_file;

	/**
	 * Compiled file
	 * @var string
	 */
	protected $compiled_file;

	/**
	 * Content of layout and view
	 * @var string
	 */
	protected $content;

	/**
	 * Parsed content to be used
	 * @var string
	 */
	protected $parsed;

	/**
	 * Plugins to help parse the content
	 * @var array
	 */
	protected $renderers = array('conditionals', 'loop', 'echo', 'translate', 'var');

	/**
	 * Protected strings and chars
	 * @var array
	 */
	protected $protectedStrings = array('{', '}');

	/**
	 * Replacements for escape protected strings
	 * @var array
	 */
	protected $replacedString = array('##OPENBRACKETS##', '##CLOSEBRAKETS##');

	/**
	 * Constructor function for set the layout and view selected
	 *
	 * If view is null the template class only use the layout (nice for errors for example)
	 *
	 * @param string $layout name of the used layout
	 * @param string $view name of the used view
	 */
	function __construct($layout, $view = null)
	{
		$this->layout = $layout;
		$this->view = $view;
		$this->vars = array(
			'current_url' => Request::$uri 
		);
	}

	/**
	 * Function to insert a partial view inside other
	 *
	 * <code>
	 * echo $this->partial('form', array('name' => $name, 'description' => $description));
	 * </code>
	 *
	 * This load the '_form' view and pass the params 'name' and 'description' for can use inside the view.
	 *
	 * @param string $name name of the
	 * @param array $params params for use in the view
	 */
	public function partial($name, $params = array())
	{
		$template = new self('partial', Request::$controller.'/_'.$name);
		$template->setParams($params, true);
		$template->render(true);
	}

	/**
	 * Function for set a new param for use in the layout or view
	 *
	 * @param string $param name of the param
	 * @param mixed $value value for set in the param
	 * @param bool $force true for replace the old if exists
	 */
	public function set($param, $value, $force = true)
	{
		if ( ! array_key_exists($param, $this->vars) || ($this->vars[$param] && $force)) {
			$this->vars[$param] = $value;
		}
	}

	/**
	 * Function for set some params at the same time
	 *
	 * @param array $params array with params array('key', 'value')
	 * @param bool $force true for replace the old if exists
	 */
	public function setParams($params, $force = true)
	{
		foreach ($params as $key => $value) {
			$this->set($key, $value, $force);
		}
	}

	/**
	 * Function for add the params and parse the content
	 *
	 * - extract the params setted for use in layout / view
	 * - search file of layout and file of view
	 * - get the content of files
	 * - parse content with helpers
	 * - return if show is true and echo if false
	 *
	 * @param bool $show if true, show the parsed content but if false returns an string with it
	 * @return string parsed content if show is false
	 */
	public function render($show = true)
	{
		extract($this->vars);
		
		$this->searchLayoutAndView();
		$this->getContent();
		$this->parseContent();
		
		if ($show)
			require_once $this->compiled_file;
		else {
			ob_start();
			require_once $this->compiled_file;
			return ob_get_clean();
		}
	}

	/**
	 * Function for search and check if exists layout and view files
	 */
	private function searchLayoutAndView()
	{
		if ($this->layout) {
			if (file_exists(VIEWS.DS.'layouts'.DS.$this->layout.'.mtp')) {
				$this->layout_file = VIEWS.DS.'layouts'.DS.$this->layout.'.mtp';
			}	else if (file_exists(MVIEWS.DS.'layouts'.DS.$this->layout.'.mtp')) {
				$this->layout_file = MVIEWS.DS.'layouts'.DS.$this->layout.'.mtp';
			} else {
				trigger_error('The layout '.$this->layout.' does not exists');
			}
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

	/**
	 * Function for get content of layout and view in a single big string
	 */
	private function getContent()
	{
		$content_for_layout = null;
		if ($this->view) {
			$content_for_layout = file_get_contents($this->view_file);
		}
		if ($this->layout) {
			$layout = file_get_contents($this->layout_file);
		} else {
			$layout = '{content_for_layout}';
		}
		$this->content = str_replace('{content_for_layout}', $content_for_layout, $layout);
	}

	/**
	 * Function for parse content
	 *
	 * If cache option is active it try to load before parse again
	 * or save if the cache does not exists or is old
	 *
	 * app.template.cache bool (true for use false for not)
	 * app.template.refresh int (time in hours)
	 *
	 * @return string parsed content
	 */
	private function parseContent()
	{
		if ($this->layout) {
			$this->compiled_file = CACHE.DS.'templates'.DS.$this->layout.'.cached';
			if ($this->view)
						$this->compiled_file = CACHE.DS.'templates'.DS.$this->layout.'.'.str_replace('/', '.', $this->view).'.cached';
		} elseif ($this->view) {
			$this->compiled_file = CACHE.DS.'templates'.DS.str_replace('/', '.', $this->view).'.cached';
		}

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

	/**
	 * Function for replace escaped chars
	 */
	private function removeEscapedChars()
	{
		$protected = array();
		foreach ($this->protectedStrings as $p) {
			$protected[] = '\\'.$p;
		}
		$this->parsed = str_replace($protected, $this->replacedString, $this->parsed);
	}

	/**
	 * Function for unescape escaped chars
	 */
	private function addEscapedChars()
	{
		$this->parsed = str_replace($this->replacedString, $this->protectedStrings, $this->parsed);
	}

	/**
	 * Function for compress the layout removing CR's and spaces
	 */
	private function compress()
	{
		$i = array('#<!-[^\[].+->#', '/[\r\n\t]+/', '/>[\s]+</', '/[\s]+/');
		$ii = array('', ' ', '><', ' ');
		$this->parsed = preg_replace($i, $ii, $this->parsed);
	}
}
