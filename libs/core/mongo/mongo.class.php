<?php

class MongoBase implements arrayaccess {
    public $_id, $rt_sync = false, $obj_type = 'MongoBase';
    protected $cl = array(), $data = array(), $obj_id = false, $get_cache = array();
    
    public function __construct($rec = null) {
        if(is_null($rec)){
            return;
        }

        if(is_string($rec) && $this->obj_id){
            $rec = new MongoId($rec);
        }

        if (is_array($rec)) {
            if(isset($rec['_id'])){
                $this->loadFromRecord($rec);
            }else{
                foreach($rec as $key => $val){
                    $this[$key] = $val;
                }
            }
        } elseif (is_object($rec)) {
            if (get_class($rec) == 'MongoId') {
                $this->_id = $rec;
                $this->loadFromId();
            } else {
                $this->loadFromObject($rec);
            }
        } elseif (!empty($rec)) {
            $this->_id = (string) $rec;
            $this->loadFromId();
        }
    }

    function __get($var) {
        $method_name = 'get' . ucfirst($var);
        if (method_exists($this, $method_name)) {
            return $this->$method_name();
        }
    }

    function __toString() {
        return json_encode($this->data);
    }

    protected function loadFromId() {
        if ($exists = $this->exists()) {
            $this->data = $exists;
            if (method_exists($this, 'load_postprocess')) {
                call_user_func(array(
                    $this,
                    'load_postprocess'
                ));
            }
        }

    }
    //just a proxy for loadFromId
    public function refresh(){
        $this->loadFromId();
    }

    protected function loadFromRecord($rec) {

        $this->_id = $rec['_id'];
        $this->data = $rec;
        if (method_exists($this, 'load_postprocess')) {
            call_user_func(array(
                $this,
                'load_postprocess'
            ));
        }
    }

    public function exists($ret_obj = true) {
        if (!$this->_id) {
            if(isset($this['_id'])){
                $this->_id = $this['_id'];
            }else{
                $this->exists = false;
                return false;
            }
        }
        $q = array('_id' => $this->_id);
        if ($ret_obj) {
            $rec =  mdb()->{$this->collection}->findOne($q);
        } else {
            $rec =  mdb()->{$this->collection}->count($q);
        }
        if ($rec) {
            $this->exists = true;
            return $rec;
        } else {
            $this->exists = false;
            return false;
        }
    }

    public function getExists() {
        return $this->exists(false);
    }

    public function save() {
        if (!isset($this->collection)) {
            throw new Exception('You must either define a collection or save the parent document');
        }
        if (!$this['_id'] && $this->_id) {
            $this['_id'] = $this->_id;
        }
        if (method_exists($this, 'savePreprocess')) {
            $this->savePreprocess();
        }

        if ($this->exists(false)) {
            //var_dump($this);
                
            $set = array();
            uksort($this->cl,'sort_by_len');
            //var_dump($this->cl);
            foreach($this->cl as $field=>$op){
                if($field == '_id'){
                    continue;
                }
                $skip = false;
                $parts = explode('.', $field);
                $part = '';
                
                foreach($parts as $p){
                    $part = $part . $p;
                    //var_dump($part);
                    if(isset($set[$part]) || isset($unset[$part])){
                        $skip = true;
                    }
                    $part = $part . '.';
                    
                }
                //var_dump($skip);
                if($skip){
                    continue;
                }
                
                if($op){
                    $set[$field] = $this[$field];
                }else{
                    $unset[$field] = '';
                }
            }
            if(!empty($set) || !empty($unset)){
                $update = array();
                if(!empty($set)){$update['$set'] = $set;} 
                if(!empty($unset)){$update['$unset'] = $unset;} 
                if(!empty($update)){
                    mdb()->{$this->collection}->update(array('_id' => $this->_id), $update);
                }
            }
        } else {
            
            try{
            mdb()->{$this->collection}->insert($this->data);
            } catch (Exception $e) {    
                var_dump('You dun fucked up now son',$this->data);
                 die();
            }
            $this->_id = $this['_id'];
            $this->exists = true;
        }
        return $this;
    }

    public function delete() {
        //if ($this->exists(false)) {
            mdb()->{$this->collection}->remove(array('_id' => $this->_id));
        //}
    }

    

    public function offsetSet($offset, $value) {
        //reset the get cache;
        $this->get_cache = array();
        if (is_null($offset)) {
            $this->data = $value;
        } else {
            $offsets = explode('.', $offset);
            $this->data = $this->recursiveSet($this->data, $offsets, $value);
        }
        if(end($offsets) == '[]'){
            $parent = implode('.', array_slice($offsets, 0, count($offsets) - 1));
            $index = count($this[$parent]) - 1;
            $offset = $parent;
        }
         
        $this->cl[$offset] = true;
        if ($this->rt_sync) {
            $this->save();
        }
    }

    public function offsetExists($offset) {
        $offsets = explode('.', $offset);
        $value = $this->recursiveGet($this->data, $offsets);
        return isset($value);
    }

    public function offsetUnset($offset) {
        $this->cl[$offset] = false;
        $offsets = explode('.', $offset);
        $this->data = $this->recursiveSet($this->data, $offsets, null);
        if ($this->rt_sync) {
            $this->save();
        }
    }

    public function offsetGet($offset) {
        if(isset($this->get_cache[$offset])){
            return $this->get_cache[$offset];
        }

        if (is_null($offset)) {
            return $this->data;
        } else {
            $offsets = explode('.', $offset);
            $this->get_cache[$offset] = $this->recursiveGet($this->data, $offsets);
            return $this->get_cache[$offset];
        }
    }

    private function recursiveSet($data, $offset_array, $value, $i = 0) {
        $final = false;
        if (count($offset_array) - 1 == $i) {
            $final = true;
        }
        if ($final) {
            if ($offset_array[$i] == '[]') {
                if (!is_array($data)) {
                    $data = array($value);
                } else {
                    $data[] = $value;
                }
            } else {
                $data[$offset_array[$i]] = $value;
            }
        } else {
            if (!isset($data[$offset_array[$i]])) {
                $data[$offset_array[$i]] = array();
            }
            $data[$offset_array[$i]] = $this->recursiveSet($data[$offset_array[$i]], $offset_array, $value, $i + 1);
        }
        return $data;
    }

    private function recursiveGet($data, $offset_array, $i = 0) {

        $final = false;
        if (count($offset_array) - 1 == $i) {
            $final = true;
        }
        if (isset($data[$offset_array[$i]])) {
            if (is_array($data[$offset_array[$i]]) && !$final) {
                return $this->recursiveGet($data[$offset_array[$i]], $offset_array, $i + 1);
            } elseif (is_object($data[$offset_array[$i]]) && !$final){
                if(isset($data[$offset_array[$i]]->obj_type) && $data[$offset_array[$i]]->obj_type == 'MongoBase'){
                    //this is a MongoBase object so we reconstuct its part of the request, this will trigger it to perform
                    //it's own recursiveGet and return the results, meta innit.
                    $param = implode('.',array_splice($offset_array, $i+1));
                    return $data[$offset_array[$i]][$param];
                }else{
                    throw new Exception('Object found during recrusiveGet which cannot be travered');
                }
            }else {
                if($final){
                    return $data[$offset_array[$i]];
                }else{
                    return null;
                }
            }
        } else {
            return null;
        }
    }

    public function encode() {
        if (method_exists($this, 'encode_preprocess')) {
            call_user_func(array(
                $this,
                'encode_preprocess'
            ));
        }

        return json_encode($this->stripMongoObjects($this->data));
    }

    private function stripMongoObjects($data) {
        if (is_array($data) || is_object($data)) {
            if (is_a($data, 'MongoId')) {
                return (string)$data;
            }
            if (is_a($data, 'MongoDate')) {
                return (string)$data;
            }
            $result = array();
            foreach ($data as $key => $value) {
                $result[$key] = $this->stripMongoObjects($value);
            }
            return $result;
        }
        return $data;
    }
    
    public function toArray($fields = null){
        
        if(isset($this->default_fields) && $fields === null){
            $fields = $this->default_fields;
        }elseif($this->fields()){
            $fields = array();
            $f = $this->fields();
            foreach($f as $field=>$props){
                foreach($props as $prop=>$value){
                    if($prop == 'title'){
                        $fields[] = $field;
                    }
                }
            }
        }elseif($fields === null){
            $fields = array_keys($this->data);
        }


        $ret = array();
        foreach($fields as $field){
            $ret[$field] = $this[$field];
        }
        if(isset($this->actions['view']) && isset($ret['_id'])){
            $ret['_id'] = Render::link($ret['_id'], $this->actions['view']['url'] . $ret['_id']);
        }
        if(array_key_exists('actions', $ret)){
            $ret['actions'] = '<div class="btn-grp">';

            foreach($this->actions as $action){
                if(isset($action['hide']) && $action['hide'] === true){continue;}
                if(!isset($action['class'])){$action['class'] = '';}
                $ret['actions'] .= Render::link($action['label'], $action['url'] . $this['_id'], 'btn ' . $action['class']);
            }
            $ret['actions'] .= '</div>';
        }

        return $ret;
    }

    function render($style = 'table', $args = array()) {
        $mname = 'render'.ucfirst($style);
        if(method_exists($this, $mname)){
            return call_user_func(array($this, $mname), $args);
        }else{
            throw new Exception('Style ' . $style . ' has not been implemented');
        }   
    }
}

