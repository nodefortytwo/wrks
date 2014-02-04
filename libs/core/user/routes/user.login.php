<?php
class UserLoginRoute extends Route{

	function validate(){
		if($this->method == 'POST'){
			if(isset($this->args[0])){
				$auth_method = User::getAuthMethod($this->args[0]);
			}else{
				$auth_method = User::getAuthMethod();
			}
			$auth_method->loginFormSubmit();
		}

		if(User::currentUser()){
			message('You are already logged in!');
			redirect('/user/');
		}


		return true;
	}

	function render(){
		$page = new Template();
		$page->addTemplate('templates/login.html');
		$page->addVariable('register_form', User::registerForm());
		$page->addVariable('login_form', User::loginForm());
		$this->output = $page->render();
	}

}