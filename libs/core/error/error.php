<?php

function error_init(){
	set_error_handler('error_error');
	set_exception_handler('error_exception');
}

function error_routes(){
	$routes = array();

	$routes['error/logs'] = array('callback' => 'error_logs');

	return $routes;
}

function error_exception($e){
	try{
		$error = new Error();

		$eid = (string) $error->toss($e);

		//take them to the error 500 page.
		if(System::$cli){
			echo $error;
			die();
		}
		$route = System::$routes[500];
		$route->params[] = $eid;
		$route->exec();
		die();

		redirect('/500?eid=' . $eid);
	} catch (Exception $e) {	
        print get_class($e)." thrown within the exception handler. Message: ".$e->getMessage()." on line ".$e->getLine();
    	echo '<pre>';
    	var_dump($e);
    	echo '</pre>';
    }
}

function error_error($errno, $errstr, $errfile, $errline){

	//convert errors into exceptions, we run a clean ship and want every error to crash
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    
	return true;
}

function error_logs(){
	$errors = new ErrorCollection(array(), null,  array('time' => -1));
	$page = new template();
	$page->c($errors->render());
	return $page->render();
}
