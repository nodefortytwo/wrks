<?php
class Form {
    protected $form = array();
    public $rows = 0, $multipart = false;

    function __construct($array) {

        $this->data = $array;

    }

    public static function elem($array){
        $obj_type = 'FormItem' . ucfirst($array['type']);
        $obj = new $obj_type($array, new Form(array()));
        return $obj->render();
    }

    public function e($array) {
        $obj_type = 'FormItem' . ucfirst($array['type']);
        if($array['type'] == 'File'){
            $this->multipart = true;
        }
        $this->form[] = new $obj_type($array, $this);
    }

    public function row($args = array()) {
        $args['type'] = 'Row';
        $this->e($args);
    }

    public function render() {
        $html = '<form ';

        if (isset($this->data['action'])) {
            $html .= ' action="' . $this->data['action'] . '"';
        }
        if (isset($this->data['class'])) {
            $html .= ' class="' . $this->data['class'] . '"';
        }
        
        if (isset($this->data['id'])) {
            $html .= ' id="' . $this->data['id'] . '"';
        }

        if (isset($this->data['method'])) {
            $html .= ' method="' . $this->data['method'] . '"';
        }
        
        if ($this->multipart){
            $html .= ' enctype="multipart/form-data"';
        }
        
        $html .= '>'. "\n";
        foreach ($this->form as $item) {
            $html .= $item->render();
        }
        //if rows have beeen added then we need to close it off
        if ($this->rows > 0) {
            $html .= '</div>';
        }
        $html .= '</form>';
        //die($html);

        $html = '<div class="row-fluid"><div class="span12">'.$html . '</div></div>';

        return $html;
    }

}

class FormItem implements arrayaccess {

    function __construct($array, $form) {
        $this->data = $array;
        $this->form = $form;
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $offsets = explode('.', $offset);
            $this->data = $this->recursive_set($this->data, $offsets, $value);
        }
    }

    public function offsetExists($offset) {
        $offsets = explode('.', $offset);
        $value = $this->recursive_get($this->data, $offsets);
        return isset($value);
    }

    public function offsetUnset($offset) {
        $offsets = explode('.', $offset);
        $this->data = $this->recursive_set($this->data, $offsets, null);
    }

    public function offsetGet($offset) {
        if (is_null($offset)) {
            return $this->data;
        } else {
            $offsets = explode('.', $offset);
            return $this->recursive_get($this->data, $offsets);
        }
    }

    private function recursive_set($data, $offset_array, $value, $i = 0) {
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
            $data[$offset_array[$i]] = $this->recursive_set($data[$offset_array[$i]], $offset_array, $value, $i + 1);
        }
        return $data;
    }

    private function recursive_get($data, $offset_array, $i = 0) {

        $final = false;
        if (count($offset_array) - 1 == $i) {
            $final = true;
        }
        if (isset($data[$offset_array[$i]])) {
            if (is_array($data[$offset_array[$i]]) && !$final) {
                return $this->recursive_get($data[$offset_array[$i]], $offset_array, $i + 1);
            } else {

                return $data[$offset_array[$i]];
            }
        } else {
            return null;
        }
    }

}

class FormItemText extends FormItem {
    function render() {
        //some required defaults
        if (!isset($this['class'])) {
            $this['class'] = 'span12';
        }
        if (!isset($this['id'])) {
            $this['id'] = rand_str();
        }

        $html = '<div class="' . $this['class'] . '">';
        $append_class  = '';
        if(isset($this['before'])){
            $append_class .= " input-prepend";
        }
        if(isset($this['after'])){
            $append_class .= " input-append";
        }

        if ($this['label']) {
            $html .= '<div class="control-group">';
            $html .= '<label class="control-label" for="' . $this['id'] . '">' . $this['label'] . '</label>';
            $html .= '<div class="controls">';
        }
        
        if(!empty($append_class)){
            $html .= '<div class="'.$append_class.'">';
        }

        if(isset($this['before'])){
            $html .= '<span class="add-on">'.$this['before'].'</span>';
        }

        $html .= '<input type="text" id="' . $this['id'] . '" name="' . $this['id'] . '" placeholder="' . $this['placeholder'] . '" class="span12" value="'.$this['default'].'" />';
        
        if(isset($this['after'])){
            $html .= '<span class="add-on">'.$this['after'].'</span>';
        }

        if(!empty($append_class)){
            $html .= '</div>';
        }

        if(isset($this['help'])){
            $html .= '<span class="help-block">'.$this['help'].'</span>';
        }

        if ($this['label']) {
            $html .= '</div>';
            $html .= '</div>';
        }
        $html .= '</div>';
        return $html;
    }

}


class FormItemPassword extends FormItem {
    function render() {
        //some required defaults
        if (!isset($this['class'])) {
            $this['class'] = 'span12';
        }
        if (!isset($this['id'])) {
            $this['id'] = rand_str();
        }

        $html = '<div class="' . $this['class'] . '">';
        $append_class  = '';
        if(isset($this['before'])){
            $append_class .= " input-prepend";
        }
        if(isset($this['after'])){
            $append_class .= " input-append";
        }

        if ($this['label']) {
            $html .= '<div class="control-group">';
            $html .= '<label class="control-label" for="' . $this['id'] . '">' . $this['label'] . '</label>';
            $html .= '<div class="controls">';
        }
        
        if(!empty($append_class)){
            $html .= '<div class="'.$append_class.'">';
        }

        if(isset($this['before'])){
            $html .= '<span class="add-on">'.$this['before'].'</span>';
        }

        $html .= '<input type="password" id="' . $this['id'] . '" name="' . $this['id'] . '" placeholder="' . $this['placeholder'] . '" class="span12" value="'.$this['default'].'" />';
        
        if(isset($this['after'])){
            $html .= '<span class="add-on">'.$this['after'].'</span>';
        }

        if(!empty($append_class)){
            $html .= '</div>';
        }

        if(isset($this['help'])){
            $html .= '<span class="help-block">'.$this['help'].'</span>';
        }

        if ($this['label']) {
            $html .= '</div>';
            $html .= '</div>';
        }
        $html .= '</div>';
        return $html;
    }

}

class FormItemHidden extends FormItem {
        function render(){
            return '<input type="hidden" id="' . $this['id'] . '" name="' . $this['id'] . '" placeholder="' . $this['placeholder'] . '" class="span12" value="'.$this['default'].'" />';
        }
}

class FormItemTextArea extends FormItem {

    function render() {
        //some required defaults
        if (!isset($this['class'])) {
            $this['class'] = 'span12';
        }
        if (!isset($this['id'])) {
            $this['id'] = rand_str();
        }

        $html = '<div class="' . $this['class'] . '">';
        if ($this['label']) {
            $html .= '<div class="control-group">';
            $html .= '<label class="control-label" for="' . $this['id'] . '">' . $this['label'] . '</label>';
            $html .= '<div class="controls">';
        }
        
        $html .= '<textarea id="' . $this['id'] . '" name="' . $this['id'] . '" rows="' . $this['rows'] . '" placeholder="' . $this['placeholder'] . '" class="span12" style="height:'.$this['height'].'px;">'.$this['value'].'</textarea>';
        if(isset($this['help'])){
            $html .= '<span class="help-block">'.$this['help'].'</span>';
        }
        if ($this['label']) {
            $html .= '</div>'. "\n";
            $html .= '</div>'. "\n";
        }
        $html .= '</div>';
        return $html;
    }

}

class FormItemSelect extends FormItem {
    function render(){
        //some required defaults
        if (!isset($this['class'])) {
            $this['class'] = 'span12';
        }
        if (!isset($this['id'])) {
            $this['id'] = rand_str();
        }

        $html = '<div class="' . $this['class'] . '">';
        if ($this['label']) {
            $html .= '<div class="control-group">';
            $html .= '<label class="control-label" for="' . $this['id'] . '">' . $this['label'] . '</label>';
            $html .= '<div class="controls">';
        }

        
        $html .= '<select id="' . $this['id'] . '" name="' . $this['id'] . '" placeholder="' . $this['placeholder'] . '" class="span12" style="height:'.$this['height'].'px;">';

        foreach($this['options'] as $key=>$value){
            $selected = '';
            if(isset($this['selected']) && $key == $this['selected']){
                $selected = 'selected=selected';
            }
            $html .= '<option value="' . $key . '"'.$selected.'>' . $value . '</option>';
        }

        $html .= '</select>';
        
        if ($this['label']) {
            $html .= '</div>'. "\n";
            $html .= '</div>'. "\n";
        }
        $html .= '</div>';
        return $html;
    }
}

class FormItemRadio extends FormItem {
    function render(){
        //some required defaults
        if (!isset($this['class'])) {
            $this['class'] = 'span12';
        }
        if (!isset($this['id'])) {
            $this['id'] = rand_str();
        }

        $html = '<div class="' . $this['class'] . '">';
        if ($this['label']) {
            $html .= '<div class="control-group">';
            $html .= '<label class="control-label" for="' . $this['id'] . '">' . $this['label'] . '</label>';
            $html .= '<div class="controls">';
        }
        $c = 0;
        foreach($this['options'] as $key=>$value){
            if($c == 0){
                $selected  = 'checked="checked"';
            }else{
                $selected = '';
            }
            $html .= '<label class="radio '.$this['option_label_class'].'"><input type="radio" group="'.$this['id'].'" name="' . $this['id'] . '" value="'.$key.'" '.$selected.'/>' . $value . '</label>';
            $c++;
        }

        if ($this['label']) {
            $html .= '</div>'. "\n";
            $html .= '</div>'. "\n";
        }
        $html .= '</div>';
        return $html;
    }
}

class FormItemCheckbox extends FormItem {
    function render(){
        //some required defaults
        if (!isset($this['class'])) {
            $this['class'] = 'span12';
        }
        if (!isset($this['id'])) {
            $this['id'] = rand_str();
        }

        if(!is_array($this['checked'])){
            $this['checked'] = array();
        }

        $html = '<div class="' . $this['class'] . '">';
        if ($this['label']) {
            $html .= '<div class="control-group">';
            $html .= '<label class="control-label" for="' . $this['id'] . '">' . $this['label'] . '</label>';
            $html .= '<div class="controls">';
        }
        $c = 0;
        foreach($this['options'] as $key=>$value){
            if(in_array($key, $this['checked'])){
                $selected  = 'checked="checked"';
            }else{
                $selected = '';
            }
            $html .= '<label class="checkbox '.$this['option_label_class'].'"><input type="checkbox" name="' . $this['id'] . '[]" value="'.$key.'" '.$selected.'/>' . $value . '</label>';
            $c++;
        }

        if ($this['label']) {
            $html .= '</div>'. "\n";
            $html .= '</div>'. "\n";
        }
        $html .= '</div>';
        return $html;
    }
}

class FormItemFile extends FormItem {

    function render() {
        //some required defaults
        if (!isset($this['class'])) {
            $this['class'] = 'span12';
        }
        if (!isset($this['id'])) {
            $this['id'] = rand_str();
        }

        $html = '<div class="' . $this['class'] . '">';
        if ($this['label']) {
            $html .= '<div class="control-group">';
            $html .= '<label class="control-label" for="' . $this['id'] . '">' . $this['label'] . '</label>';
            $html .= '<div class="controls">';
        }
        
        $html .= '<input type="file" id="' . $this['id'] . '" name="' . $this['id'] . '">';
        if ($this['label']) {
            $html .= '</div>'. "\n";
            $html .= '</div>'. "\n";
        }
        $html .= '</div>';
        return $html;
    }

}

class FormItemRow extends FormItem {

    function render() {
        $html = '';
        if ($this->form->rows > 0) {
            $html .= '</div>';
        }
        $this->form->rows++;
        $html .= '<div class="row-fluid '.$this['class'].'">' . "\n";
        return $html;
    }

}

class FormItemSubmit extends FormItem {

    function render() {
        if (!isset($this['text'])) {
            $this['text'] = 'Submit';
        }
        $html = '<div class="' . $this['class'] . '">';
        $html .= '<button type="submit" class="btn btn-primary '.$this['style'].'">' . $this['text'] . '</button>';
        $html .= '</div>';
        return $html;
    }

}

class FormItemBoundingBox extends FormItem {

    function render() {
        $s = new FormItemHidden(array('id' => $this['id'] . '_input'), $this->form);
        $html = $s->render();


        $html .= '<div class="row-fluid"><div class="span12 gmap" id="' . $this['id'] . '" style="height:300px"></div></div>';

        return $html;
    }
}

class FormItemTag extends FormItem {

    function render() {

        Template::addJs('js/select2.min.js');
        Template::addJs('js/form.tag.js');
        Template::addCss('css/select2.css');

        if(is_array($this['default'])){
            $this['default'] = implode(', ',$this['default']);
        }

        $s = new FormItemText(array(
            'id' => $this['id'],
            'default' => $this['default'],
            'label' => $this['label'],
            'class' => 'tagbox'
            ), $this->form);
        $html = $s->render();

        return $html;
    }
} 
