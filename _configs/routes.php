<?php

    // rules: left side slash [/] compulsory, right must have side NO slash in route and path both
    registerRoute('/', '/_api/index.php');
    registerRoute('/user/login', '/user/login.php', 'POST', 1);
    registerRoute('/user/register', '/user/register.php', auth:0);
    registerRoute('/user/registera/:id/:table', '/userApi/register.php');