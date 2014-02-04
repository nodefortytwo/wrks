<?php
class Model extends MongoBase{
	protected $obj_id = true;
	static $type_defaults = array(
			'text' => '',
			'id' => 'MongoId()',
			'date' => 'MongoDate()',
			'int' => 0,
			'float' => 0.0,
			'embed_one' => null,
			'embed_many' => array(),
			'reference_one' => null,
			'reference_many' => array(),
			'file' => null,
			'image' => null,
			'array' => null,
			'currency' => null
		);
	protected static $embeded_by = null;
	protected static $referenced_by = null;

	protected static $fields = array(
			'_id' => array('type' => 'id')
		);

	public function fields(){
		$class = get_class($this);
		return $class::$fields;
	}

	private function embeded(){
		$class = get_class($this);
		if($class::$embeded_by){
			return true;
		}else{
			return false;
		}
	}

	public function save(){
		if(isset($this->_id) && !isset($this['_id'])){
			$this['_id'] = $this->_id;
		}
		//cache the change log before out tweakery
		$cl = $this->cl;
		foreach($this->fields() as $key=>$field){

			if(!isset($this[$key]) || is_null($this[$key])){
				$method_name = 'field' . $field['type'] . 'Default';
				if(method_exists($this, $method_name)){
					$this[$key] = $this->$method_name();
				}else{
					$this[$key] = self::$type_defaults[$field['type']];
				}
			}else{
				switch($field['type']){
					case 'reference_one':
						if(!is_object($this[$key])){
							continue;
						}
						if(!is_object($this[$key]) || (is_object($this[$key]) && get_class($this[$key]) != $field['reference_class'])){
							throw new exception($key . ' Must be ' . $field['reference_class'] . ' Object but is ' . get_class($this[$key]));
						}

						if(!$this[$key]->_id){
							$this[$key]->save();
						}

						$this[$key] = $this[$key]->getReference();
						break;	
					case 'embed_one':
						$this[$key] = $this[$key]->save()->data;
						break;
					case 'embed_many':
						$elems = $this[$key];

						foreach($elems as $ekey=>$elem){
							$elem->save();
							$elems[$ekey] = $elem->data;
						}
						$this[$key] = $elems;
						break;
				}


			}
		}
		
		if(!is_null($this['_id'])){
			$this->_id = $this['_id'];
		}
		//reinstate the change log, ignoring the things that were converted or changed as a result of the save preporocessing
		$this->cl = $cl;
		//only pass through to mongo if the current document is not embeded.
		if(!$this->embeded()){
			parent::save();	
		}
		return $this;
	}

	public function __construct($rec = null, $load_entities = true){
		if(!$this->embeded()){
			parent::__construct($rec);
		}else{
			$this->data = $rec;
		}
		if(!$load_entities || !$this->exists){
			return;
		}
		foreach($this->fields() as $key=>$field){
			switch ($field['type']){
				case 'reference_one':
					$f = new $field['reference_class']($this[$key]['_id']);
					$f->parent = $this;
					$f->location = $key;
					$this[$key] = $f;
					break;
				case 'reference_many':
					$elems = $this[$key];
					foreach($elems as $ekey=>$elem){
						$f = new $field['reference_class']($elem['_id']);
						$f->parent = $this;
						$f->key = $ekey;
						$f->location = $key;
						$elems[$ekey] = $f;
					}
					$this[$key] = $elems;
					break;
				case 'embed_many':
					$elems = $this[$key];
					foreach($elems as $ekey=>$elem){
						$f =  new $field['embed_class']($elem);
						$f->parent = $this;
						$f->key = $ekey;
						$f->location = $key;
						$elems[$ekey] = $f;
					}
					$this[$key] = $elems;
					break;
				case 'embed_one':
					$f =  new $field['embed_class']($this[$key]);
					$f->parent = $this;
					$f->location = $key;
					$this[$key] = $f;
					break;
			}
		}
		//reset the change log because the load process hasn't actually changed anything.
		$this->cl = array();
	}

	public function push($field, $document){
		$fields = $this->fields();
		$type = $fields[$field]['type'];

		if($type == 'embed_many' || $type == 'reference_many'){
			if(!is_array($this[$field])){
				$this[$field] = array();
			}
			$array = $this[$field];
			array_push($array, $document);
			$this[$field] = $array;

		}else{
			throw new Exception('push is for reference_many and embed_many type fields only');
		}

	}

	//returns a reference array
	private function getReference(){

		return array(
				'_id' => $this->_id,
				'type' => get_class($this)
			);
	}

	//function based defaults
	private function fieldIdDefault(){
		return new MongoId();
	}
	private function fieldDateDefault(){
		return new MongoDate(0);
	}
}

/*class Article extends Model{
	protected $collection = 'article';
	protected static $fields = array(
			'_id' => array('type' => 'id'),
			'title' => array('type' => 'text'),
			'author' => array('type' => 'reference_one', 'reference_class' => 'Author'),
			'last_comment' => array('type' => 'embed_one', 'embed_class' => 'Comment'),
			'comments' => array('type' => 'embed_many', 'embed_class' => 'Comment'),
			'comment_count' => array('type' => 'int')
		);

	function save(){
		if(count($this['comments']) > 0){
			$this['last_comment'] = $this['comments.'.(count($this['comments']) - 1)];			
		}

		$this['comment_count'] = count($this['comments']);

		parent::save();
	}

}

class Comment extends Model{
	protected static $fields = array(
			'title' => array('type' => 'text'),
			'author' => array('type' => 'reference_one', 'reference_class' => 'Author')
		);
	protected static $embeded_by = array('Article');

}

class Author extends Model{
	protected $collection = 'author';
	protected static $fields = array(
			'_id' => array('type' => 'id'),
			'name' => array('type' => 'text')
		);
	protected static $referenced_by = array('Article', 'Comment');
}
*/