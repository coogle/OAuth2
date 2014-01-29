<?php

namespace OAuth2\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\Application;
use Zend\Http\Response;

class AuthUriFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $config = $config['oauth2'];
        
        if(is_null($config['auth']['redirect_uri'])) {
            $router = $serviceLocator->get('Router');
        
            $requestUri = $router->getRequestUri();
            $requestUri->setQuery(null);
        
            $OAuthUrl = $router->assemble(array(), array(
                    'name' => 'oauth2-callback',
                    'force_canonical' => true,
                    'uri' => $requestUri
            ));
            
        } else {
            $OAuthUrl = $config['auth']['redirect_uri'];
        }
        
        $response = $serviceLocator->get('Application')->getResponse();
        
        $response->setStatusCode(Response::STATUS_CODE_302)
                 ->getHeaders()
                 ->addHeaderLine('Location', $OAuthUrl);
        
        return $response;
    }
}