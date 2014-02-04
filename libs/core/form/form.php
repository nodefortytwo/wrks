<?php

function form_routes(){
	$routes = array();

	$routes['tags'] = new FormTagsRoute();

	return $routes;	
}

function form_init(){
	Template::addJs('js/select2.min.js');
	Template::addCss('css/select2.css');
}