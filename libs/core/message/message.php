<?php

function message($text, $level = 'info'){
    if(System::$cli){
        echo $text , "\n";
        return;
    }
    //sesh(1) tells the session class to start a session if one doesn't already exist;
    $messages = session(1)->messages;
    if(!$messages){
        $messages = array();
    }
    
    $messages[] = array(
        'text' => $text,
        'level' => $level
    );
    
    session()->messages = $messages;
}

function message_render(){
    $messages = session()->messages;
    if(empty($messages)){
        return '';
    }
    
    $messages_html = '';
    foreach($messages as $message){
        $messages_html .= '<div class="alert alert-info span12">' . $message['text'] . '</div>';
    }
    $html = '<div class="row-fluid messages">
                '.$messages_html.'
            </div>';


    session()->messages = array(); 
    return $html;
}