<?php
class UserUpdateLocationRoute extends Route{
	function render(){
		$user = User::currentUser();
		$user['location'] = $_POST['location'];
		$user['location.lat'] = (float) $user['location.lat'];
		$user['location.lng'] = (float) $user['location.lng'];
		$user['location.accuracy'] = (int) $user['location.accuracy'];
		$user->save();
		$this->output = array('message' => "Updated Location");
	}
}