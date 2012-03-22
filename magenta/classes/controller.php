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

class Controller
{
	protected $layout;
	protected $view;
	protected $render;
	protected $filters = array();
	protected $data = array();
	protected $params = array();
	
	public function __construct()
	{
		$this->params = array(
			'webtitle' => Config::load('app.webtitle'),
			'meta_title' => Config::load('app.meta_title'),
			'meta_description' => Config::load('app.meta_description'),
			'meta_tags' => Config::load('app.meta_tags')
		);

		$this->filters['#'] = array_merge(array('admin,owner', array(
					'url' => '/admin/login',
					'message' => 'Do not have enough permissions',
					'type' => 'warning',
					'exclude' => 'login,logout',
					'no_message' => 'index'
				)), array_key_exists('#', $this->filters) ? $this->filters['#'] : array());

		/* Filter for all */
		if (array_key_exists('*', $this->filters)) $this->filter($this->filters['*']);

		/* Filters for admin */
		if (array_key_exists('#', $this->filters) && Request::$admin) $this->filter($this->filters['#']);

		/* Filters for single */
		if (array_key_exists(Request::$action, $this->filters)) $this->filter($this->filters[Request::$action]);
		
		if ( ! $this->render)
			$this->render = true;

		if ( ! $this->view)
			$this->view = Request::$controller.DS.Request::$action;

		if ( ! $this->layout && $this->layout !== false)
			if ( ! Request::$admin) {
				$this->layout = Config::load('app.template.layout');
			} else {
				$this->layout = 'admin';
			}
		
		$this->_updateDataAndFiles();
	}
	
	public function filter($filter)
	{
		$url = '/';
		$message = __('Sorry, you do not have permission for access here');
		$type = 'warning';
		
		if ( ! is_array($filter))
			$roles = explode(',', $filter);
		else {
			$roles = explode(',', $filter[0]);
			array_shift($filter);
			if ($filter) {
				if (array_key_exists('exclude', $filter[0])) {
					$excluded = explode(',', $filter[0]['exclude']);
					if (in_array(Request::$action, $excluded))
						return true;
				}
				$url = array_key_exists('url', $filter[0]) ? $filter[0]['url'] : $url;
				$message = array_key_exists('message', $filter[0]) ? $filter[0]['message'] : $message;
				$type = array_key_exists('type', $filter[0]) ? $filter[0]['type'] : $type;
				if (array_key_exists('no_message', $filter[0]) && in_array(Request::$action, explode(',', $filter[0]['no_message'])))
					$message = $type = '';
			}
		}
		
		if ( ! UserComponent::CheckRol($roles)) {
			$last = urlencode(BASE_PATH.DS.Request::$uri);
			$url = strpos($url, '?') === false ? $url.'?last='.$last : $url.'&last='.$last;
			if ( ! $message && ! $type) {
				redirect($url);
				return false;
			}

			Error::flash($url, $message, $type);
		}
	}
	
	/**
	 * Actualizar datos recogidos de $_POST[] y $_FILES[];
	 *
	 * @return void
	 * @author Daniel Rodríguez Gil
	 */
	private function _updateDataAndFiles()
	{
		$data = $this->data;
		if (isset($_POST['data']) && is_array($_POST['data'])) {
			$data = array_merge_recursive($data, $_POST['data']);
			unset($_POST['data']);
		}
		
		if ($_FILES) {
			$this->files = $_FILES;
		}
		
		$this->data = $data;
	}
	
	public function setData($data)
	{
		foreach ($data as $k => $v) {
			$this->data[$k] = $v;
		}
	}
	
	public function beforeRender()
	{
	}

	public function set($param, $value, $force = true)
	{
		if ( ! $force && array_key_exists($param, $this->params))
			return false;
		
		$this->params[$param] = $value;
	}
	
	public function checkErrors()
	{
		$this->set('display_errors', Error::getErrors());
	}
	
	public function __destruct()
	{
		if ($this->render && ! Error::getParsed()) {
			$this->beforeRender();
			$this->checkErrors();
			Data::set('global', $this->data);
			$this->params['global_data'] = $this->data;
			$template = new Template($this->layout, $this->view);
			$template->setParams($this->params, true);
			$template->render(true);
		}
	}
}