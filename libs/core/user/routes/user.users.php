<?php
class UserRouteUsers extends Route{
	function __construct(){
		parent::__construct();
		//set some default params;
		$this->params = array(
				'user_id' => null
			);
	}

	function render(){

		if(!is_null($this->params['user_id'])){
			if($this->params['user_id'] == 'current'){
				$this->params['user_id'] = null;
			}

			$user = new User($this->params['user_id']);

			$this->output = array('user' => $user->toArray());


		}else{

			$users = new UserCollection(array());
			$this->output = array('users' => $users->toArray());

		}
	}
}

