<?php
class User extends Model{
	public $collection = 'user', $obj_id = true;
	protected static $auth_methods = array(), $auth_method;

	public static function registerAuthMethod($class, $name){
		if(!in_array('iAuthMethod', class_implements($class))){
			throw new Exception(get_class($class) . ' is not a valid Auth Method because it doesn\'t implement iAuthMethod');
		}

		self::$auth_methods[$name] = $class;
 	}

 	public static function getAuthMethod($method_name = null){

 		if(isset($method_name)){
 			if(in_array($method_name, array_keys(self::$auth_methods))){
 				return self::$auth_method = self::$auth_methods[$method_name];
 			}else{
 				throw new Exception($method_name . ' is not a valid auth method defined');
 			}
 		}

 		if(!empty(self::$auth_method)){
 			return self::$auth_method;
 		}else{
 			if(in_array(get('auth_method', 'simple'), array_keys(self::$auth_methods))){
 				return self::$auth_method = self::$auth_methods[get('auth_method', 'simple')];
 			}else{
 				throw new Exception('No valid auth method defined');
 			}
 		}
 	}

 	public static function registerForm(){
 		return self::getAuthMethod()->registerForm();
 	}

 	public static function loginForm(){
 		return self::getAuthMethod()->loginForm();
 	}

 	public static function loginFormSubmit(){
 		return self::getAuthMethod()->loginForm();
 	}

 	public static function currentUser(){

 		if(!is_null(session()->user_id)){

 			return new User(session()->user_id);
 		}else{
 			return self::getAuthMethod()->currentUser();
 		}
 	}

 	public function renderWidgetSmall(){

 		return $this['name'];

 	}

 	public function renderWidget(){
 		$content  = new Partial('/templates/user.widget.html');
 		$content->addVariable('name', $this['name']);
 		return $content->render();
 	}
}

class UserCollection extends Collection{
	public $collection = "user", $class_name = 'User';
}