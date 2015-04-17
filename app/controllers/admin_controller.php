<?php
/**
 * Controller for Admin
 *
 * @package App
 * @author danybmx <dany@dpstudios.es>
 */
class AdminController extends Controller
{
	public function index() {
	}

	// Just a comment for test
	public function login() {
		$this->layout = 'login';
		if ($this->data) {
			if(UserComponent::login($this->data['username'], $this->data['password'])) {
				Error::flash('/admin', __('Welcome :user', array('user' => $this->data['username'])), 'notify');
			} else {
				Error::report(__('Username or password are incorrect'), 'warning');
			}
		}
	}

	public function logout() {
		$this->render = false;
		UserComponent::logout();
		Error::flash('/admin/login', __('You are logged off'), 'notify', 0);
	}
}
