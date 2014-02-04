<?php
class UserRegisterRoute extends Route{

	function validate(){

		if($this->method == 'POST'){
			if(isset($this->args[0])){
				$auth_method = User::getAuthMethod($this->args[0]);
			}else{
				$auth_method = User::getAuthMethod();
			}
			$auth_method->registerFormSubmit();
		}
		return true;
	}

}