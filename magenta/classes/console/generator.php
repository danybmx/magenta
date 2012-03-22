<?php
/**
 * Generator for magenta framework
 *
 * Generate models and database structure (g m Model field:type)
 *
 * @author danybmx <dany@dpstudios.es>
 * @package Console
 */
class Console_Generator
{
	public static $_con;

	public static function init($args) {
		global $con;
		self::$_con = $con;
		if ( ! $args || ! preg_match('/[m|c]/', $args[0]) || ! array_key_exists('1', $args))
			self::help();
		else {
			$action = $args[0];
			array_shift($args);
			switch ($action) {
				case 'm':
					self::model($args);
					break;
				case 'c':
					self::controller($args);
					break;
				case 'crud':
					self::crud($args);
					break;
			}
		}
	}

	public static function help() {
		self::$_con->write('You can generate:');
		self::$_con->cr();
		self::$_con->write('Model: script/mangeta g m Name field:type');
		self::$_con->write('Controller: script/mangeta g c Name');
		self::$_con->write('Crud: script/mangeta g crud Model');
	}
	
	public static function crud($args) {
		$model = $args[0];
		array_shift($args);
		$controller = array_key_exists('0', $args) ? $args[0] : Inflector::camelize(Inflector::tableize($model));
		$controller_file = Inflector::underscore($controller);
		$singular = strtolower($model);
		$plural = Inflector::pluralize(strtolower($model));
		$url = strtolower($controller);
		if ( ! class_exists($model))
			self::$_con->write('The model '.$model.' does not exists');
		
		$functions = '';
		$_model = ActiveRecord::get($model);
		$_fields = $_model->getFields();
		$columns = ActiveRecord::query('SHOW COLUMNS FROM '.$_model->getTable())->fetchAll();
		$fields = array();
		foreach ($columns as $c) {
			if ($c->Field == $_model->getPK())
				$c->Type = 'pk';
				
			if ($c->Field == 'password')
				$c->Type = 'password';
			
			if (preg_match('/image/', $c->Field))
				$c->Type = 'image';
				
			if ($c->Type == 'tinyint(4)')
				$c->Type = 'bool';
				
			if (in_array($c->Field, $_fields))
				$fields[$c->Field] = $c->Type;
		}
		
		$views_folder = VIEWS.DS.$controller_file;

		if ( ! file_exists($views_folder))
			mkdir($views_folder);
		
		// index
		$index_view_file = $views_folder.DS.'index.mtp';
		if (file_exists($index_view_file))
			$confirm = self::$_con->read('The index view exists, overwrite?', array('y', 'n'), 'n');
		else $confirm = 'y';
		
		if (strtolower($confirm) == 'y') {
			$index_view_fields = '';
			foreach ($fields as $f => $t) {
				switch ($t) {
					case 'image':
						$index_view_fields .= '<div class="'.$f.'">'."\r\n\t\t".'<span class="title">_{\''.Inflector::titleize($f).'\'}:</span>'."\r\n\t\t".'<span class="content"><?php echo Html::image(\'public/{'.strtolower($model).'.'.$f.'}\', array(\'alt\' => \'Image \'.$'.strtolower($model).'->'.$f.')); ?></span>'."\r\n\t".'</div>'."\r\n\t";
						break;
						
					case 'bool':
						$index_view_fields .= '<div class="'.$f.'">'."\r\n\t\t".'<span class="title">_{\''.Inflector::titleize($f).'\'}:</span>'."\r\n\t\t".'<span class="content"><?php echo '.$model.'.'.$f.' ? __(\'Yes\') : __(\'No\'); ?></span>'."\r\n\t".'</div>'."\r\n\t";
						break;
					
					default:
						$index_view_fields .= '<div class="'.$f.'">'."\r\n\t\t".'<span class="title">_{\''.Inflector::titleize($f).'\'}:</span>'."\r\n\t\t".'<span class="content">{'.strtolower($model).'.'.$f.'}</span>'."\r\n\t".'</div>'."\r\n\t";
						break;
				}
			}
			$index_view_fields .= '<?php echo Html::link(__(\'Show\').\' \'.__(\''.$model.'\'), \''.strtolower($controller).'/show/\'.$'.strtolower($model).'->id, array(\'class\' => \'featured\')); ?>'."\r\n\t";
			
			$index_view_content = Skels::parse('index', array(
				'name' => $model,
				'singular' => strtolower($model),
				'plural' => Inflector::pluralize(strtolower($model)),
				'fields' => $index_view_fields,
				'names' => Inflector::titleize(Inflector::pluralize(strtolower($model)))
			));
			file_put_contents($index_view_file, $index_view_content);
			self::$_con->write('View index created');	
		}
		
		$plural = Inflector::pluralize(strtolower($model));
		$functions .= <<<EOF
	public function index() {
		\$this->set('$plural', ActiveRecord::get('$model')->find());
	}


EOF;
		
		// show
		$show_view_file = $views_folder.DS.'show.mtp';
		if (file_exists($show_view_file))
			$confirm = self::$_con->read('The show view exists, overwrite?', array('y', 'n'), 'n');
		else $confirm = 'y';

		if (strtolower($confirm) == 'y') {
			$show_view_fields = '';
			foreach ($fields as $f => $t) {
				switch ($t) {
					case 'image':
						$show_view_fields .= "\t".'<div class="'.$f.'">'."\r\n\t\t".'<span class="title">_{\''.Inflector::titleize($f).'\'}:</span>'."\r\n\t\t".'<span class="content"><?php echo Html::image(\'public/{'.strtolower($model).'.'.$f.'}\', array(\'alt\' => \'Image \'.$'.strtolower($model).'->'.$f.')); ?></span>'."\r\n\t".'</div>'."\r\n";
						break;

					default:
						$show_view_fields .= '<div class="'.$f.'">'."\r\n\t".'<span class="title">_{\''.Inflector::titleize($f).'\'}:</span>'."\r\n\t".'<span class="content">{'.strtolower($model).'.'.$f.'}</span>'."\r\n".'</div>'."\r\n";
						break;
				}
			}

			$show_view_content = Skels::parse('show', array(
				'name' => Inflector::titleize($model),
				'singular' => strtolower($model),
				'plural' => Inflector::pluralize(strtolower($model)),
				'fields' => $show_view_fields,
				'url' => $url
			));
			file_put_contents($show_view_file, $show_view_content);
			self::$_con->write('View show created');
		}
		
		$singular = strtolower($model);
		$functions .= <<<EOF
	public function show(\$value) {
		\$this->set('$singular', ActiveRecord::get('$model')->findByPK(\$value));
	}


EOF;
		
		$admin = self::$_con->read('Create admin for crud?', array('y', 'n'), 'y');
		
		// CREATE ADMIN VIEWS
		if (strtolower($admin) == 'y') { # admin_index
			$admin_index_file = $views_folder.DS.'admin_index.mtp';
			if (file_exists($admin_index_file))
				$confirm = self::$_con->read('The view admin_index exists, overwrite?', array('y', 'n'), 'n');
			else $confirm = 'y';

			if (strtolower($confirm) == 'y') {
				$admin_index_fields = '';
				foreach ($fields as $f => $t) {
					$use_for_dg = self::$_con->read('Show "'.Inflector::titleize($f).'" in datagrid?', array('y', 'n'), 'y');
					if (strtolower($use_for_dg) == 'n') continue;
				
					switch ($t) {
						case 'date':
							$admin_index_fields .= '{key: \''.$f.'\', label: \''.Inflector::titleize($f).'\', type: \'date\'},'."\n\t\t\t";
							break;
					
						case 'datetime':
							$admin_index_fields .= '{key: \''.$f.'\', label: \''.Inflector::titleize($f).'\', type: \'datetime\'},'."\n\t\t\t";
							break;
						
						case 'bool':
							$admin_index_fields .= '{key: \''.$f.'\', label: \''.Inflector::titleize($f).'\', type: \'bool\'},'."\n\t\t\t";
							break;

						default:
							$admin_index_fields .= '{key: \''.$f.'\', label: \''.Inflector::titleize($f).'\'},'."\n\t\t\t";
							break;
					}
				}
			
				$vars = array(
					'title' => Inflector::titleize(Inflector::pluralize($model)),
					'name' => Inflector::titleize($model),
					'singular' => strtolower($model),
					'plural' => Inflector::pluralize(strtolower($model)),
					'fields' => $admin_index_fields,
					'url' => strtolower($controller),
					'relations' => ''
				);
			
				$admin_index_content = Skels::parse('admin_index', $vars);
				file_put_contents($admin_index_file, $admin_index_content);
				self::$_con->write('View admin_index created');
			} # !/admin_index
			
			$functions .= <<<EOF
	public function admin_index() {
		\$this->set('{$singular}', ActiveRecord::get('{$model}')->find(array('get' => '{$plural}', 'limit' => 20, 'page' => 1)));
	}


EOF;

            # /_form
            $admin_form_file = $views_folder.DS.'_form.mtp';
            if (file_exists($admin_form_file))
                $confirm = self::$_con->read('The view _form exists, overwrite?', array('y', 'n'), 'n');
            else $confirm = 'y';

            if (strtolower($confirm) == 'y') {
                $admin_form_fields = '';
                foreach ($fields as $f => $t) {
                    switch ($t) {
						case 'password':
							$admin_form_fields .= "<?php echo Form::input('{$f}', array('label' => __('".Inflector::titleize($f)."'), 'type' => 'password')); ?>\r\n";
							break;
							
                        case 'date':
                            $admin_form_fields .= "<?php echo Form::input('{$f}', array('label' => __('".Inflector::titleize($f)."'), 'magenta-form-type' => 'date')); ?>\r\n";
                            break;

                        case 'datetime':
                            $admin_form_fields .= "<?php echo Form::input('{$f}', array('label' => __('".Inflector::titleize($f)."'), 'magenta-form-type' => 'datetime')); ?>\r\n";
                            break;

                        case 'bool':
							$admin_form_fields .= "<?php echo Form::input('{$f}', array('label' => __('".Inflector::titleize($f)."'), 'type' => 'checkbox')); ?>\r\n";
                            break;

						case 'text':
							$admin_form_fields .= "<?php echo Form::input('{$f}', array('label' => __('".Inflector::titleize($f)."'), 'type' => 'textarea')); ?>\r\n";
							break;
							
						case 'pk':
							break;

                        default:
                            $admin_form_fields .= "<?php echo Form::input('{$f}', array('label' => __('".Inflector::titleize($f)."'))); ?>\r\n";
							break;
                    }
                }

                $vars = array(
                    'name' => Inflector::titleize($model),
                    'fields' => $admin_form_fields
                );

                $admin_form_content = Skels::parse('form', $vars);
                file_put_contents($admin_form_file, $admin_form_content);
                self::$_con->write('View _form created');
            } # !/_form

			# /admin_add
            $admin_add_file = $views_folder.DS.'admin_add.mtp';
            if (file_exists($admin_add_file))
                $confirm = self::$_con->read('The view admin_add exists, overwrite?', array('y', 'n'), 'n');
            else $confirm = 'y';

            if (strtolower($confirm) == 'y') {
                $vars = array(
                    'name' => Inflector::titleize($model),
					'singular' => strtolower($model),
					'url' => strtolower($controller)
                );

				$admin_add_content = Skels::parse('admin_add', $vars);
                file_put_contents($admin_add_file, $admin_add_content);
                self::$_con->write('View admin_add created');
            } # !/admin_add

			$functions .= <<<EOF
	public function admin_add(\$draft = false)
	{
		if (\$this->data) {
			if (\${$singular} = ActiveRecord::get('{$model}')->create(\$this->data)->save()) {
				if (\$draft) {
					\$this->render = false;
					echo \${$singular}->toJson();
					return true;
				}
				Error::flash('/admin/{$url}', __('The changes was saved'), 'notify');
			} else {
				if (\$draft) {
					\$this->render = false;
					echo json_encode(array('error' => true, 'message' => __('An error occurred, check the form'), 'fields' => Error::getFormErrors()));
					return true;
				}
				Error::report(__('An error occurred, check the form'));
			}
		}
	}


EOF;

			# /admin_edit
            $admin_edit_file = $views_folder.DS.'admin_edit.mtp';
            if (file_exists($admin_edit_file))
                $confirm = self::$_con->read('The view admin_edit exists, overwrite?', array('y', 'n'), 'n');
            else $confirm = 'y';

            if (strtolower($confirm) == 'y') {
                $vars = array(
					'singular' => strtolower($model),
                    'name' => Inflector::titleize($model),
					'url' => strtolower($controller)
                );

                $admin_edit_content = Skels::parse('admin_edit', $vars);
                file_put_contents($admin_edit_file, $admin_edit_content);
                self::$_con->write('View admin_edit created');
            } # !/admin_edit

			$functions .= <<<EOF
	public function admin_edit(\$id)
	{
		if (\$this->data) {
			if (ActiveRecord::get('{$model}')->create(\$this->data)->save()) {
				Error::flash('/admin/{$url}', __('The changes was saved'), 'notify');
			} else {
				Error::report(__('An error occurred, check the form'));
			}
		} else {
			\${$singular} = ActiveRecord::get('$model')->findByPK(\$id);
			\$this->set('{$singular}', \${$singular});
			\$this->setData(\${$singular});
		}
	}


EOF;

		# /admin/delete/{id}
		$functions .= <<<EOF
	public function admin_delete(\$ids) {
		\$this->render = false;
		\$ids = explode(',', urldecode(\$ids));
		foreach (\$ids as \$id) {
			ActiveRecord::get('{$model}')->delete(\$id);
		}
		Error::flash('/admin/{$url}', __(':model deleted', array('model' => '{$model}')), 'notify');
	}


EOF;
		}
		
		self::controller(array($controller), $functions);
	}

	public static function controller($args, $functions = null) {
		$name = Inflector::camelize($args[0]);
		$controller = array(
			'name' => $name,
			'functions' => $functions ? $functions : <<<EOF
	public function index() {
	
	}
EOF
		);

		$controller_file = CONTROLLERS.DS.strtolower($controller['name']).'_controller.php';
		if (file_exists($controller_file))
			$confirm = self::$_con->read('The controller exists, overwrite?', array('y', 'n'), 'n');
		else $confirm = 'y';

		if (strtolower($confirm) == 'y') {
			$controller_content = Skels::parse('controller', $controller);
			file_put_contents($controller_file, $controller_content);
			self::$_con->write('Controller '.$controller['name'].' created');
		}
	}

	public static function model($args) {
		$name = Inflector::classify($args[0]);
		$model = array(
			'name' => $name,
			'table' => Inflector::tableize($name),
			'fields' => '$id, '
		);

		$fields = array_slice($args, 1);
		$sql = "CREATE TABLE IF NOT EXISTS `".$model['table']."` (\r\n";
		$sql .= "`id` int(11) auto_increment primary key,\r\n";
		foreach ($fields as $k => $f) {
			$field = explode(':', $f);
			$n = $field[0];
			if (preg_match('/\@(.*)/', $n)) {
				$n = str_replace('@', '', $n).'_id';
				$field[1] = 'int';
			}
			array_shift($field);
			if ($field) {
				$t = $field[0];
				array_shift($field);
				if ($field) {
					$t .= "({$field[0]})";
				} else {
					if ($t == 'bool') {
						$t = 'tinyint';
					} else if ($t == 'string') {
						$t = 'varchar(255)';
					} else if ($t == 'int') {
						$t = 'int(11)';
					}
				}
			} else if (preg_match('/_at$/', $n)) {
				$t = 'datetime';
			} else
				$t = 'varchar(255)';

			$c = $k < count($fields)-1 ? ',' : '';

			$sql .= "`{$n}` {$t}{$c}\r\n";

			$model['fields'] .= '$'.$n.', ';
		}
		$model['fields'] = substr($model['fields'], 0, -2);
		$sql .= ');';
		self::$_con->box('Database structure');
		self::$_con->write($sql);

		$sql_file = ROOT.DS.'database'.DS.$model['table'].'.sql';
		if (file_exists($sql_file))
			$confirm = self::$_con->read('The sql for table exists, overwrite?', array('y', 'n'), 'n');
		else $confirm = 'y';

		if (strtolower($confirm) == 'y') {
			file_put_contents($sql_file, $sql);
		} else {
			exit();
		}

		$model_file = MODELS.DS.strtolower(Inflector::singularize(Inflector::tableize($model['name']))).'.php';
		if (file_exists($model_file))
			$confirm = self::$_con->read('The model exists, overwrite?', array('y', 'n'), 'n');
		else $confirm = 'y';

		if (strtolower($confirm) == 'y') {
			$model_content = Skels::parse('model', $model);
			file_put_contents($model_file, $model_content);
			self::$_con->write('Model '.$model['name'].' created');
		} else {
			exit();
		}
	}
}
