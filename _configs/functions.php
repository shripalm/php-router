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
            foreach ($routeToCheck as $rtc_key => $rtc_value){
                if($rtc_value[0] === ':') {
                    $GLOBALS['route_params'][ltrim($rtc_value, ':')] = $route[$rtc_key];
                }
                else{
                    if($rtc_value !== $route[$rtc_key]){
                        $success = false;
                        break;
                    }
                }
            }
            if($success) {
                $matchedRoute = $value;
                $matchedRoute['route_params'] = $GLOBALS['route_params'];
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