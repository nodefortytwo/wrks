<?php
class Collection implements Iterator{
    protected $collection, $default_cols = array('ID' => '_id'), $class_name = null, $position = 0, $run = false;
    public $cursor;
    public $search = array();

    function __construct($search = null, $limit = null, $sort = null, $skip = null, $projection = null, $hint = null) {
        if(!$this->collection || !$this->class_name){
            throw new Exception('collection and class_name must be specified');
        }
        
        $this->search = $search;
        $this->limit = $limit;
        $this->sort = $sort;
        $this->projection = $projection;
        $this->skip = $skip;
        $this->hint = $hint;
        $this->find();
    }
    
    public static function query($search = null, $limit = null, $sort = null, $skip = null, $projection = null, $hint = null){
        $classname = get_called_class();
        return new $classname($search, $limit, $sort, $skip, $projection, $hint);
    }

    public function search($search){
        $this->search = $search;
        return $this;
    }

    public function limit($limit){
        $this->limit = $limit;
        return $this;
    }

    public function sort($sort){
        $this->sort = $sort;
        return $this;
    }

    public function skip($skip){
        $this->skip = $skip;
        return $this;
    }
    public function projection($projection){
        $this->projection = $projection;
        return $this;
    }
    public function hint($hint){
        $this->hint = $hint;
        return $this;
    }

    function __get($var) {
        if (method_exists($this, 'get' . $var)) {
            return call_user_func_array(array(
                $this,
                'get' . $var
            ), array());
        }
    }
    
    function find() {
        if($this->run){
            return;
        }
        //unset some counts
        unset($this->cnt);
        unset($this->cnt_total);
        unset($this->pages);


        //don't run a null search.
        if (!is_array($this->search)) {
            return;
        }
        if($this->projection){
            $this->cursor = mdb()->{$this->collection}->find($this->search, $this->projection);
        }else{
            $this->cursor = mdb()->{$this->collection}->find($this->search);
        }

        if($this->hint){
            $this->cursor->hint($this->hint);
        }

        if($this->sort){
            $this->cursor->sort($this->sort);
        }
        if($this->skip > 0){
            $this->cursor->skip($this->skip);
        }
        if($this->limit){
            $this->cursor->limit($this->limit);
        }

        $this->run = true;
        return $this;
    }

    function render($style = 'table', $args = array()){
        $func = 'render' . ucfirst($style);
        if(method_exists($this, $func)){
            return call_user_func(array($this, $func), $args);
        }else{
            echo $func . ' not supported';
        }
    }
    
    function renderTable($args = array()) {
        list($headers, $rows) = $this->getTableData($args);
        $class = $this->class_name;
        foreach($rows as $key=>$row){
            foreach($row as $ckey=>$col){
                if(empty($ckey)){continue;}
                switch($class::$fields[$ckey]['type']){
                    case 'currency':
                        $row[$ckey] = $class::$fields[$ckey]['symbol']. number_format($col, 2);
                        break;
                    case 'date':
                        $row[$ckey] = Render::date($col, $class::$fields[$ckey]['format']);
                        break;
                }
            }
            $rows[$key] = $row;
        }

        return Render::table($headers, $rows, 'table-' . $class);
    }
    
    function getTableData($args = array()){
        if (!isset($args['cols'])) {
            $args['cols'] = $this->default_cols;
        }
        $class = $this->class_name;
        if(isset($class::$fields)){
            $args['cols'] = array();
            $f = $class::$fields;
            foreach($f as $field=>$props){
                foreach($props as $prop=>$value){
                    if($prop == 'title'){
                        $args['cols'][$value] = $field;
                    }
                }
            }
        }


        $headers = array_keys($args['cols']);
        if(isset($args['include_actions']) && $args['include_actions']){
            $headers[] ='';
        }
        $rows = array();
        foreach ($this as $result) {
            $row = $result->toArray($args['cols']);
            if(isset($args['include_actions']) && $args['include_actions']){

                if(isset($class::$actions)){
                    $actions = '';
                    foreach($class::$actions as $title=>$action){
                        $url = str_replace(':id', $result['_id'], $action['url']);
                        $actions .= Render::Link($title, $url, 'btn');
                    }
                }

                $row[] = $actions;
            }
            array_shift($row);
            $rows[(string)$result['_id']] = $row;
        }
        
        if (isset($args['sort'])) {
            $index = array_search($args['sort'], array_values($args['cols']));
            $col = array();
            foreach ($rows as $row) {
                $col[] = $row[$index];
            }
            array_multisort($col, SORT_DESC, $rows);
        }
        array_shift($headers);
        return array($headers, $rows);
    }

    function fromObjects($objects) {
        //this is an experiment, a standard array should behave in almost the same way a mongo cursor so this should work.
        $this->cursor = new PseudoCursor($objects);
    }
    
    function getIds(){
        $this->ids = array();    
        foreach($this as $res){
            $this->ids[] = $res['_id'];
        }
        return $this->ids;
    }
    
    function getCnt(){
        $this->cnt = $this->cursor->count(true);
        return $this->cnt;
    }

    function getCntTotal(){
        $this->cnt_total =  $this->cursor->count(false);
        return $this->cnt_total;
    }

    function getPages(){
        if(!is_null($this->limit)){
            return $this->pages = ceil($this->cnt_total / $this->limit);
        }else{
            return 1;
        }
    }

    //iterator stuff
    function rewind() {
        if(!$this->run){
            $this->find();
        }
        $this->position = 0;
        $this->cursor->reset();
        $this->next();
    }

    function current() {
        //die('first result');
        if(is_object($this->cursor->current())){
            return $this->cursor->current();
        }
        $classname = $this->class_name;
        return new $classname($this->cursor->current());
    }

    function key() {
        return $this->cursor->key();
    }

    function next() {
       $this->cursor->next();
       $this->position++;
    }

    function valid() {
        return $this->cursor->valid();
    }
}

//emulate mogo cursor behaviour on a standard array, associated arrays should work too but not tested.
class PseudoCursor{
    private $data, $position = -1, $keys = array();
    function __construct($data){
        $this->data = $data;
        $this->keys = array_keys($this->data);
    }

    function rewind() {
        $this->position = -1;
        //reset($this->data);
    }

    function reset() {
        $this->rewind();
    }

    function current() {
        return $this->data[$this->key()];
    }

    function key() {
        if(isset($this->keys[$this->position])){
            return $this->keys[$this->position];
        }else{
            return null;
        }
    }

    function next() {
        $this->position++;
    }

    function valid() {
        if(!is_null($this->key())){
            return isset($this->data[$this->key()]);
        }else{
            return false;
        }
    }

    function count(){
        return count($this->data);
    }

}

