<?php
class Config{

	static $data = array();

	public function init(){
		$path = getcwd().'/settings/' . $_SERVER['HTTP_HOST'] . '.settings.json';
		if(!file_exists($path)){
			$path = getcwd().'/libs/settings/' . 'default' . '.settings.json';
		}
		$config = o2a(json_decode(file_get_contents($path)));

		foreach($config as $name=>$val){
			self::$data[strtoupper($name)] = $val;
		}

	}

	public static function get($key, $default = null){
		$key = strtoupper($key);
		if(!is_null(self::getVal($key))){
			return self::getVal($key);
		}else{
			if(!is_null($default)){
				return $default;	
			}

			throw new Exception('Config ' . $key . ' not set and no default provided');
		}
	}

	public static function getVal($key){
		if(isset(self::$data[$key])){
			return self::$data[$key];
		}elseif(defined($key)){
			return constant($key);
		}elseif(isset($_ENV[$key])){
			return $_ENV[$key];
		}

		return null;
	}

}Config::init();