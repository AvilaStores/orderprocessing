<?php

$google_api_config = [
    'application-id' => 'magento-orders',
    'service-account-name' => '855043483396-d6a2uqac94d0dgmgbmoak6ocvtc0v444@developer.gserviceaccount.com',
    'private-key' => file_get_contents('../credentials/magento-orders-bcb6b4e5eec6.p12'),
    'dataset-id' => 'magento-orders'
];

Avila_AppEngine_DatastoreService::setInstance(new Avila_AppEngine_DatastoreService($google_api_config));

use OAuth\Common\Token\TokenInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Storage\Exception\TokenNotFoundException;
use OAuth\OAuth1\Token\StdOAuth1Token;

/**
 * Stores a token in a PHP session.
 */
class Avila_OAuth_Common_Storage_GAEDataStore implements TokenStorageInterface
{
    public $storageKey;

    public function __construct($storageKey) {
        $this->storageKey = $storageKey;
    }

    public function getStorageKey()
    {
        return $this->storageKey;
    }

    public function retrieveAccessToken($service)
    {
        if ($this->hasAccessToken($service)) {
            $kname = sha1($service);

            $token_model_fetched = Avila_AppEngine_OAuth1TokenModel::fetch_by_name($kname)[0];

            $token = new StdOAuth1Token();
            $token->setRequestToken($token_model_fetched->getRequestToken());
            $token->setRequestTokenSecret($token_model_fetched->getRequestSecret());
            $token->setAccessToken($token_model_fetched->getAccessToken());
            $token->setAccessTokenSecret($token_model_fetched->getAccessSecret());

            $token->setEndOfLife(StdOAuth1Token::EOL_NEVER_EXPIRES);
            return $token;
        }

        throw new TokenNotFoundException('Token not found in session, are you sure you stored it?');
    }

    public function storeAccessToken($service, TokenInterface $token)
    {
        $token_model = new Avila_AppEngine_OAuth1TokenModel($service, $token->getRequestToken(), $token->getRequestTokenSecret(), $token->getAccessToken(), $token->getAccessTokenSecret());
        $token_model->put();

        // allow chaining
        return $this;
    }

    public function hasAccessToken($service)
    {
        $kname = sha1($service);
        $token_model_fetched = Avila_AppEngine_OAuth1TokenModel::fetch_by_name($kname)[0];
        return $token_model_fetched != null;
    }

    public function clearToken($service)
    {
        // delete entity with this service key from the datastore

        // allow chaining
        return $this;
    }

    public function clearAllTokens()
    {
        // delete all tokens from the datatore

        // allow chaining
        return $this;
    }

    public function storeAuthorizationState($service, $state)
    {

        // allow chaining
        return $this;
    }

    public function hasAuthorizationState($service)
    {
        return false;
    }

    public function retrieveAuthorizationState($service)
    {
        return null;

//        throw new AuthorizationStateNotFoundException('State not found in session, are you sure you stored it?');
    }

    public function clearAuthorizationState($service)
    {
        // allow chaining
        return $this;
    }

    public function clearAllAuthorizationStates()
    {

        // allow chaining
        return $this;
    }

    public function __destruct()
    {

    }
}
