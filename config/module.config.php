<?php

return array(
    'controller_plugins' => array(
    ),
    'router' => array(
        'routes' => array(
            'oauth2-callback' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/oauth2/callback',
                    'defaults' => array(
                        'controller' => 'OAuth2\Controller\Callback',
                        'action'     => 'OAuth2Callback',
                    ),
                )
            ),
            'oauth2-unauth' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/oauth2/unauth',
                    'defaults' => array(
                        'controller' => 'OAuth2\Controller\Callback',
                        'action' => 'unauth'
                    )
                )
            )
        )
    ),
    'oauth2' => array(
        'tokenStore' => 'OAuth2\Storage\Session',
        'auth' => array(
            "client_id" => null,
            "client_secret" => null,
            "auth_uri" => null,
            "token_uri" => null,
            "redirect_uri" => null,
            "credentials_in_request_body" => true,
            'scopes' => array(),
        ),
        'httpClient' => array(
            'maxredirects'    => 5,
            'strictredirects' => false,
            'useragent'       => 'GoogleGlass Client',
            'timeout'         => 10,
            'adapter'         => 'Zend\Http\Client\Adapter\Curl',
            'httpversion'     => \Zend\Http\Request::VERSION_11,
            'storeresponse'   => true,
            'keepalive'       => false,
            'outputstream'    => false,
            'encodecookies'   => true,
            'argseparator'    => null,
            'rfc3986strict'   => false,
            'sslcapath'       => __DIR__ . "/certs/"
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'OAuth2\Controller\Callback' => 'OAuth2\Controller\CallbackController',
        ),
    )
);