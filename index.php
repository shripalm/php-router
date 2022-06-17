<?php

    define('BASE_PHYSICAL_PATH', __DIR__);
    function retResponse($code, $message, $data=[]){
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(["msg"=>$message,"data"=>$data]);
        exit;
    }
    try {
        include_once(BASE_PHYSICAL_PATH.'/entry.php');
    } catch (\Throwable $th) {
        retResponse(500, "Internal Server Error", [
            "msg"  => $th->getMessage(),
            "file" => $th->getFile(),
            "line" => $th->getLine(),
            "code" => $th->getCode()
        ]);
    }