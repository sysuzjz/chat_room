<?php
    require_once("./public.action.php");
    require_once("./socket.action.php");
    //error_reporting(E_ALL);
    echo "<h2>tcp/ip connection </h2>\n";
    $service_port = 10000;
    $address = '127.0.0.1';
    // $address = '172.18.183.118';

    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    header("Content-type: text/html; charset=gb2312");
    if ($socket === false) {
        echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
    } else {
        echo "OK. \n";
    }

    echo "Attempting to connect to '$address' on port '$service_port'...";
    $result = socket_connect($socket, $address, $service_port);
    if($result === false) {
        echo "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
    } else {
        echo "OK \n";
    }
    $in = "HEAD / http/1.1\r\n";
    $in .= "HOST: localhost \r\n";
    $in .= "Connection: close\r\n\r\n";
    $out = "";
    echo "sending http head request ...";
    $testIn = array("password" => "1224456", "uname" => "xxxwy");
    $testIn = array("data" => json_encode($testIn), "dataType" => "json", "keepAlive" => "1", "delay" => "0", "type" => "get", "source" => "java", "function" => "kill");
    $testIn = json_encode($testIn);
    $testIn .= "\n";
    socket_write($socket, $testIn, strlen($testIn));
    echo  "OK\n";

    echo "Reading response:\n\n";
    $count = 0;
    while ($out = socket_read($socket, 819200)) {
        echo $out;
        if(!empty($out)) {
            $data['msg'] = $out;
            insert("test", $data);
        }
    }
    echo "closeing socket..";
    socket_close($socket);
    echo "ok .\n\n";
?>