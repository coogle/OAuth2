<?php

namespace OAuth2\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Log\Logger;
use Zend\Loader\Exception\SecurityException;
use Zend\Uri\UriFactory;
use Zend\Json\Json;
use OAuth2\Entity\Token;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use OAuth2\OAuth2Events;

class CallbackController extends AbstractActionController
{
    use \OAuth2\Log\LoggerTrait;
    
    public function setEventManager(\Zend\EventManager\EventManagerInterface $events)
    {
        parent::setEventManager($events);
        $events->addIdentifiers(OAuth2Events::EVENT_IDENTIFIER);
        return $this;
    }
    
    protected function doRefreshToken(Token $token)
    {
        $oAuthClient = $this->getServiceLocator()->get('OAuth2\Client');
        $params = $oAuthClient->getRequest()->getPost()->toArray();
        
        if(is_null($token->getRefreshToken())) {
            return $this->doRequestToken();
        }
        
        $postParams = array(
                'refresh_token' => $token->getRefreshToken(),
                'client_id' => $params['client_id'],
                'client_secret' => $params['client_secret'],
                'grant_type' => 'refresh_token'
        );
        
        $oAuthClient->setParameterPost($postParams);
        
        $response = $oAuthClient->send();
        $tokenData = Json::decode($response->getBody(), Json::TYPE_ARRAY);
        
        $this->processOauth2Token($tokenData);
        
        return $this->redirect()->toUrl('/');
    }
    
    protected function doRequestToken()
    {
        $config = $this->getServiceLocator()->get('Config');
        $config = $config['oauth2'];
        
        if(is_null($config['auth']['redirect_uri'])) {
            $router = $this->getServiceLocator()->get('Router');
        
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
        
        $uri = UriFactory::factory($config['auth']['auth_uri']);
        $uri->setQuery(array(
            'response_type' => 'code',
            'client_id' => $config['auth']['client_id'],
            'redirect_uri' => $OAuthUrl,
            'scope' => implode(' ', $config['auth']['scopes']),
            'state' => '',
            'access_type' => 'offline',
            'approval_prompt' => 'auto',
            'include_granted_scopes' => 'true'
        ));
        
        return $this->redirect()->toUrl($uri->__toString());
    }
    
    public function OAuth2CallbackAction()
    {
        $config = $this->getServiceLocator()->get('Config');
        $config = $config['oauth2'];
        
        switch(true) {
            case is_null($config['auth']['client_id']):
            case is_null($config['auth']['client_secret']):
                throw new \RuntimeException("You must configure the OAuth2 module"); 
        }
        
        $tokenStorageObj = $this->getServiceLocator()->get($config['tokenStore']);
        
        if(!$tokenStorageObj instanceof \OAuth2\Storage\StorageInterface) {
            throw new ServiceNotFoundException("Provided storage service must implement StorageInterface");
        }
        
        if(empty($_GET)) {
            
            $token = $tokenStorageObj->retrieve();
            
            if($token instanceof \OAuth2\Entity\Token)
            {
                $this->logEvent("Refreshing existing token");
                return $this->doRefreshToken($token);
            }
            
            $this->logEvent("Requesting new Access Token for user");
            return $this->doRequestToken();
        }
        
        if($this->getRequest()->getQuery('error', false)) {
             $this->logEvent("Error authenticating: {$this->getRequest()->getQuery('error')}", Logger::ERR);
             throw new \RuntimeException("Error Authenticating");
        }
        
        $code = $this->getRequest()->getQuery('code', false);
        
        if(!$code) {
            $this->logEvent("Did not receive code from authentication server as expected during OAuth2", Logger::ERR);
            throw new \RuntimeException("Did not receive code as expected");
        }
        
        $oAuthClient = $this->getServiceLocator()->get('OAuth2\Client');
        $oAuthClient->getRequest()->getPost()->set('code', $code);
        
        $response = $oAuthClient->send();
        
        $tokenData = Json::decode($response->getBody(), Json::TYPE_ARRAY);
        
        $this->processOauth2Token($tokenData);
        
        return $this->redirect()->toUrl('/');
    }
    
    protected function processOauth2Token(array $tokenData)
    {
        $tokenStorageObj = $this->getServiceLocator()->get('OAuth2\TokenStore');
        
        if(!$tokenStorageObj instanceof \OAuth2\Storage\StorageInterface) {
            throw new ServiceNotFoundException("Provided storage service must implement StorageInterface");
        }
        
        if(isset($tokenData['error'])) {
            $this->logEvent("Failed to get access token from OAuth2: {$tokenData['error']}", Logger::ERR);
            throw new \RuntimeException("Failed to get access token");
        }
        
        $token = clone $this->getServiceLocator()->get('OAuth2\Token');
        
        $tokenStorageObj->store($token->fromArrayResult($tokenData));
        
        $this->getEventManager()->trigger(OAuth2Events::EVENT_NEW_AUTH_TOKEN, null, array('token' => $token));
        
    }
    
    public function unauthAction()
    {
        $tokenStorageObj = $this->getServiceLocator()->get('OAuth2\TokenStore');
        $tokenStorageObj->destroy();
        
        return $this->redirect()->toUrl('/');
    }
    
}