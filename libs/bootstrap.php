<?php
if (defined('STDIN')) {
	if(isset($argv[1])){
    	$_SERVER['HTTP_HOST'] = $argv[1];
	}
	if(isset($argv[2])){	
    	$_SERVER['REQUEST_URI'] = $argv[2];
	}

	if(isset($argv[3])){
		$_SERVER['REQUEST_METHOD'] = strtoupper($argv);
	}else{
		$_SERVER['REQUEST_METHOD'] = 'GET';
	}

}	
require 'misc.php';
require 'config.class.php';
require 'system.class.php';