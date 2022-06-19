<?php

    require_once(__DIR__.'/_configs/constants.php');
    require_once(__DIR__.'/_configs/functions.php');
    require_once(__DIR__.'/_configs/routes.php');

    $requestedRoute = routeExtractor();    
    $method = $_SERVER['REQUEST_METHOD'];
    $matchedRouteData = routeMatcher($method);
    $fileName = __DIR__ . '/_view' . $matchedRouteData["path"];
    if (! file_exists($fileName)) retResponse(404, "Route Defined, File Not Found - $fileName");
    essentialCall($matchedRouteData["auth"]);
    if($matchedRouteData["auth"]) $jwtdata = fetchJWTData($bearer_token['encoded']);
    $data = getJsonFromBody();
    include_once($fileName);