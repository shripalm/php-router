<?php

    // $request defined in root's entry.php
    $callRequest = $request[1];

    /* 
        Defining New routes for php files

        key is a file name [PHP file name], Where the main code takes place
        value contains method and auth
            if you give auth as false, it will be able to accessible without auth token
    */
    $routes = [
        "register"=> ["method" => "GET", "auth" => false],
        "login"=> ["method" => "POST", "auth" => true]
    ];

    if (isset($routes[$callRequest])) {
        if (! file_exists($callRequest . '.php')) retResponse(404, 'Route Defined, File Not Found');
        $currMethod = $routes[$callRequest]['method'];
        $currAuth = $routes[$callRequest]['auth'];
        if(!(strtoupper($method) == $currMethod)) retResponse(405, 'Invalid Method');

        /* 
            This essentialCall invokes 
                checkContentType --> Every time
                getBearerToken  --> Only if auth is defined as true
            
            Hence, checkContentType invokes every time it should require some content all the time
        */
        essentialCall($currMethod, $currAuth);

        /*
            Gets json from the body, means you must pass json every time
            However, you can change it...

            Valid -->
                $data = $_POST
                $data = $_FILES
                $data = $_GET
        */
        $data = getJsonFromBody();
        include_once($callRequest . '.php');
    }
    else retResponse(404, 'Invalid Route');