<?php
    require_once __DIR__.'/src/JWT.php';
    require_once __DIR__.'/src/Key.php';
    require_once __DIR__.'/src/BeforeValidException.php';
    require_once __DIR__.'/src/SignatureInvalidException.php';
    require_once __DIR__.'/src/ExpiredException.php';
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
    
    
    function jwtEncode($data, $secretKey, $algo = 'HS512'){
        $issuedAt   = new DateTimeImmutable();
        $expire     = $issuedAt->modify('+6 minutes')->getTimestamp();      // Add 60 seconds
        $serverName = "jwt-php-router.tanglecoder.com";                     // Retrieved from filtered POST data
        return JWT::encode(
            [
                'iat'  => $issuedAt->getTimestamp(),         // Issued at: time when the token was generated
                'iss'  => $serverName,                       // Issuer
                'nbf'  => $issuedAt->getTimestamp(),         // Not before
                'exp'  => $expire,                           // Expire
                'data' => $data,                             // User Specific Data
            ],
            $secretKey,
            $algo
        );
    }

    function jwtDecode($token, $secretKey, $algo = 'HS512'){
        return JWT::decode($token, new Key($secretKey, $algo));
    }


    // print_r(jwtDecode(jwtEncode(['hie'], 'hello'), 'helloasda'));