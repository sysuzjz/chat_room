<?php
    require_once("./public.action.php");
    $data['uname'] = $_POST['uname'];
    $data['password'] = md5(md5($_POST['password']));

    if(empty(select("user", "*", $data))) {
        if(insert("user", $data)) {
            return 1;
        }
    }

    return 0;
    
?>