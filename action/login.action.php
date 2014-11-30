<?php
    require_once("./public.action.php");
    $cond['uname'] = $_POST['uname'];
    $cond['password'] = md5(md5($_POST['password']));
    $queryResult = select("user", "*", $cond, "", 1);
    if(empty($queryResult)) {
        $result = array("status" => 0, "msg" => "the user does not exist");
    } else {
        $data['is_login'] = 1;
        $data['last_login'] = time() + 3600 * 8;
        if(update("user", $cond, $data) {
            $queryResult = $queryResult[0];
            $_SESSION['userId'] = $queryResult['id'];
            $_SESSION['uname'] = $queryResult['uname'];
            $_SESSION['nickname'] = $queryResult['nickname'];

            $result = array("status" => 1, "msg" => "log in successfully");
        }
    }

    echo json_encode($result);

?>