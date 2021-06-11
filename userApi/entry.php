<?php

    $callRequest = $request[1];

    $routes = [
        "register"=> ["method" => "GET", "auth" => false],
        "login"=> ["method" => "POST", "auth" => true]
    ];

    if (isset($routes[$callRequest])) {
        $currMethod = $routes[$callRequest]['method'];
        $currAuth = $routes[$callRequest]['auth'];
        if(!(strtoupper($method) == $currMethod)) retResponse(405, 'Invalid Method');
        essentialCall($currMethod, $currAuth);
        $data = getJsonFromBody();
        include_once($callRequest . '.php');
    }
    else retResponse(404, 'Invalid Route');