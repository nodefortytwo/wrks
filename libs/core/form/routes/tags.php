<?php
class FormTagsRoute extends Route{

	public function render(){
		$tags = array();
		$res = mdb()->tags->find(array())->sort(array('name' => 1));
		foreach($res as $t){
			$tags[] = $t['name'];
		}

		$this->output = array(
				'tags' => $tags
			);
	}

}

function add_tag($tag){
	$id = new MongoId($tag);
	$rec = array(
			'_id' => $tag,
			'name' => $tag
		);
	mdb()->tags->update(array("_id"=>$tag), $rec, array('upsert' => true));
}