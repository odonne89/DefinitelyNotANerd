#! /usr/bin/php
<?php

//TEST FUNCTIONS

include_once("SimpleIPCServer.class.php");

$k = 0;

function testServerFunction01(){
    global $k;
    return ($k++);
}

function testServerFunction02(){
    $j = Array("5", "4");
    return $j;
}

//MAIN SCRIPT

/*Script options*/
error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();

// Constants
$NO_VALUE_INDICATOR = "--";
$WITH_VALUE_INDICATOR = "-";

// Default values
$host = "127.0.0.1";
$port = "5555";

// Spin through arguments, ignoring script name
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

echo "\nPHP IPC Server\n";
echo "--------------\n";

$myIPC = new SimpleIPCServer($host, $port);

if ($myIPC->connection_open()){
    echo "\nAwaiting  incoming connection\n";
}

while(TRUE) {
    
    if ($myIPC->await_client() === false) {
        break;
    } else { echo "Client connected\n"; }
    
    /* Send greeting. */
    $myIPC->send_response("\nPHP IPC Test Server. \n");

    while(TRUE) {
        
        if (false == ($buffer = $myIPC->await_command())) {
            echo "Client disconnected\n";
            break;
        }
        
        if (!$buffer = trim($buffer)) {
            continue;
        }

        if ( $buffer == $myIPC->getShutdownCommand() ) {
            echo "Shutting down server...\n";
            $myIPC->disconnect_client();
            break 2;
        }

        // For this implementation the command rec'd must be the a function
        // call, the result is returned to the client
        $execute = "\$response = $buffer;";
        eval($execute);
        
        // Return a string if the response is an array, must be parsed on the
        // client side
        if (is_array($response)){
            $response = print_r($response, true);
        }
        $myIPC->send_response($response);
        
        echo "Command received: $buffer\n";
    }
}

$myIPC->close_connection();

?>