<?php
    include_once('constants.php');

    // Commented below line is for not get into trouble on production
    // error_reporting(1);

    $conn = new mysqli(HOST, USER, PASS, DB);

    function retResponse($code, $message, $data=[]){
        http_response_code($code);
        echo json_encode(["msg"=>$message,"data"=>$data]);
        exit;
    }

    function checkContentType(){
        if(! isset($_SERVER['CONTENT_TYPE'])) $_SERVER['CONTENT_TYPE'] = '';

        $contentType = explode(';', $_SERVER['CONTENT_TYPE'])[0];

        // These are only content type which is valid in whole app
        // However You can change it accordingly
        $validContentType = [
            "application/json",
            "multipart/form-data",
            ''
        ];

        if(! in_array($contentType, $validContentType)) retResponse(406, 'Invalid Content Type');
    }

    function getJsonFromBody(){
        return json_decode(stream_get_contents(fopen('php://input', 'r')), true);
    }

    function getAuthHeader(){
        $headers = null;
        if (isset($_SERVER['Authorization'])) $headers = trim($_SERVER["Authorization"]);
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) $headers = trim($_SERVER["HTTP_AUTHORIZATION"]); //Nginx or fast CGI
        elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            // print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) $headers = trim($requestHeaders['Authorization']);
        }
        return $headers;
    }

    function getBearerToken(){
        $headers = getAuthHeader();
        if(empty($headers)) retResponse(406, 'Headers Not Found');
        return explode(' ', $headers)[1];
    }

    function essentialCall($auth){
        checkContentType();
        if($auth) getBearerToken();
    }