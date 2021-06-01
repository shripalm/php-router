<?php

    include_once('configFunctions/functions.php');

    if(! ($_SERVER['REQUEST_URI'])[-1] !== '/') $_SERVER['REQUEST_URI'] .= '/';
    $request = explode('/', str_replace(BASE_URL, "", $_SERVER['REQUEST_URI']));
    $method = $_SERVER['REQUEST_METHOD'];
    
    $callRequest = $request[0];

    $routes = [
        "user"=> "userApi/",
        "product"=> "productApi/"
    ];
    
    if(isset($routes[$callRequest])) include_once( $routes[$callRequest] . 'entry.php' );
    else retResponse(404, 'Invalid Route');