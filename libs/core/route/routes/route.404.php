<?php
class RouteRoute404 extends Route{
	function render(){
		$this->status = 404;
		$this->output = array('message' => 'Page Not Found');
	}
}