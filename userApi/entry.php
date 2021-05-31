<?php

    $callRequest = $request[1];

    $routes = [
        "register"=> "register.php",
        "login"=> "login.php"
    ];

    if(isset($routes[$callRequest])) include_once( $routes[$callRequest] );
    else header('Location: '.ERR_404_ROUTE);