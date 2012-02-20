<?php
/**
 * @package App
 */
class HomeController extends Controller
{
	function index()
	{
		$user = ActiveRecord::get('User')->find(array('fetch' => ActiveRecord::FETCH_ONE));
		echo $user->toJson(array('Rol'));

		ActiveRecord::get('User')->create(array('name'=>'test'))->save();
	}
}