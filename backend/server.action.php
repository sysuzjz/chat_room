<?php
    require_once("./public.action.php");
    require_once("./socket.action.php");
    require_once("./function.action.php");

    class PChatServer{

        var $systemVars   = array(
            "appName"   => "PChat",
            "appVersion"   => "1.1",
            "author"   => array("panda")
           );

        var $port   = 10005;
        var $domain   = "0.0.0.0";
        var $maxClients = -1;
        var $readBufferSize   = 81920;
        var $readEndCharacter = "\n";
        var $maxQueue = 500;
        var $debug   = true;
        var $debugMode = "html";
        var $debugDest = "stdout";
        var $null   = array();
        var $clientFD = array();
        var $clientInfo = array();
        var $serverInfo = array();
        var $clients = 0;

        function PChatServer( $domain = "0.0.0.0", $port = 10000 ){
            $this->domain = $domain;
            $this->port   = $port;
            $this->serverInfo["domain"]         = $domain;
            $this->serverInfo["port"]         = $port;
            $this->serverInfo["servername"]     = $this->systemVars["appName"];
            $this->serverInfo["serverversion"] = $this->systemVars["appVersion"];
            set_time_limit( 0 );
        }

        function setMaxClients( $maxClients ){
            $this->maxClients = $maxClients;
        }

        function setDebugMode( $debug, $dest = "stdout" ){
            if( $debug === false ){
                $this->debug = false;
                return true;
            }
            $this->debug   = true;
            $this->debugMode = $debug;
            $this->debugDest = $dest;
        }

        function start(){
            $this->initFD = socket_create( AF_INET, SOCK_STREAM, 0 );
            if( !$this->initFD ) {
                die( "PChatServer: Could not create socket." );
            }
            // adress may be reused
            socket_setopt( $this->initFD, SOL_SOCKET, SO_REUSEADDR, 1 );
            // bind the socket
            if(!socket_bind( $this->initFD, $this->domain, $this->port ) ){
                socket_close( $this->initFD );
                die( "PChatServer: Could not bind socket to ".$this->domain." on port ".$this->port." ( ".$this->getLastSocketError( $this->initFd )." )." );
            }
            // listen on selected port
            if(!socket_listen( $this->initFD, $this->maxQueue ) ) {
                die( "PChatServer: Could not listen ( ".$this->getLastSocketError( $this->initFd )." )." );
            }
            $this->sendDebugMessage( "Listening on port ".$this->port.". Server started at ".date( "H:i:s", time() ) );
            // this allows the shutdown function to check whether the server is already shut down
            $GLOBALS["_PChatServerStatus"] = "running";
            // this ensures that the server will be sutdown correctly
            register_shutdown_function( array( $this, "shutdown" ) );
            if( method_exists( $this, "onStart" ) ) {
                $this->onStart();
            }
            $this->serverInfo["started"] = time();
            $this->serverInfo["status"]   = "running";
            while( true ){
                $readFDs = array();
                array_push( $readFDs, $this->initFD );
                // fetch all clients that are awaiting connections
                for( $i = 0; $i < count( $this->clientFD ); $i++ ) {
                    if( isset( $this->clientFD[$i] ) ) {
                        array_push( $readFDs, $this->clientFD[$i] );
                    }
                }
                // block and wait for data or new connection
                $ready = socket_select( $readFDs, $this->null, $this->null, NULL );
                if( $ready === false ){
                    $this->sendDebugMessage( "socket_select failed." );
                    $this->shutdown();
                }
                $this->sendDebugMessage( "socket_select failed." );
                // check for new connection
                if( in_array( $this->initFD, $readFDs ) ) {
                    $newClient = $this->acceptConnection( $this->initFD );
                    // check for maximum amount of connections
                    if( $this->maxClients > 0 ) {
                        if( $this->clients > $this->maxClients ) {
                            $this->sendDebugMessage( "Too many connections." );
                            if( method_exists( $this, "onConnectionRefused" ) ) {
                                $this->onConnectionRefused( $newClient );
                            }
                            $this->closeConnection( $newClient );
                        }
                    }
                    if( --$ready <= 0 ) {
                        continue;
                    }
                }
                
                // check all clients for incoming data
                for( $i = 0; $i < count( $this->clientFD ); $i++ ) {
                    if( !isset( $this->clientFD[$i] ) ) {
                        continue;
                    }
                    if( in_array( $this->clientFD[$i], $readFDs ) ) {
                        $data = $this->readFromSocket( $i );
                        // empty data => connection was closed
                        if( !$data ) {
                            $this->sendDebugMessage( "Connection closed by peer" );
                            $this->closeConnection( $i );
                        } else {
                            $this->sendDebugMessage( "Received ".trim( $data )." from ".$i );
                            $this->onReceiveData( $i, $data );
                        }
                    }
                }
            }
        }

        function readFromSocket( $clientId ){
            // start with empty string
            $data   = "";
            // read data from socket
            while( $buf = socket_read( $this->clientFD[$clientId], $this->readBufferSize ) ){
                $data .= $buf;
                $endString = substr( $buf, - strlen( $this->readEndCharacter ) );
                if( $endString == $this->readEndCharacter ) {
                    break;
                }
                if( $buf == NULL ) {
                    break;
                }
            }
            if( $buf === false ) {
                $this->sendDebugMessage( "Could not read from client ".$clientId." ( ".$this->getLastSocketError( $this->clientFD[$clientId] )." )." );
            }
            return $data;
        }

        function acceptConnection( $socket ){
            for( $i = 0 ; $i <= count( $this->clientFD ); $i++ ){
                if( !isset( $this->clientFD[$i] ) || $this->clientFD[$i] == NULL ) {
                    $this->clientFD[$i] = socket_accept( $socket );
                    socket_setopt( $this->clientFD[$i], SOL_SOCKET, SO_REUSEADDR, 1 );
                    $peer_host = "";
                    $peer_port = "";
                    socket_getpeername( $this->clientFD[$i], $peer_host, $peer_port );
                    $this->clientInfo[$i] = array(
                        "host"   => $peer_host,
                        "port"   => $peer_port
                        // "connectOn" => time()
                    );
                    $this->clients++;
                    $this->sendDebugMessage( "New connection ( ".$i." ) from ".$peer_host." on port ".$peer_port );
                    if( method_exists( $this, "onConnect" ) ) {
                        $this->onConnect( $i );
                    }
                    return $i;
                }
           }
        }

        function isConnected( $id ){
            if( !isset( $this->clientFD[$id] ) )
                return false;
            return true;
        }

        function closeConnection( $id ){
            if( !isset( $this->clientFD[$id] ) ) {
                return false;
            }
            if( method_exists( $this, "onClose" ) ) {
                $this->onClose( $id );
            }
            $this->sendDebugMessage( "Closed connection ( ".$id." ) from ".$this->clientInfo[$id]["host"]." on port ".$this->clientInfo[$id]["port"] );
            socket_close( $this->clientFD[$id] );
            $this->clientFD[$id] = NULL;
            unset( $this->clientInfo[$id] );
            $this->clients--;
        }

        function shutDown(){
            if( $GLOBALS["_PChatServerStatus"] != "running" ) {
                exit;
            }
            $GLOBALS["_PChatServerStatus"] = "stopped";
            if( method_exists( $this, "onShutdown" ) ) {
                $this->onShutdown();
            }
            $maxFD = count( $this->clientFD );
            for( $i = 0; $i < $maxFD; $i++ ) {
                $this->closeConnection( $i );
            }
            socket_close( $this->initFD );
            $this->sendDebugMessage( "Shutdown server." );
            exit;
        }

        function getClients(){
            return $this->clients;
        }

        function sendData( $clientId, $data, $debugData = true, $dataType = "string" ){
            if( !isset( $this->clientFD[$clientId] ) || $this->clientFD[$clientId] == NULL ) {
                return false;
            }
            if( $debugData ) {
                $this->sendDebugMessage( "sending: \"" . $data . "\" to: $clientId" );
            }
            if($dataType == "json") {
                $data = json_encode($data);
            }
            $sendData = createResponse($dataType, $data);
            if(!socket_write( $this->clientFD[$clientId], $sendData, strlen($sendData) ) ) {
                $this->sendDebugMessage( "Could not write '".$data."' client ".$clientId." ( ".$this->getLastSocketError( $this->clientFD[$clientId] )." )." );
            }
        }

        function broadcastData( $data, $exclude = array(), $debugData = true, $dataType = "json" ){
            if( !empty( $exclude ) && !is_array( $exclude ) ) {
                $exclude = array( $exclude );
            }
            $debugDatas = is_array($data) ? implode("\n", $data) : $data;
            for( $i = 0; $i < count( $this->clientFD ); $i++ ) {
                if( isset( $this->clientFD[$i] ) && $this->clientFD[$i] != NULL && !in_array( $i, $exclude ) ) {
                    if( $debugData ) {
                        $this->sendDebugMessage( "sending: \"" . $debugDatas . "\" to: $i" );
                    }
                    $broadcastData = createBroadcast($dataType, $data);
                    if(!socket_write( $this->clientFD[$i], $broadcastData ) ) {
                        $this->sendDebugMessage( "Could not write '".$debugDatas."' client ".$i." ( ".$this->getLastSocketError( $this->clientFD[$i] )." )." );
                    } else {
                        $this->sendDebugMessage( "sending: \"" . $debugDatas . "\" to: $i successfully" );
                    }
                }
            }
        }

        function getClientInfo( $clientId ){
            if( !isset( $this->clientFD[$clientId] ) || $this->clientFD[$clientId] == NULL )
                return false;
            return $this->clientInfo[$clientId];
        }

        function sendDebugMessage( $msg ){
            if(is_array($msg)) {

            }
            if( !$this->debug )
                return false;
            $msg = date( "Y-m-d H:i:s", time() + 3600 * 8 ) . " " . $msg;
            switch( $this->debugMode ) {
                case "text":
                    $msg = $msg."\n";
                    break;
                case "html":
                    $msg = htmlspecialchars( $msg ) . "<br/>\n";
                    break;
            }
            if( $this->debugDest == "stdout" || empty( $this->debugDest ) ) {
                echo $msg."<br />";
                flush();
                return true;
            }
            error_log( $msg, 3, $this->debugDest );
            return true;
        }

        function getLastSocketError( $fd ){
            $lastError = socket_last_error( $fd );
            return "msg: " . socket_strerror( $lastError ) . " / Code: ".$lastError;
        }
        function onReceiveData($i,$data){
            $datas['msg'] = $data;
            insert("test", $datas);
            $this->handleData($i, $data);
        }
        function handleData($count, $request) {
            $type = getMsgType($request);
            $function = getFunction($request);
            $data = getDataFromRequest($request);
            switch ($type) {
                case "get":
                    $this->handleGet($function, $data, $count);
                    break;
                case "broadcast":
                    $this->handleBroadcast($function, $data, $count);
                    break;
                default:
                    break;
            }
            
        }

        function handleGet($function, $data, $count) {
            $this->sendDebugMessage("enter handleGet in ".$function);
            var_dump($data);
            switch ($function) {
                case 'register':
                    if(register($data)) {
                        $this->sendData($count, "1");
                    } else {
                        $this->sendData($count, "0");
                    }
                    break;
                case 'login':
                    $this->sendDebugMessage("enter function login");
                    if(login($data)) {
                        $this->sendData($count, "1");
                        $this->clientInfo[$count]['uname'] = $data['uname'];
                    } else {
                        $this->sendData($count, "0");
                        $this->closeConnection($count);
                    }
                    break;
                case 'forgetPassword':
                    if(forgetPw($data)) {
                        $this->sendData($count, "1");
                    } else {
                        $this->sendData($count, "0");
                    }
                    break;
                case 'updatePhoto':
                    $uname = $this->clientInfo[$count]['uname'];
                    if(updatePhoto($uname, $data)) {
                        $this->sendData($count, "1");
                    } else {
                        $this->sendData($count, "0");
                    }
                    break;
                case 'getPhoto':
                    $uname = $data['uname'];
                    $photo = getPhoto($uname);
                    $this->sendData($count, $photo);
                    break;
                case 'kill':
                    die("kill for testing<br />");
                    break;
                default:
                    # code...
                    break;
            }
        }

        function handleBroadcast($function, $data, $count) {
            $this->sendDebugMessage("enter handleBroadcast ".$function);
            var_dump($data);
            switch ($function) {
                case 'friendList':
                    $this->broadcastClients();
                    break;
                case 'chat':
                    if(isset($data['isImage']) && $data['isImage']) {
                        $dataType = "image";
                        $returnData = array("uname" => $this->clientInfo[$count]['uname'], "chatcontent" => $data['image']);
                        $returnData = json_encode($returnData);
                    } else {
                        $dataType = "json";
                        $returnData = array("uname" => $this->clientInfo[$count]['uname'], "chatcontent" => $data);
                        
                    }
                    var_dump($returnData);
                    $this->broadcastData($returnData, array(), true, $dataType);
                    break;
                default:
                    # code...
                    break;
            }

        }

        function broadcastClients() {
            $clientsStr = "";
            for($i = 0; $i < count($this->clientInfo); $i++) {
                if(isset($this->clientInfo[$i]) && is_array($this->clientInfo[$i])) {
                    $clientsStr = $clientsStr.json_encode($this->clientInfo[$i])."&";
                }
            }
            $clientsStr = substr($clientsStr, 0, -1);
            $this->broadcastData($clientsStr, array(), true, "array");
        }
    }
    $PChatServer = new PChatServer("0.0.0.0", 10000);
    $PChatServer->start();
?>