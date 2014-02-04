<?php

function session_routes(){
    $paths = array();
    return $paths;
}

function session($state = 0){
    static $session;
    if(!$session){
        $session = new Session();
    }
    
    if($session->state() != $state){
        $session->state($state);
    }
    
    return $session;
}

function session_kill(){//session_destroy is already a function :(

    sesh()->destroy();
    redirect('/');
    
}
