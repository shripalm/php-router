<?php
    // Each request except direct URL to the path, redirected to entry.php 
    http_response_code(404);
    echo 'Page not found';
?>