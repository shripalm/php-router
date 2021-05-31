<?php

    include_once('configFunctions/constants.php');

    $request = explode('/', str_replace(BASE_URL, "", $_SERVER['REQUEST_URI']));
    $method = $_SERVER['REQUEST_METHOD'];
    
    $callRequest = $request[0];

    $routes = [
        "404"=> "error404/",
        "user"=> "userApi/",
        "product"=> "productApi/"
    ];
    
    if(isset($routes[$callRequest])) include_once( $routes[$callRequest] . 'entry.php' );
    else header('Location: '.ERR_404_ROUTE);