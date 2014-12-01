<?php
    function getRequest($request) {
        $request = str_replace(" ", "", $request);
        // json 嵌套时引号匹配会出问题
        if(stripos($request, '"dataType":"json"')) {
            preg_match('/"data":"(\S+\})"/', $request, $m);
            $data = $m[1];
            $data = str_replace('\\', '', $data);
            $request = preg_replace('/"data":"\S+\}",/', '', $request);
            $request = json_decode($request, true);
            $request['data'] = $data;
        } else {
            $request = json_decode($request, true);
        }
        return $request;
    }

    function getAttrFromRequest($request, $attr) {
        $data = getRequest($request);
        return $data[$attr];
    }
    function getMsgType($request) {
        return getAttrFromRequest($request, "type");
    }

    function getFunction($request) {
        return getAttrFromRequest($request, "function");
    }


    function getDataFromRequest($request) {
        $data = getAttrFromRequest($request, "data");
        $dataType = getAttrFromRequest($request, "dataType");
        switch ($dataType) {
            case "json":
                return json_decode($data, true);
                break;
            case "array":
                return explode("&", $data);
                break;
            // case "image":
            //     return compressPic($data);
            //     break;
            case "image":
                return array("isImage" => true, "image" => $data);
                break;
            default:
                return $data;
                break;
        }
    }

    function get($request, $key) {
        $data = getDataFromRequest($request);
        return $data[$key];
    }

    function createResponse($dataType = "json", $data = "", $allowAlive = 1, $type = "response", $server = "PHP") {
        if($dataType == "json") {
            $data = json_encode($data);
        }
        $response = array("type" => $type, "server" => $server, "dataType" => $dataType, "data" => $data, "allowAlive" => $allowAlive);
        return json_encode($response)."\n";
    }

    function createBroadcast($dataType = "json", $data = "", $type = "broadcast", $source = "0.0.0.0") {
        if($dataType == "json") {
            $data = json_encode($data);
        }
        $broadcast = array("type" => $type, "source" => $source, "dataType" => $dataType, "data" => $data);
        return json_encode($broadcast)."\n";
    }

?>