<?php

namespace OAuth2\Factory\Http;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Http\Client;

class ClientFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        
        $client = new Client();
        $client->setOptions($config['oauth2']['httpClient']);
        
        return $client;
    }
}