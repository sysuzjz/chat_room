<?php
    require_once("./public.action.php");
    define("PIC_DIR", "./picture/");
    define("PIC_W", 60);
    define("PIC_H", 60);
    function isUnameExist($uname) {
        $cond['uname'] = $uname;
        $queryResult = select("user", "*", $cond);
        return !empty($queryResult);
    }

    function isEmailExist($email) {
        $cond['email'] = $email;
        $queryResult = select("user", "*", $cond);
        return !empty($queryResult);
    }

    function isPlatformLogin($uname) {
        $cond['uname'] = $uname;
        $queryResult = select("user", "platform", $cond);
        $platform = $queryResult[0];
        return $platform != "none";
    }

    function register($param) {
        if(is_array($param) && isset($param['uname'])) {
            if(!isUnameExist($param['uname']) && !isEmailExist($param['email'])) {
                insert("user", $param);
                return true;
            }
        }
        return false;
    }

    function login($param) {
        if(isset($param['email'])) {
            if(!forgetPw($param['email'])) {
                return false;
            }
        }
        if(isset($param['platform'])) {
            if(!isUnameExist($param['uname'])) {
                insert("user", $param);
                $photo = file_get_contents($param['photo']);
                updatePhoto($param['uname'], $photo);
            }

            return true;
        }
        if(is_array($param) && isset($param['uname']) && isset($param['password'])) {
            $result = select("user", "uname", $param);
            if(!empty($result)) {
                return true;
            }
        }
        return false;
    }

    function forgetPw($email) {
        require_once("./library/PHPMailer_5.2.4/class.phpmailer.php");
        $randomCode = rand(1000,9999);
        if(!isEmailExist($email)) {
            return false;
        }
        $mail = new phpmailer();
        $mail->IsSMTP(); // 使用SMTP方式发送
        $mail->CharSet = "utf-8";
        //$mail->Port = 465;
        $mail->Host = "smtp.163.com"; 
        //$mail->SMTPSecure = 'ssl';
        $mail->SMTPAuth = true; // 启用SMTP验证功能
        $mail->Username = "argo_public@163.com"; // 邮局用户名(请填写完整的email地址)
        $mail->Password = "sixargo"; // 邮局密码
        $mail->From = "argo_public@163.com"; //邮件发送者email地址
        $mail->FromName = "PChat";
        $mail->AddAddress("$email", "hello");//收件人地址，可以替换成任何想要接收邮件的email信箱,格式是AddAddress("收件人email","收件人姓名")
        $mail->IsHTML(true); // set email format to HTML //是否使用HTML格式
        $mail->Subject = "PChat 邮箱验证"; //邮件标题
        $str1 = "<p>点击链接完成验证： </p>";
        $str1 .= "<a href='http://192.168.137.1/PChat/forgetPw.action.php?randomCode=".$randomCode.
            "'>http://192.168.137.1/PChat/forgetPw.action.php?randomCode=".$randomCode. "</a>";
        $mail->Body = $str1; //邮件内容
        return $mail->Send();
    }

    function updatePhoto($uname, $data) {
        if(isUnameExist($uname)) {
            $fileName = time().rand(0, 9);
            $fileName = PIC_DIR.$fileName.".png";
            file_put_contents($fileName, $data);
            $cond['uname'] = $uname;
            $param['photo'] = $fileName;
            return update("user", $param, $cond);
        }
        return false;
    }

    function getPhoto($uname) {
        $cond['uname'] = $uname;
        if(isUnameExist($uname)) {
            $photo = select("user", "photo", $cond);
            $photo = $photo[0];
            return file_get_contents($photo);
        }
        return "";
    }

    function recordMsg($param) {
        if(is_array($param)) {
            return insert("message", $param);
        }
    }

    function compressPic($pictureData) {
        $tempPicDir = PIC_DIR."temp.png";
        file_put_contents($tempPicDir, $pictureData);
        $newImage = imagecreatetruecolor(PIC_W, PIC_H);
        list($originWidth, $originHeight) = getimagesize($tempPicDir);
        $tempPic = imagecreatefrompng($tempPicDir);
        imagecopyresampled($newImage, $tempPic, 0, 0, 0, 0, PIC_W, PIC_H, $originWidth, $originHeight);
        imagepng($newImage, $tempPicDir, 1);
        $compressedPic = file_get_contents($tempPicDir);
        return $compressedPic;
    }

    

?>