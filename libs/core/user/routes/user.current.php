<?php
class UserCurrentRoute extends Route{

	function render(){
		$at = new TwitterAccessToken();
		if(is_array(session()->access_token)){
			$this->output = array(
					'user' => true
				);
		}else{
			$this->output = array(
					'user' => false
				);
		}	
	}

}