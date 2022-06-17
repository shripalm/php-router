<?php

    require_once(__DIR__.'/_configs/constants.php');
    require_once(__DIR__.'/_configs/functions.php');
    require_once(__DIR__.'/_configs/routes.php');

    $requestedRoute = routeExtractor();    
    $method = $_SERVER['REQUEST_METHOD'];
    $matchedRouteData = routeMatcher($method);
    $fileName = __DIR__ . '/_view' . $matchedRouteData["path"];
    if (! file_exists($fileName)) retResponse(404, "Route Defined, File Not Found - $fileName");
    /* 
        This essentialCall invokes 
            checkContentType --> Every time
            getBearerToken  --> Only if auth is defined as true
        
        Hence, checkContentType invokes every time it should require some content all the time
    */
    essentialCall($matchedRouteData["auth"]);

    /*
        Gets json from the body, means you must pass json every time
        However, you can change it...

        Valid -->
            $data = $_POST
            $data = $_FILES
            $data = $_GET
    */
    $data = getJsonFromBody();
    include_once($fileName);