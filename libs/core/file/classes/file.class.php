<?php
class File extends MongoBase{
	protected $collection = 'file', $obj_id = true;

	public static function create($contents, $ext = '', $name = '', $interface = "LocalFileInterface"){

		//allow files that are the same with different names and extensions
		$hash = md5(serialize(func_get_args()));

		if($file = self::hashExists($hash)){
			return $file;
		}
		//the new file is unique;

		$file = new File();
		$file['hash'] = $hash;
		$file['ext'] = $ext;
		$file['name'] = $name;
		$file['created_at'] = new MongoDate();
		$file['size'] = strlen($contents) * 8;
		$file['interface'] = $interface;
		$file['interface_data'] = null;
		$file->save();

		$interface = new $interface($file);
		$file = $interface->create($contents);
		$file->save();
		
		return $file;

	}

	public static function hashExists($hash){
		$file = new File();
		$file->loadFromHash($hash);
		if($file->exists){
			return $file;
		}else{
			return false;
		}
	}


	public function loadFromHash($hash){
		$record = mdb()->{$this->collection}->findOne(array('hash' => $hash));
		if($record){
			$this->loadFromRecord($record);
		}else{
			$this->exists = false;
		}
		return $this;
	}


	public function getUrl(){
		return $this->url = $this['url'];
	}

	public function exists($ret_obj = true){
		//exists in the database?
		$exists_db = parent::exists(true);
		if($exists_db){
			$exists_db = new File($exists_db);
			//check if our file interface agrees
			$interface = new $exists_db['interface']($exists_db);
			if($interface->exists()){
				//honor the original exists return values
				if($ret_obj){
					return $exists_db;
				}else{
					return 1;
				}
			}else{

				//nope the actually file doesn't exist so delete the db record and return false;
				$exists_db->delete();
				return false;
			}
		}else{
			return false;
		}
	}

}