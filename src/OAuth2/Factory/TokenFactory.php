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
        
        $tokenStorageObj = $serviceLocator->get($config['googleglass']['tokenStore']);
        
        if(!$tokenStorageObj instanceof \GoogleGlass\OAuth2\Storage\StorageInterface) {
            throw new ServiceNotFoundException("Provided storage service must implement StorageInterface");
        }
        
        $token = $tokenStorageObj->retrieve();
        
        if(!$token instanceof \OAuth2\Entity\Token) {
            $token = new Token();
        }
        
        return $token;
    }
}