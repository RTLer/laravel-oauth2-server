<?php

return [
    /*
    |--------------------------------------------------------------------------
    | public and private keys
    |--------------------------------------------------------------------------
    |
    | The private key must be kept secret
    | (i.e. out of the web-root of the authorization server).
    | The authorization server also requires the public key.
    | If a passPhrase has been used to generate private key
    | it must be provided to the authorization server.
    | The public key should be distributed to any services
    | (for example resource servers) that validate access tokens.
    |
    */
    'private_key'        => storage_path('oauth2/private.key'),
    'private_key_phrase' => '',
    'public_key'         => storage_path('oauth2/public.key'),

    /*
    |--------------------------------------------------------------------------
    | user verifier
    |--------------------------------------------------------------------------
    |
    | this is the class that use to verify user.
    | it must implement UserVerifierInterface
    |
    */
    'user_verifier' => App\Oauth2\UserVerifier::class,

    /*
    |--------------------------------------------------------------------------
    | database type
    |--------------------------------------------------------------------------
    |
    | it use to set models to make it use mongo or mysql
    | it can be set mongo ro mysql
    |
    */
    'database_type' => 'mysql', // or mongo

    /*
    |--------------------------------------------------------------------------
    | custom bearer
    |--------------------------------------------------------------------------
    |
    | if you don't like the usage of jwt
    | or want to add your custom bearer
    | you can set them here
    |
    */
//    'bearer_token_response' => RTLer\Oauth2\Bearer\BearerTokenResponse::class,
//    'bearer_token_validator' => RTLer\Oauth2\Bearer\BearerTokenValidator::class,


    /*
    |--------------------------------------------------------------------------
    | grants
    |--------------------------------------------------------------------------
    |
    | this part config the active grants.
    | (if the grant config added to this array grant will be activated)
    |
    */
    'grants' => [
        'client_credentials' => [
            'access_token_ttl' => 10,
        ],
        'auth_code' => [
            'auth_code_ttl'     => 60,
            'refresh_token_ttl' => 60,
            'access_token_ttl'  => 10,
        ],
        'password' => [
            'refresh_token_ttl' => 60,
            'access_token_ttl'  => 10,
        ],
        'implicit' => [
            'access_token_ttl' => 10,
        ],
        'refresh_token' => [
            'refresh_token_ttl' => 60,
            'access_token_ttl'  => 10,
        ],
    ],
];
