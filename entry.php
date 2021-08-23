<?php

    include_once('configFunctions/functions.php');

    // Adding [/] to Requested URL at the end if not
    if(! ($_SERVER['REQUEST_URI'])[-1] !== '/') $_SERVER['REQUEST_URI'] .= '/';
    $_SERVER['REQUEST_URI'] = ltrim($_SERVER['REQUEST_URI'], '/');
    
    // Trimming Base url [defined in constants] from the Requested URL and converting to array exploding with [/]
    $request = explode('/', str_replace(BASE_URL, "", $_SERVER['REQUEST_URI']));
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    $callRequest = $request[0];

    /* 
        Pair of Key Value Array, containes routes as key, and directory path as value

        Don't Declare Key and value same, it will reveal directory's index
    */
    $routes = [
        "user"=> "userApi/",
        "product"=> "productApi/"
    ];
    
    if(isset($routes[$callRequest])){
        if (file_exists($routes[$callRequest])) include_once( $routes[$callRequest] . 'entry.php' );
        else retResponse(404, 'Route Defined, Folder Not Found');
    }
    else retResponse(404, 'Invalid Route');