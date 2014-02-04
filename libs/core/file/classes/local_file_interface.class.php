<?php
class localFileInterface implements iFileInterface{

	function __construct($file){
		$this->base_path = Config::get('files', 'public');
		$this->file = $file;
	}

	private function fileName(){
		if(isset($this->file['name']) && !empty($this->file['name'])){
			$this->filename = $this->file['name']; 
		}else{
			$this->filename = (string) $this->file['_id'];
		}

		if(isset($this->file['ext']) && !empty($this->file['ext'])){
			$this->filename .= '.' . $this->file['ext'];
		}
		return $this->filename;
	}

	public function create($contents){

		$path = System::$cwd . '/' . $this->base_path . '/' . $this->filename();

		file_put_contents($path, $contents);
		$this->file['url'] = $this->url();
		$this->file['interface_data'] = array(
				'path' => $path
			);

		return $this->file;

	}
	public function save(){

	}
	public function load(){

	}
	public function stream(){

	}
	public function delete(){

	}
	public function copy($dest){

	}
	public function rename($name){

	}
	public function exists(){
		return file_exists($this->file['interface_data.path']);
	}
	public function url(){
		return '/' . $this->base_path . '/' . $this->filename();
	}

}