<?php
    // Commented below line is for not get into trouble on production
    // error_reporting(1);

    // $conn = new mysqli(HOST, USER, PASS, DB);

    $routes = [];

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
        if($auth){
            $token = getBearerToken();
            $GLOBALS['bearer_token']['encoded'] = $token;
            fetchJWTData($token);
        }
    }

    function registerRoute($route, $path, $method='GET', $auth=false){
        $GLOBALS['all_routes'][$method][$route] = ["path"=>$path, "method"=> $method, "auth"=> $auth];
    }

    function routeExtractor(){
        function str_replace_first($search, $replace, $subject){
            $search = '/'.preg_quote($search, '/').'/';
            return preg_replace($search, $replace, $subject, 1);
        }
        $routeWithoutQueryParams = explode('?', $_SERVER['REQUEST_URI'])[0];
        $routeWithoutBaseURL = str_replace_first(BASE_URL, "", $routeWithoutQueryParams);
        $route = str_replace('//', '/', $routeWithoutBaseURL);
        if($route[0] !== '/') $route = '/'.$route;
        $route = rtrim($route, '/');
        return $route; 
    }

    function routeMatcher($method){
        $success = true;
        $matchedRoute = [];
        $route = explode('/', $GLOBALS['requestedRoute']);
        $all_routes = $GLOBALS['all_routes'][$method];
        foreach ($all_routes as $key => $value) {
            $success = true;
            $routeToCheck = explode('/', $key);
            if(count($route) !== count($routeToCheck)){
                $success = false;
                continue;
            }
            foreach ($route as $rtc_key => $rtc_value){
                if($routeToCheck[$rtc_key][0] === ':') {
                    $GLOBALS['route_params'][ltrim($routeToCheck[$rtc_key], ':')] = $rtc_value;
                }
                else{
                    if($rtc_value !== $routeToCheck[$rtc_key]){
                        $success = false;
                        break;
                    }
                }
            }
            if($success) {
                $matchedRoute = $value;
                $matchedRoute['route_params'] = $GLOBALS['route_params'];
                $matchedRoute['route_matched'] = $key;
                $matchedRoute['route_asked'] = $GLOBALS['requestedRoute'];
                break;
            }
        }
        if(!$success || ($all_routes === null)) retResponse(404, 'Invalid Route');
        return $matchedRoute;
    }

    function fetchJWTData($token){
        require_once(BASE_PHYSICAL_PATH.'/_library/jwt/jwt.php');
        $GLOBALS['bearer_token']['decoded'] = jwtDecode($token, JWT_SECRET, JWT_ALGO);
    }

    function generateJWT($data){
        require_once(BASE_PHYSICAL_PATH.'/_library/jwt/jwt.php');
        return jwtEncode($data, JWT_SECRET, JWT_ALGO);
    }

    function get_requiredFields($payload, $list = []){
        $returnObject = [];
        foreach ($list as $key => $value) {
            if(isset($payload[$value])){
                $returnObject[$value] = $payload[$value];
            }
            else {
                retResponse(400, "$value is required field", [
                    "debug" => [
                        "requestedPayload" => $payload, 
                        "requiredList" => $list
                    ]
                ]);
            }
        }
        return $returnObject;
    }

    function get_optionalFields($payload, $list = []){
        $returnObject = [];
        foreach ($list as $key => $value) {
            if(isset($payload[$value])){
                $returnObject[$value] = $payload[$value];
            }
        }
        return $returnObject;
    }

    function mailToUser($subject, $email, $body){
        require_once(BASE_PHYSICAL_PATH.'/_library/sendgrid-php/vendor/autoload.php');
        $email_send = new \SendGrid\Mail\Mail(); 
        $email_send->setFrom(SENDGRID_MAIL, "Tangle Coder");
        $email_send->setSubject($subject);
        $email_send->addTo("$email", "Tangle Coder");
        $email_send->addContent("text/plain", "$body");
        $email_send->addContent(
          "text/html", "$body"
        );
        // $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
        $sendgrid = new \SendGrid(SENDGRID_ID);
        try {
          $response = $sendgrid->send($email_send);
          if(($response->statusCode() > 199 ) && ($response->statusCode() < 300)){
            return true;
          }else{
            retResponse($response->statusCode(), "SendGrid Error");
          }
        } catch (Exception $e) {
          retResponse(500, 'Caught exception: '. $e->getMessage());
        }
    }


    function fileUpload($filePayload){
        $file_path = FILE_LOCATION . CURRENT_TS . "_" . rand(10000, 99999) . "_" . basename($filePayload["name"]);
        $target_file = BASE_PHYSICAL_PATH . '/' . $file_path;
        
        // Check file size
        if ($filePayload["size"] > MAX_FILE_SIZE) {
            retResponse(400, "Sorry, your file is too large: ".(MAX_FILE_SIZE/1024/1024)."MB is max Limit");
        }
        
        if (!move_uploaded_file($filePayload["tmp_name"], $target_file)){
            retResponse(500, "Sorry, there was an error uploading your file, Internal server error", [
                "tmp" => $filePayload["tmp_name"],
                "target" => $target_file
            ]);
        }

        return $file_path;
    }

    function randomPassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*()-+=_';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    function myUrlDecoder($url){
        $url = preg_replace("/%u([0-9a-f]{3,4})/i","&#x\\1;",urldecode($url)); 
        $url = html_entity_decode($url, ENT_SUBSTITUTE,'UTF-8');
        return $url;
    }   