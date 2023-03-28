<?php
    define('BASE_URL', '/');

    define('FILE_LOCATION', 'uploads/');
    define('MAX_FILE_SIZE', 1 * 1024 * 1024);
    
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_DB', 'test');

    define('JWT_SECRET', 'Bwtey45aaIII=');
    define('JWT_ALGO', 'HS512');
    
    define('SENDGRID_ID', '');
    define('SENDGRID_MAIL', '');

    $date = new DateTime();
    $currentTS = $date->getTimestamp();
    define('CURRENT_TS', $currentTS);

?>