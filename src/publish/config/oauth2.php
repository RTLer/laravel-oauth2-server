<?php
return [
    'private_key' => storage_path('oauth2/private.key'),
    'private_key_phrase' => '',
    'public_key' => storage_path('oauth2/public.key'),
    'user_verifier' => App\Oauth2\UserVerifier::class,
    'grants'=>[
        'client_credentials' => [
            'access_token_ttl' => 10,
        ],
        'auth_code' => [
            'auth_code_ttl' => 60,
            'refresh_token_ttl' => 60,
            'access_token_ttl' => 10,
        ],
        'password' => [
            'refresh_token_ttl' => 60,
            'access_token_ttl' => 10,
        ],
        'implicit' => [
            'access_token_ttl' => 10,
        ],
        'refresh_token' => [
            'refresh_token_ttl' => 60,
            'access_token_ttl' => 10,
        ],
    ],
];