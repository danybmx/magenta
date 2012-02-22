<?php
/**
 * @package App
 */
class HomeController extends Controller
{
	function index()
	{
		d($user->getErrors());
	}
}