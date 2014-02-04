<?php
interface iAuthMethod{
	
	public function login();
	public function logout();
	public function currentUser();
	public function registerForm();
	public function registerFormSubmit();
	public function loginForm();
	public function loginFormSubmit();
}