<?php

namespace OAuth2\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use OAuth2\Entity\Token;

class TokenFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        
        $tokenStorageObj = $serviceLocator->get($config['oauth2']['tokenStore']);
        
        if(!$tokenStorageObj instanceof \OAuth2\Storage\StorageInterface) {
            throw new ServiceNotFoundException("Provided storage service must implement StorageInterface");
        }
        
        $token = $tokenStorageObj->retrieve();
        
        if(!$token instanceof \OAuth2\Entity\Token) {
        
            if(!class_exists($config['oauth2']['tokenEntity'])) {
                throw new \RuntimeException("Specified token entity could not be located");
            }
            
            $token = new $config['oauth2']['tokenEntity'];
        }
        
        return $token;
    }
}