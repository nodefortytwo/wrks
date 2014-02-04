<?php

function route_routes(){
	$routes = array();

	$routes['404'] = new RouteRoute404();
	$routes['500'] = new RouteRoute500();
	//$routes['index'] = new RouteRouteHome();

	return $routes;
}