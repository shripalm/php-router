<?php

    function cors() {

        // Allow from any origin
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
            // you want to allow, and if so:
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');    // cache for 1 day
        }

        // Access-Control headers are received during OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
                // may also be using PUT, PATCH, HEAD etc
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

            exit(0);
        }

    }
    cors();

    define('BASE_PHYSICAL_PATH', __DIR__);
    define('TIME_ZONE', '+0530');
    function retResponse($code, $message, $data=[]){
        
        $date = new \DateTime(date('m/d/Y H:i:s', $_SERVER['REQUEST_TIME']));
        $date->setTimezone(new \DateTimeZone(TIME_ZONE));
        $logInfo = 
        "\n-------------------------------------------------------".
        "\nREMOTE_ADDR: ".$_SERVER['REMOTE_ADDR'].
        "\nREMOTE_PORT: ".$_SERVER['REMOTE_PORT'].
        "\nREQUEST_URI: ".$_SERVER['REQUEST_URI'].
        "\nREQUEST_METHOD: ".$_SERVER['REQUEST_METHOD'].
        "\nREQUEST_TIME: ".$_SERVER['REQUEST_TIME']." | ".$date->format('Y-m-d H:i:s').
        "\nRESPONSE_CODE: $code".
        "\nRESPONSE_MSG: $message".
        "\n-------------------------------------------------------";
        $fname = BASE_PHYSICAL_PATH.'/_logs/req-res/'.$date->format('Y-m-d').'.txt';
        $fp = fopen($fname, 'a');
        fwrite($fp, $logInfo);
        fclose($fp);
        
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(["msg"=>$message,"data"=>$data]);
        exit;
    }
    try {
        include_once(BASE_PHYSICAL_PATH.'/entry.php');
    } catch (\Throwable $th) {
        
        $date = new \DateTime(date('m/d/Y H:i:s', $_SERVER['REQUEST_TIME']));
        $date->setTimezone(new \DateTimeZone(TIME_ZONE));
        $logInfo = 
        "\n-------------------------------------------------------".
        "\nMESG: ".$th->getMessage().
        "\nFILE: ".$th->getFile().
        "\nLINE: ".$th->getLine().
        "\nCODE: ".$th->getCode().
        "\n-------------------------------------------------------";
        $fname = BASE_PHYSICAL_PATH.'/_logs/error/'.$date->format('Y-m-d').'.txt';
        $fp = fopen($fname, 'a');
        fwrite($fp, $logInfo);
        fclose($fp);
        
        retResponse(500, "Internal Server Error", [
            "msg"  => $th->getMessage(),
            "file" => $th->getFile(),
            "line" => $th->getLine(),
            "code" => $th->getCode()
        ]);
    }