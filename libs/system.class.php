<?php
class System{
	static $cli = false;
	static $active_route = null;
	static $core_modules = array();
	static $modules = array();
	static $installed_modules = array();
	static $routes = array();
	static $args = array();
	static $cwd = '';

	public function init(){
		self::$cwd = getcwd();
		ob_start(); 
  		ob_implicit_flush(); 
		self::isCli();
		self::getCoreModules();
		self::getModules();
		self::includeModules(self::$core_modules);
		self::includeModules(self::$modules);
		self::execHook('init');
		self::findActiveRoute();
		self::executeActiveRoute();
		ob_end_flush();
	}


	private function isCli(){
		
		if (defined('STDIN')) {
			if(isset($argv[1])){
		    	$_SERVER['HTTP_HOST'] = $argv[1];
			}
			if(isset($argv[2])){	
		    	$_SERVER['REQUEST_URI'] = $argv[2];
			}
		    self::$cli = true;
		}else{
			self::$cli = false;
		}
	}

	private static function getCoreModules(){
		$core_path = './libs/core/';
		$modules = glob($core_path.'*');
		foreach($modules as $module){
			$mname = str_replace($core_path, '', $module);
			self::$core_modules[$mname] = o2a(json_decode(file_get_contents($module.'/'.$mname.'.config.json')));
			self::$core_modules[$mname]['path'] = $module;
		}

	}

	private static function getModules(){
		$core_path = './libs/modules/';
		$modules = glob($core_path.'*');
		foreach($modules as $module){
			$mname = str_replace($core_path, '', $module);
			self::$modules[$mname] = o2a(json_decode(file_get_contents($module.'/'.$mname.'.config.json')));

			if(is_null(self::$modules[$mname])){
				throw new Exception($mname . ' json config is invalid');
			}
			self::$modules[$mname]['path'] = $module;
			
		}
	}

	private static function includeModules(&$modules){
		$module_copy = $modules;
		$amodules = array_keys($modules);
		$included_files = array();
		$weight = 0;
		while(count($modules) > 0){
			foreach($modules as $key=>$module){
				if(isset($module['dependencies'])){
					$dmet = true;
					foreach($module['dependencies'] as $dependency){
						if(!in_array($dependency, $amodules)){
							var_Dump($module, $dependency);
							throw new Exception($dependency . ' is required by ' . $module['name'] . ' but was not found!');
						}
						if(!in_array($dependency, self::$installed_modules)){
							$dmet = false;
						}
					}
					//if we are missing dependencies skip module
					if(!$dmet){
						continue;
					}
				}

				//dependencies met
				if(isset($module['files'])){
					foreach($module['files'] as $file){

						if($file[strlen($file)-1] == '*'){
							//require the whole folder
							$files = glob($module['path'] . '/' . $file);
							foreach($files as $f){
								require_once $f;
								$included_files[] = $f;
							}
						}else{
							require_once $module['path'] . '/' . $file;
							$included_files[] = $module['path'] . '/' . $file;
						}
						
					}
				}
				self::$installed_modules[] = $key;
				unset($modules[$key]);
				$weight++;
				$module_copy[$key]['weight'] = $weight;

			}
		}
		$modules = $module_copy;
	}

	private static function findActiveRoute(){
		self::$routes = self::execHook('routes');
		$routes = array();
		foreach(self::$routes as $m){
			foreach($m as $k=> $r){
				$routes[$k] = $r;
			}
		}
		self::$routes = $routes;

		// Extract the path from REQUEST_URI.
        $request_path = strtok($_SERVER['REQUEST_URI'], '?');

        if(!self::$cli){
            $base_path_len = strlen(rtrim(dirname($_SERVER['SCRIPT_NAME']), '\/'));
            $path = substr(urldecode($request_path), $base_path_len + 1);
            if ($path == basename($_SERVER['PHP_SELF'])) {
                $path = '';
            }
            if (empty($path)) {
                $path = $request_path;
            }
        }else{
            $path = $request_path;
        }
        //Find any args
        $split = explode('~', $path);
        $path = rtrim($split[0], "/");
        $args = array();
        if (!empty($split[1])) {
            $split[1] = trim($split[1], "/");
            $args = explode('/', $split[1]);
        }
        
        if(empty($path)){
        	$path = 'index';
        }


        //perfect matches will always be faster than regex based matching
        if(array_key_exists($path, self::$routes)){
        	self::$active_route = self::$routes[$path];
        	self::$active_route->params = $args;
        	self::$active_route->params = array_merge(self::$active_route->params, $_POST);
			self::$active_route->params = array_merge(self::$active_route->params, $_GET);
        }else{
	        foreach (self::$routes as $route_path=>$route) {

				$pattern = getRegex($route_path);
				if (!preg_match("@^".$pattern."*$@i", $path, $matches)) continue;
				self::$active_route = $route;
				self::$active_route->params = $args;
				self::$active_route->params = array_merge(self::$active_route->params, $_POST);
				self::$active_route->params = array_merge(self::$active_route->params, $_GET);
				if (preg_match_all("/:([\w-]+)/", $route_path, $argument_keys)) {

	                // grab array with matches
	                $argument_keys = $argument_keys[1];

	                // loop trough parameter names, store matching value in $params array
	                foreach ($argument_keys as $key => $name) {
	                    if (isset($matches[$key + 1]))
	                        $params[$name] = $matches[$key + 1];
	                }
	                self::$active_route->params = array_merge(self::$active_route->params, $params);
	            }

	            
	        }
    	}

        if(is_null(self::$active_route)){
        	self::$active_route = self::$routes['404'];
        }
	}

	private static function executeActiveRoute(){
		self::$active_route->args = self::$args;
		self::$active_route->exec();
	}

	public static function execHook($hook){
		$ret = array();
		foreach(self::$installed_modules as $module){
			if(function_exists($module . '_' . $hook)){
				$ret[$module] = call_user_func($module . '_' . $hook);
			}
		}
		return $ret;
	}


}System::init();



function getRegex($url) {
		return preg_replace_callback("/:(\w+)/", 'substituteFilter', $url);
	}

function substituteFilter($matches) {
	return "([A-Za-z_0-9+ &-]+)";
	return "([\w-]+)";
}
