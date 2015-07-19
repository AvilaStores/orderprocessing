<?php


class BaseTest extends PHPUnit_Framework_TestCase
{
    public $client;

    public function __construct() {
        parent::__construct();
        $this->client = $this->getMagentoClient();
    }
    protected function getHost() {
        return 'http://104.131.73.201';

    }
    protected function getConsumerKey() {
        return '11932204fb41f45e1e3b97bebf341887';
    }
    protected function getConsumerSecret() {
        return '7347d53132ce15e74e6b467b4793d4b0';
    }
    protected function getStorageKey() {
        return 'Magento';
    }

    protected function getMagentoClient() {

        $host           = $this->getHost();
        $consumerKey    = $this->getConsumerKey();
        $consumerSecret = $this->getConsumerSecret();
        $storageKey     = $this->getStorageKey();

        $magentoClient = new Client($host, $consumerKey, $consumerSecret, $storageKey);
        return $magentoClient;
    }

}
