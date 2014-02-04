<?php
class RouteRoute500 extends Route{
	function render(){
		$error = new Error($this->params[0]);
		
		$stack_trace = explode("\n", trim($error->getExceptionTraceAsString()));


		$this->status = 500;
		$this->output = array(
			'status' => 500, 
			'message' => 'Code Error', 
			'error' => (string) $error,
			'stack_trace' => $stack_trace);
	}
}