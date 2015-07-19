<?php

use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Http\Uri\UriFactory;
use OAuth\ServiceFactory;
use JonnyW\MagentoOAuth\OAuth1\Service\Magento;

class Avila_Magento_API_Client
{
    public $applicationUrl;
    public $consumerKey;
    public $consumerSecret;
    public $storageKey;

    public $storage;
    public $uriFactory;
    public $serviceFactory;

    public $currentUri;

    public $magento_service;

    function __construct($host, $consumerKey, $consumerSecret, $storageKey)
    {
        $this->applicationUrl = $host;
        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;
        $this->storageKey = $storageKey;

        $this->storage = new Avila_OAuth_Common_Storage_GAEDataStore($this->storageKey);
        $this->uriFactory= new UriFactory();

        $this->serviceFactory = new ServiceFactory();

        $this->serviceFactory->registerService('magento', 'JonnyW\MagentoOAuth\OAuth1\Service\Magento');

        $this->currentUri = $this->uriFactory->createFromSuperGlobalArray($_SERVER);
        $this->currentUri->setQuery('');

        $baseUri = $this->uriFactory->createFromAbsolute($this->applicationUrl);

        $credentials = new Credentials(
            $this->consumerKey,
            $this->consumerSecret,
            $this->currentUri->getAbsoluteUri()
        );

        $this->magentoService = $this->serviceFactory->createService('magento', $credentials, $this->storage, array(), $baseUri);
        $this->magentoService->setAuthorizationEndpoint(Magento::AUTHORIZATION_ENDPOINT_ADMIN);
    }

    public function getRequestToken() {
        $token = $this->magentoService->requestRequestToken();
        $url = $this->magentoService->getAuthorizationUri(array('oauth_token' => $token->getRequestToken()));
        return $url;
    }

    public function getAccessToken() {
        // Get the stored request token
        $token = $this->storage->retrieveAccessToken($this->storageKey);

        $this->magentoService->requestAccessToken(
            $_GET['oauth_token'],
            $_GET['oauth_verifier'],
            $token->getRequestTokenSecret()
        );

        $url = $this->currentUri->getRelativeUri() . "?request=orders";
        return $url;
    }

    public function request($endpoint) {
        $result = $this->magentoService->request('/api/rest/' . $endpoint, 'GET', null, array('Accept' => '*/*'));
        return $result;
    }

    public function getCurrentURL(){
        return $this->currentUri->getRelativeUri();
    }
}