<?php


class BaseTest extends PHPUnit_Framework_TestCase
{
    public $client;

    public function __construct() {
        parent::__construct();
        $this->client = $this->getMagentoClient();
    }
    protected function getHost() {
        return 'http://magento2.site';

    }
    protected function getConsumerKey() {
        return '920b324e02330f55c1d53dd19e87c8db';

    }
    protected function getConsumerSecret() {
        return 'e66d927c017765b607ec0cb72663130b';
    }
    protected function getStorageKey() {
        return 'MagentoDev';
    }

    protected function getUri()
    {
        $mockUri = $this->getMock('OAuth\Common\Http\Uri\Uri');

        return $mockUri;
    }

    protected function getCredentials()
    {
        $mockCredentials = $this->getMock('OAuth\Common\Consumer\CredentialsInterface');

        return $mockCredentials;
    }

    protected function getMagentoClient() {

        $host           = $this->getHost();
        $consumerKey    = $this->getConsumerKey();
        $consumerSecret = $this->getConsumerSecret();
        $storageKey     = $this->getStorageKey();
        $uri            = $this->getUri();
        $credentials    = $this->getCredentials();

        $magentoClient = new Avila_Magento_API_Client($host, $consumerKey, $consumerSecret, $storageKey, $uri, $credentials);
        assert($magentoClient != null);
        return $magentoClient;
    }

}
