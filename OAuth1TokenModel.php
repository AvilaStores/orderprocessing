<?php

require_once 'Model.php';

/**
 * Model class for token objects
 */
class OAuth1TokenModel extends Model {

    const TOKEN_MODEL_KIND = 'OAuth1Token';
    const SERVICE_KEY_NAME = 'service_key';
    const REQUEST_TOKEN_NAME = 'request_token';
    const REQUEST_SECRET_NAME = 'request_secret';
    const ACCESS_TOKEN_NAME = 'access_token';
    const ACCESS_SECRET_NAME = 'access_secret';

    private $service_key;
    private $request_token;
    private $request_secret;
    private $access_token;
    private $access_secret;

    public function __construct($service_key, $request_token, $request_secret, $access_token, $access_secret) {
        parent::__construct();
        $this->key_name = sha1($service_key);

        $this->service_key = $service_key;
        $this->request_token = $request_token;
        $this->request_secret = $request_secret;
        $this->access_token = $access_token;
        $this->access_secret = $access_secret;
    }

    public function getServiceKey()
    {
        return $this->service_key;
    }

    public function getRequestToken()
    {
        return $this->request_token;
    }

    public function getRequestSecret()
    {
        return $this->request_secret;
    }

    public function getAccessToken()
    {
        return $this->access_token;
    }

    public function getAccessSecret()
    {
        return $this->access_secret;
    }

    protected static function getKindName() {
        return self::TOKEN_MODEL_KIND;
    }

    /**
    * Generate the entity property map from the feed object fields.
    */
    protected function getKindProperties() {
        $property_map = [];

        $property_map[self::SERVICE_KEY_NAME] = parent::createStringProperty($this->service_key, true);
        $property_map[self::REQUEST_TOKEN_NAME] = parent::createStringProperty($this->request_token, true);
        $property_map[self::REQUEST_SECRET_NAME] = parent::createStringProperty($this->request_secret, true);
        $property_map[self::ACCESS_TOKEN_NAME] = parent::createStringProperty($this->access_token, true);
        $property_map[self::ACCESS_SECRET_NAME] = parent::createStringProperty($this->access_secret, true);
        return $property_map;
    }


    /**
    * Fetch a feed object given its feed URL.  If get a cache miss, fetch from the Datastore.
    * @param $service_key service key.
    */
    public static function get($service_key) {
        $mc = new Memcache();
        $key = self::getCacheKey($service_key);
        $response = $mc->get($key);
        if ($response) {
          return [$response];
        }

        $query = parent::createQuery(self::TOKEN_MODEL_KIND);
        $token_filter = parent::createStringFilter(self::SERVICE_KEY_NAME, $service_key);
        $filter = parent::createCompositeFilter([$token_filter]);
        $query->setFilter($filter);
        $results = parent::executeQuery($query);
        $extracted = self::extractQueryResults($results);
        return $extracted;
    }

    /**
    * This method will be called after a Datastore put.
    */
    protected function onItemWrite() {
        $mc = new Memcache();
        try {
          $key = self::getCacheKey($this->service_key);
          $mc->add($key, $this, 0, 120);
        }
        catch (Google_Cache_Exception $ex) {
          syslog(LOG_WARNING, "in onItemWrite: memcache exception");
        }
    }

    /**
    * This method will be called prior to a datastore delete
    */
    protected function beforeItemDelete() {
        $mc = new Memcache();
        $key = self::getCacheKey($this->service_key);
        $mc->delete($key);
    }

    /**
    * Extract the results of a Datastore query into FeedModel objects
    * @param $results Datastore query results
    */
    protected static function extractQueryResults($results) {
        $query_results = [];
        foreach($results as $result) {
          $id = @$result['entity']['key']['path'][0]['id'];
          $key_name = @$result['entity']['key']['path'][0]['name'];
          $props = $result['entity']['properties'];
          $service_key = $props[self::SERVICE_KEY_NAME]->getStringValue();
          $request_token = $props[self::REQUEST_TOKEN_NAME]->getStringValue();
          $request_secret = $props[self::REQUEST_SECRET_NAME]->getStringValue();
          $access_token = $props[self::ACCESS_TOKEN_NAME]->getStringValue();
          $access_secret = $props[self::ACCESS_SECRET_NAME]->getStringValue();

          $token_model = new OAuth1TokenModel($service_key, $request_token, $request_secret, $access_token, $access_secret);
          $token_model->setKeyId($id);
          $token_model->setKeyName($key_name);
          // Cache this read feed.
          $token_model->onItemWrite();

          $query_results[] = $token_model;
        }
        return $query_results;
    }

    private static function getCacheKey($service_key) {
        return sprintf("%s_%s", self::TOKEN_MODEL_KIND, sha1($service_key));
    }
}
