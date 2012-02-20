<?php
/**
 * Form Helper
 *
 * Make forms, show errors of validation, default data, etc...
 *
 * @author danybmx <dany@dpstudios.es>
 * @package Helpers
 */
class Form {
	
	public static function create($name, $action, $options = array())
	{
		$html = '';
		$default_options = array(
			'action' => $action,
			'method' => 'post',
			'enctype' => 'multipart/form-data',
			'ajax' => false,
			'container' => null,
			'autocomplete' => 'off'
			);
			
		$options = array_merge($default_options, $options);
		$action = make_url($action);
		$find = array('/^http\:\/\//', '/^https\:\/\//');
		$options['action'] = ((preg_match($find['0'], $action) || preg_match($find['1'], $action)) && substr($action, 0, 1) != '/') ? make_url($action) : make_url('/'.$action);
		
		if ($options['ajax'] == true ) {
			if ($options['container'] != null) {
				$html .= '
				<script type="text/javascript">
					ajaxform(\''.$name.'\', \''.$options['container'].'\');
				</script>
				';
			}
		}
		unset($options['ajax']);
		unset($options['container']);

		$options['class'] = array_key_exists('class', $options) ? $options['class'].' dp' : 'dp';

		$options = self::_makeOptionsString($options);
		$html .= '<form id="'.$name.'" '.$options.'>';
		return $html;
	}
	
	public static function hidden($name, $value = null, $options = array())
	{
		$html = '';
		
		$defaultOptions = array(
			'id' => 'input'.ucfirst($name),
			'name' => 'data['.$name.']',
			'type' => 'hidden',
			'value' => $value,
			'default' => false
			);
		
		/** Getting all options **/
		$options = array_merge($defaultOptions, $options);
		
		/** Value **/
		$options['value'] = $options['value'] ? $options['value'] : self::getData($name);
		$options['value'] = $options['value'] ? $options['value'] : $options['default'];
		
		$html .= '<input'.self::_makeOptionsString($options).' />';
		
		return $html;
	}
	
	public static function input($name, $options = array())
	{
		$html = '';

		$defaultOptions = array(
			'id' => 'input'.ucfirst($name),
			'name' => 'data['.$name.']',
			'type' => 'text',
			'class' => null,
			'value' => null,
			'div' => true,
			'div_class' => '',
			'comment' => null,
			'comment_class' => 'form-comment',
			'default' => false,
			'label' => false,
			'clear' => true,
			'style' => false
			);
		
		/** Allowed options for input tag **/
		$inputOptions = array('id', 'name', 'type', 'style', 'class', 'value', 'checked', 'placeholder', 'data', 'icon');
			
		/** Getting all options **/
		$options = array_merge($defaultOptions, $options);
		
		/** Value **/
		$options['value'] = $options['value'] ? $options['value'] : self::getData($name);
		$options['value'] = $options['value'] !== null ? $options['value'] : $options['default'];
		
		if ($options['div'] == true)
			$html .= '<div class="magenta-input '.$options['type'].''.($options['div_class'] ? ' '.$options['div_class'] : '').'">';
			
		if ($options['default'] && $options['value'] == null) $options['value'] = $options['default'];
		
		if ($options['label'] !== false)
			$html .= '<label for="'.$options['id'].'" class="input '.$options['type'].'">'.$options['label'].'</label>';
		
		if ($options['type'] == 'password')
			$options['value'] = '';
		
		switch ($options['type']) {
			case 'textarea': # textarea
				$value = $options['value'];
				unset($options['value'], $options['type']);
				$html .= '<textarea'.self::_makeOptionsString($options, $inputOptions).'>'.$value.'</textarea>';
				break;
				
			case 'checkbox': # checkbox 
				$html .= '<input'.self::_makeOptionsString(array('type' => 'hidden', 'value' => '0', 'name' => $options['name'])).' />';
				if ($options['value'] == 1) $options['checked'] = 'checked';
				$html .= '<input'.self::_makeOptionsString(array_merge($options, array('value' => 1)), $inputOptions).' />';
				break;
				
			case 'radio': # radio
				$html .= '<input'.self::_makeOptionsString($options, $inputOptions).' />';
				break;
			
			default: # text
				$html .= '<input'.self::_makeOptionsString($options, $inputOptions).' />';
				break;
		}
		
		if ($options['comment'])
			$html .= '<div class="magenta-comment '.$options['comment_class'].'">'.$options['comment'].'</div>';
		
		if ($options['div'] == true) {
			if ($options['clear'] == true)
				$html .= '<div class="clear"></div>';
				
			$html .= '</div>';
		}
			
		return $html;
	}
	
	public static function combo($name, $data, $options = array())
	{
		$html = '';

		$defaultOptions = array(
			'id' => 'input'.ucfirst($name),
			'name' => 'data['.$name.']',
			'class' => null,
			'value' => null,
			'div' => true,
			'div_class' => '',
			'comment' => null,
			'comment_class' => 'form-comment',
			'default' => false,
			'label' => false,
			'clear' => true
			);
		
		/** Allowed options for input tag **/
		$inputOptions = array('id', 'name', 'type', 'style', 'class', 'value', 'selected', 'placeholder', 'data');
			
		/** Getting all options **/
		$options = array_merge($defaultOptions, $options);
		
		/** Value **/
		$options['value'] = $options['value'] ? $options['value'] : self::getData($name);
		$options['value'] = $options['value'] ? $options['value'] : $options['default'];
		
		if ($options['div'] == true)
			$html .= '<div class="magenta-input combo'.($options['div_class'] ? ' '.$options['div_class'] : '').'">';
			
		if ($options['default'] && ! $options['value']) $options['value'] = $options['default'];
		
		if ($options['label'] !== false)
			$html .= '<label for="'.$options['id'].'" class="input combo">'.$options['label'].'</label>';
		
		$html .= '<select'.self::_makeOptionsString($options, $inputOptions).'>';
		foreach ($data as $key => $value) {
			$selected = ($options['value'] == $key) ? ' selected="selected"' : '';
			$html .= '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
		}
		$html .= '</select>';
		
		if ($options['div'] == true) {
			if ($options['clear'] == true)
				$html .= '<div class="clear"></div>';
				
			$html .= '</div>';
		}
			
		return $html;
	}
	
	public static function end($label = NULL, $options = array())
	{
		$html = '';
		$options = self::_makeOptionsString($options);
		
		if ($label !== NULL)
			$html .= '<div class="magenta-input submit"><input type="submit" value="'.$label.'"'.$options.' /></div>';

		$html .= '</form>';
		return $html;
	}
	
	private static function getData($name)
	{
		$data = Data::get('global');
		
		if (array_key_exists($name, $data))
			return $data[$name];
			
		return false;
	}
	
	/**
	 * Function for parse options in a string from an array
	 *
	 * @static
	 * @param array $options
	 * @return string with options parset in a string
	 */
	private static function _makeOptionsString($options = array(), $allowed = null) {
		$string = '';
		if (is_array($options)) {
			foreach ($options as $option => $value) {
				if ($allowed && ! in_array($option, $allowed)) continue;
				
				if ($value !== null)
					$string .= ' '.$option.'="'.$value.'"';
			}
		} 

		return $string;
	}
}

?>
