<?php
//all routes extend this
class Route{
	public $output = '', $status = 200, $params = array(), $type = 'application/json';
	
	function __construct(){
		$this->method = $_SERVER['REQUEST_METHOD'];
	}

	function exec(){
		if($this->auth()){
			if($this->validate()){
				$this->render();
				$this->postRender();
				echo $this->output;
			}
		}
	}

	function auth(){
		return true;//by default allow access to every route
	}

	function validate(){
		return true;//by default validate each route
	}

	function render(){
		$this->status = 404;
		$this->output = array('message' => 'please replace this routes render function');
	}

	function postRender(){

		//Out put the correct headers;
		switch($this->status){
			case 403:
				header("HTTP/1.0 403 Access Denied");
				break;
			case 404:
				header("HTTP/1.0 404 Not Found");
				break;
			case 500:
				header("HTTP/1.0 500 Server Error");
				break;
			default:
				header("HTTP/1.0 200 OK");
		}

		//if the output isn't printable convert to json
		if(!is_scalar($this->output)){
			header('Content-type: ' . $this->type);
			$this->output = json_encode($this->output);
		}
	
	}

}