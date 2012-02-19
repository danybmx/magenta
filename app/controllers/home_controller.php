<?php

class HomeController extends Controller
{
	function index()
	{
		$users = ActiveRecord::get('User')->find();
	}
}