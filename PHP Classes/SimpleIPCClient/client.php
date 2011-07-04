#! /usr/bin/php
<?php

// Include(s)
include_once("SimpleIPCClient.class.php");

// Constants
$NO_VALUE_INDICATOR = "--";
$WITH_VALUE_INDICATOR = "-";

// Default values
$host = "127.0.0.1";
$port = "5555";
$shutdown = false;

// Spin through command line arguments, ignoring script name
for ($i = 1; $i < $argc; $i++){
    
    if (substr_count($argv[$i], $NO_VALUE_INDICATOR) > 0){
        $signal = substr($argv[$i], strlen($NO_VALUE_INDICATOR));
        
        if($signal == "shutdown"){
            $shutdown = true;
        }
    }
    else{        
        $signal = strtok($argv[$i], "=");
        $signal = substr($signal, strlen($WITH_VALUE_INDICATOR));
        
        if ( $value = strtok("=") ){
            // Do nothing
        }
        else {
            $value = $argv[++$i];   
        }

        if ($signal == "h"){
            $host = $value;
        }
        elseif ($signal == "p"){
            $port = $value;
        }
    }
}

// Connect and send a test command, shutdown the server if directed
$myIPC = new SimpleIPCClient($host, $port);

if($myIPC->connect()){

    echo $myIPC->receive_response();
    
    echo "Command returned: ". $myIPC->send_command_await_response("testServerFunction01()") . "\n";    
    
    if ($shutdown){
        $myIPC->shutdown_server();
    }
}
else {
    echo "Unable to connect to server\n";
}

?>