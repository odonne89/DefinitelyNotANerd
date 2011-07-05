<?php

class SimpleIPCServer {

    /*Private class variables*/
    private $connectionSocket;
    private $messageSocket;
    private $host;
    private $port;
    private $linger;
    private $clientBacklog;
    private $connectionOpen;
    private $shutdown_command;
    
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
        $this->clientBacklog = 5;
        $this->linger = array('l_onoff' => 1, 'l_linger' => 0);
	$this->shutdown_command = "shutdown";
        $this->connectionOpen = false;
        
        $this->connectionSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        
        if (is_resource($this->connectionSocket)){
            socket_set_option($this->connectionSocket, SOL_SOCKET, SO_LINGER, $this->linger);
        
            if(socket_bind($this->connectionSocket, $this->host, $this->port)){
                
                if (socket_listen($this->connectionSocket, $this->clientBacklog)){
                    $this->connectionOpen = true;
                }
                else{ socket_close($this->connectionSocket); }
            }
            else{ socket_close($this->connectionSocket); } 
        }
        else{ socket_close($this->connectionSocket); }
	
	return;
    }
    
    /**************************************************************************
     * Name : __destruct
     * Arguments : none
     * Purpose : Close socket
     * Returns : none
     *************************************************************************/
    function __destruct(){
        $this->disconnect_client();
        $this->close_connection();
        return;
    }

    /**************************************************************************
     * Name : getShutdownCommand
     * Arguments : none
     * Purpose : To return the string used to signal the remote host to shutdown
     * Returns : The string used to signal the remote host to shutdown
     *************************************************************************/    
    function getShutdownCommand(){
        return $this->shutdown_command;
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
     * Name : connection_open
     * Arguments : none
     * Purpose : Returns true if cthe onnection socket creation was successful,
     *              false otherwise
     * Returns : none
     *************************************************************************/    
    function connection_open(){
        return $this->connectionOpen;
    }
    
    /**************************************************************************
     * Name : await_client
     * Arguments : none
     * Purpose : Allows a client to connect to the server, blocks until a
     *              connection becomes present
     * Returns : True when a client connects, false if there is a socket error
     *************************************************************************/
    public function await_client(){
        
        $returnStatus = false;
        
        if (is_resource($this->connectionSocket)){
            $this->messageSocket = socket_accept($this->connectionSocket);
            
            if (is_resource($this->messageSocket)){
                $returnStatus = true;
            }
        }
        
        return $returnStatus;
    }

    /**************************************************************************
     * Name : await_command
     * Arguments : none
     * Purpose : Close socket
     * Returns : The command sent by the client, blocks until a
     *              message becomes present, returns false if the client
     *              disconnects.
     *************************************************************************/    
    public function await_command(){
        
        $command = false;
        
        if (is_resource($this->messageSocket)){
            $command = @socket_read($this->messageSocket, 2048);
        }
        
        if(!$command){
            $this->disconnect_client();
        }
        
        return $command;
    }

    /**************************************************************************
     * Name : send_response
     * Arguments : (string)     $response - send a response to the client
     * Purpose : Close socket
     * Returns : none
     *************************************************************************/    
    public function send_response($response){
        
        $returnStatus = false;
        
        if (is_resource($this->messageSocket)){
            socket_write($this->messageSocket, $response, strlen($response));
            $returnStatus = true;
        }
        
        return $returnStatus;
    }

    /**************************************************************************
     * Name : disconnect_client
     * Arguments : none
     * Purpose : Closes the client socket
     * Returns : none
     *************************************************************************/    
    public function disconnect_client(){
        if (is_resource($this->messageSocket)){
            socket_close($this->messageSocket);
        }
        return;
    }

    /**************************************************************************
     * Name : close_connection
     * Arguments : none
     * Purpose : Close the connection socket
     * Returns : none
     *************************************************************************/    
    public function close_connection(){
        if (is_resource($this->connectionSocket)){
            socket_close($this->connectionSocket);
        }
        return;
    }
    
    
}