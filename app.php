<?php

require_once 'vendor/autoload.php';
require_once 'models.php';
require_once 'GAEDataStore.php';

date_default_timezone_set("America/New_York");

use google\appengine\api\taskqueue\PushTask;
use google\appengine\api\app_identity\AppIdentityService;

use OAuth\Common\Storage\Exception\TokenNotFoundException;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Http\Uri\UriFactory;
use OAuth\ServiceFactory;
use JonnyW\MagentoOAuth\OAuth1\Service\Magento;

class MagentoAPI
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

//        $this->storage = new Session();
        $this->storage = new GAEDataStore($this->storageKey);
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

class TaskManager {
    function createTask($order) {

        $task = new PushTask('/tasks/order', (array)$order);
        $task->add();
    }
}

if ($_SERVER['APPLICATION_ID'] != "dev~None") {
    $api = new MagentoAPI('http://104.131.73.201',
        '11932204fb41f45e1e3b97bebf341887',
        '7347d53132ce15e74e6b467b4793d4b0', 'Magento');
}
else {
    $api = new MagentoAPI('http://magento2.site',
        '920b324e02330f55c1d53dd19e87c8db',
        'e66d927c017765b607ec0cb72663130b', 'MagentoDev');
}

$task_manager = new TaskManager();

if (isset($_GET['rejected'])) {
    echo '<p>OAuth authentication was cancelled.</p>';
} elseif (isset($_GET['authenticate'])) {
    // get a request token from magento
    $url = $api->getRequestToken();

    header('Location: ' . $url);
} elseif (!empty($_GET['oauth_token'])) {
    $url = $api->getAccessToken();

    header('Location: ' . $url);
} elseif (!empty($_GET['request'])) {

    try {
        if (substr( $_GET['request'], 0, 8 ) === "products") {
            $result = $api->request("products/3");
            echo 'result: <pre>' . print_r(json_decode($result), true) . '</pre>';
        } elseif ($_GET['request'] == "orders") {
            $result = $api->request('orders');
            echo 'result: <pre>' . print_r(json_decode($result), true) . '</pre>';

            $orders = Order::fromJSONArray($result);

            $pending_orders = array();

            foreach ($orders as $order) {
                // Ignore non pending orders
                if ($order->status != "pending") {
                    continue;
                }

                // We don't get Custom Magento Attributes on /orders calls, so we need to
                // get /product for each item on each order and manually set the BBCW_ID
                foreach ($order->order_items as $item) {
//                    $result = $api->request('products?filter[0][attribute]=sku&filter[0][in]=' . $item->sku);
                    $result = $api->request('products/?order=entity_id&filter[0][attribute]=sku&filter[0][in][0]=' . $item->sku);
//                    $result = $api->request('products/' . $item->item_id);

                    syslog(LOG_INFO, $result);

                    if(isset(json_decode($result)->messages)) //Isset also will make sure $content is set
                    {
                        syslog(LOG_INFO, "On order with ID: " . $order->entity_id . "the product with ID:" . $item->item_id . " was not found. Skipping...");
                        break;
                    }

                    // Set BBCW Id for each product
                    $product = Product::fromJSON($result);
                    $item->setBBCW_Id($product->getBbcwId());
                }
                array_push($pending_orders, $order);
            }

            // Create a task to process each pending order independently
            foreach ($pending_orders as $order) {
                $task_manager->createTask($order);
            }
        }
    } catch (TokenNotFoundException $e) {
        // Back to Magento AUTH screen if we don't have a valid token.
        $url = $api->getCurrentURL() . '?authenticate=true';
        header('Location: ' . $url);
    }
} else {
    $url = $api->getCurrentURL() . '?authenticate=true';
    echo '<a href="' . $url . '" title="Authenticate">Authenticate!</a>';
}


