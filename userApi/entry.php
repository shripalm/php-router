<?php

    $callRequest = $request[1];

    $routes = [
        "register"=> "POST",
        "login"=> "POST"
    ];

    if (isset($routes[$callRequest])) {
        if(!(strtoupper($method) == $routes[$callRequest])) retResponse(405, 'Invalid Method');
        essentialCall();
        $data = getJsonFromBody();
        include_once($callRequest . '.php');
    }
    else retResponse(404, 'Invalid Route');