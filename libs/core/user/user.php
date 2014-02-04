<?php
//hook_routes
function user_routes(){
	$routes = array();
	$routes['users'] = new UserRouteUsers();
	$routes['users/:user_id'] = new UserRouteUsers();
	$routes['user'] = new UserUserRoute();
	$routes['user/current'] = new UserCurrentRoute();
	$routes['user/login'] = new UserLoginRoute();
	$routes['login'] = new UserLoginRoute();
	$routes['user/register'] = new UserRegisterRoute();
	$routes['user/update/location'] = new UserUpdateLocationRoute();
	return $routes;
}

function user_init(){
}

function user_nav(){

	if($user = User::currentUser()){
		return array(array('title' => 'Hi, '.$user['name'], 'path' => '/user/'));
	}else{
		return array(array('title' => 'Login / Register', 'path' => '/login/'));
	}

}