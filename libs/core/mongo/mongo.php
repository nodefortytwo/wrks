<?php
define('MONGO_TYPE_DOUBLE', 1);
define('MONGO_TYPE_STRING', 2);
define('MONGO_TYPE_OBJECT', 3);
define('MONGO_TYPE_ARRAY', 4);
define('MONGO_TYPE_BINARY', 5);
define('MONGO_TYPE_OBJECT_ID', 7);
define('MONGO_TYPE_BOOL', 8);
define('MONGO_TYPE_DATE', 9);
define('MONGO_TYPE_NULL', 10);

function mongo_init(){
    mdb();
    MongoCursor::$timeout = -1;
}

function mdb($newdb = null){
    static $client, $mdb;
    if(!$client){
        $client = new MongoClient(mongo_connection_string());
    }
    
    if(!$mdb && $newdb){
        $mdb = $client->$newdb;
    }
    
    if(!$mdb){
        $db = Config::get('db_name');
        $mdb = $client->$db;
    }
    return $mdb;
}


function mongo_connection_string(){

    return "mongodb://" . Config::get('DB_SERVER', 'localhost') . ':' . Config::get('DB_PORT', '27017');

}