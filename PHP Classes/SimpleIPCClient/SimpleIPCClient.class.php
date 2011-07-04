<?php

class SimpleIPCClient {

    /*Private class variables*/
    private $socket;
    private $host;
    private $port;
    private $timeout;
    private $linger;
    private $shutdown_command;
    private $connected;
    
    /**************************************************************************
     * Name : __construct
     * Arguments : (string)	$in_host - host address
     *		   (int)	$in_port - host port
     * Purpose : Initialize values and create socket for remote connection.
     * Returns : none
     *************************************************************************/
    function __construct($in_host, $in_port){
        $this->host = $in_host;
        $this->port = $in_port;
        $this->timeout = array('sec' => 5, 'usec' => 0);
        $this->linger = array('l_onoff' => 1, 'l_linger' => 0);
	$this->shutdown_command = "shutdown";
        
        $this->socket = socket_create(AF_INET, SOCK_STREAM, 0);
	
	return;
    }
    
    /**************************************************************************
     * Name : __destruct
     * Arguments : none
     * Purpose : Close socket
     * Returns : none
     *************************************************************************/
    function __destruct(){
        $this->close();
        return;
    }
    
    /**************************************************************************
     * Name : setTimeout
     * Arguments : (Array)	$timeout - elements: sec, usec
     * Purpose : Sets the socket timeout, must be done before connection.
     * Returns : none
     *************************************************************************/    
    public function setTimeout($timeout){
	$this->timeout = $timeout;
	return;
    }

    /**************************************************************************
     * Name : setLinger
     * Arguments : (Array)	$linger - elements: l_onoff, l_linger
     * Purpose : Sets the socket linger options, must be done before connection.
     * Returns : none
     *************************************************************************/ 
    public function setLinger($linger){
	$this->linger = $linger;
	return;
    }
    
    /**************************************************************************
     * Name : setShutdownCommand
     * Arguments : (string)	$command - shutdown command
     * Purpose : Sets the string used to signal the remote host to shutdown
     * Returns : none
     *************************************************************************/ 
    public function setShutdownCommand($command){
	$this->shutdown_command = $command;
	return;
    }    
    
    /**************************************************************************
     * Name : connect
     * Arguments : none
     * Purpose : Connect to the remote host
     * Returns : True if connection is successful, false otherwise
     *************************************************************************/ 
    public function connect(){
	$this->connected = false;
	socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, $this->timeout);
        socket_set_option($this->socket, SOL_SOCKET, SO_LINGER, $this->linger);
        $this->connected = @socket_connect($this->socket, $this->host, $this->port);
	
        return $this->connected;
    }
    
    /**************************************************************************
     * Name : close
     * Arguments : none
     * Purpose : Closes the socket
     * Returns : none
     *************************************************************************/ 
    public function close(){
	if (is_resource($this->socket)){
	    socket_shutdown($this->socket);
	    socket_close($this->socket);
	}
	return;
    }
    
    /**************************************************************************
     * Name : send_command
     * Arguments : (string)	$command - command to send to remote host
     * Purpose : Sends a command to remote host
     * Returns : Number of bytes sent, if successful, false otherwise
     *************************************************************************/ 
    public function send_command($command){
        $byteSent = false;
	if ($this->connected){
	    $byteSent = socket_send($this->socket, $command, strlen($command), 0);
	}
	return $byteSent;
    }
    
    /**************************************************************************
     * Name : receive_response
     * Arguments : none
     * Purpose : Get response from remote host
     * Returns : (string) $response
     *************************************************************************/ 
    public function receive_response(){
        $recv = "";
	if ($this->connected){
	    socket_recv($this->socket, $recv, 2048, 0);
	}
        return $recv;
    }
    
    /**************************************************************************
     * Name : send_command_await_response
     * Arguments : (string)	$command - command to send to remote host
     * Purpose : Sends a command to remote host and await its response
     * Returns : (string) $response
     *************************************************************************/ 
    public function send_command_await_response($command){
        $this->send_command($command);
        return $this->receive_response();
    }
    
    /**************************************************************************
     * Name : shutdown_server
     * Arguments : none
     * Purpose : Send shutdown command to remote host, default is "shutdown",
     *		can be set using setShutdownCommand() method
     * Returns : none
     *************************************************************************/
    public function shutdown_server(){
	if ($this->connected){
	    socket_send($this->socket, $this->shutdown_command, strlen($this->shutdown_command), 0);
	}
        return;
    }
}

?>