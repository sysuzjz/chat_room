<?php
    session_name("chatRoom");
    session_start();
    $con = mysql_connect("localhost","root","");
    if(!$con) {
        die("can't connect to the server");
    }
    mysql_select_db("chatRoom",$con);

    function insertMsg($userId, $msg) {
        $data['from_user'] = $userId;
        $data['time'] = time() + 3600 * 8;
        $data['msg'] = $msg;
        return insert("message", $data);
    }

    function getUnreadMsg() {
        $cond['is_read'] = 0;
        return select("message", "*", $cond, "time DESC");
    }

    function setMsgRead() {
        $cond['is_read'] = 0;
        $data['is_read'] = 0;
        return update("message", $cond, $data);
    }

    function select($table, $selected = "*", $cond = "", $order = "", $limit = "")  {
        $queryStr = "SELECT $selected FROM $table ";
        if(!empty($cond)) {
            $count = 0;
            $queryStr = $queryStr." WHERE ";
            foreach ($cond as $key => $value) {
                if($count > 0) {
                    $queryStr = $queryStr." AND ";
                }
                $count++;
                $queryStr = $queryStr.$key."="."'".$value."'";
            }
        }
        if(!empty($order)) {
            $queryStr = $queryStr." ORDER BY ".$order;
        }
        if(!empty($limit)) {
            $queryStr = $queryStr." LIMIT ".$limit;
        }
        $queryResult = mysql_query($queryStr);
        $result = array();
        if(!$queryResult) {
            return $result;
        }
        $num = mysql_num_rows($queryResult);
        for($i = 0; $i < $num; $i++) {
            $temp = mysql_fetch_array($queryResult);
            array_push($result, $temp);
        }
        return $result;
    }

    function update($table, $data, $cond) {
        $count = 0;
        $queryStr = "UPDATE $table SET ";
        foreach ($data as $key => $value) {
            if($count > 0) {
                $queryStr = $queryStr.", ";
            }
            $count++;
            $queryStr = $queryStr.$key."="."'".$value."'";
        }
        $count = 0;
        $queryStr = $queryStr." WHERE ";
        foreach ($cond as $key => $value) {
            if($count > 0) {
                $queryStr = $queryStr." AND ";
            }
            $count++;
            $queryStr = $queryStr.$key."="."'".$value."'";
        }
        $queryResult = mysql_query($queryStr);
        return $queryResult ? 1 : 0;
    }

    function insert($table, $data) {
        $count = 0;
        $queryStr = "INSERT INTO $table (";
        $keyStr = "";
        $valueStr = "";
        foreach ($data as $key => $value) {
            if($count > 0) {
                $keyStr = $keyStr.", ";
                $valueStr = $valueStr.", ";
            }
            $count++;
            $keyStr = $keyStr.$key;
            $valueStr = $valueStr."'".$value."'";
        }
        $queryStr = $queryStr.$keyStr.") value(".$valueStr.")";
        $queryResult = mysql_query($queryStr);
        return $queryResult ? 1 : 0;
    }

    function delete($table, $cond) {
        $queryStr = "DELETE FROM $table WHERE ";
        $count = 0;
        foreach ($cond as $key => $value) {
            if($count > 0) {
                $queryStr = $queryStr." AND ";
            }
            $count++;
            $queryStr = $queryStr.$key."="."'".$value."'";
        }
        $queryResult = mysql_query($queryStr);
        return $queryResult ? 1 : 0;
    }

?>