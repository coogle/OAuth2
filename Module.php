<?php

namespace OAuth2;

use Zend\Mvc\MvcEvent;
use CoogleLib\ServiceManager\ServiceLocatorFactory;

class Module
{
    public function getModuleDependencies()
    {
        return array('CoogleLib');
    }

    public function onBootstrap(MvcEvent $e)
    {
        ServiceLocatorFactory::setInstance($e->getApplication()->getServiceManager());
    }
    
    public function getAutoloaderConfig()
    {
        return array(
                'Zend\Loader\ClassMapAutoloader' => array(
                        __DIR__ . '/autoload_classmap.php'
                ),
                'Zend\Loader\StandardAutoloader' => array(
                        'namespaces' => array(
                                __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
                        )
                )
        );
    }
    
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'OAuth2\Api\Client' => 'OAuth2\Factory\Http\Api\ClientFactory',
                'OAuth2\Http\Client' => 'OAuth2\Factory\Http\ClientFactory',
                'OAuth2\Client' => 'OAuth2\Factory\ClientFactory',
                'OAuth2\Storage\Session' => 'OAuth2\Storage\Session',
                'OAuth2\TokenStore' => 'OAuth2\Storage\StorageFactory',
                'OAuth2\Token' => 'OAuth2\Factory\TokenFactory',
                'OAuth2\AuthUri' => 'OAuth2\Factory\AuthUriFactory'
            ),
        );
    }
}
