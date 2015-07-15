<?php

require_once 'vendor/autoload.php';
require_once 'models.php';

date_default_timezone_set("America/New_York");

use google\appengine\api\taskqueue\PushTask;

use OAuth\Common\Storage\Exception\TokenNotFoundException;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Http\Uri\UriFactory;
use OAuth\ServiceFactory;
use JonnyW\MagentoOAuth\OAuth1\Service\Magento;


function getOrders() {

    $applicationUrl     = 'http://magento2.site';
    $consumerKey        = '920b324e02330f55c1d53dd19e87c8db';
    $consumerSecret     = 'e66d927c017765b607ec0cb72663130b';

    $storage        = new Session();
    $uriFactory     = new UriFactory();

    $serviceFactory = new ServiceFactory();
    $serviceFactory->registerService('magento', 'JonnyW\MagentoOAuth\OAuth1\Service\Magento');

    $currentUri = $uriFactory->createFromSuperGlobalArray($_SERVER);
    $currentUri->setQuery('');

    $baseUri = $uriFactory->createFromAbsolute($applicationUrl);

    $credentials = new Credentials(
        $consumerKey,
        $consumerSecret,
        $currentUri->getAbsoluteUri()
    );

    $magentoService = $serviceFactory->createService('magento', $credentials, $storage, array(), $baseUri);
    $magentoService->setAuthorizationEndpoint(Magento::AUTHORIZATION_ENDPOINT_ADMIN);

    if(isset($_GET['rejected'])) {
        echo '<p>OAuth authentication was cancelled.</p>';
    }
    elseif(isset($_GET['authenticate'])) {
        // get a request token from magento

        $token     = $magentoService->requestRequestToken();
        $url     = $magentoService->getAuthorizationUri(array('oauth_token' => $token->getRequestToken()));

        header('Location: ' . $url);
    }
    elseif(!empty($_GET['oauth_token'])) {

        // Get the stored request token
        $token = $storage->retrieveAccessToken('Magento');


        // Exchange the request token for an access token
        // Caution: The request access token ovewrites the request token here.
        // Assume $storage has an access token from now on
        $magentoService->requestAccessToken(
            $_GET['oauth_token'],
            $_GET['oauth_verifier'],
            $token->getRequestTokenSecret()
        );

        $url = $currentUri->getRelativeUri() . "?request=products";
        header('Location: ' . $url);
    }
    elseif(!empty($_GET['request'])){

        try {
            if ($_GET['request'] == "products") {
                $result = $magentoService->request('/api/rest/products', 'GET', null, array('Accept' => '*/*'));
                echo 'result: <pre>' . print_r(json_decode($result), true) . '</pre>';
            }
            elseif ($_GET['request'] == "orders") {
                $result = $magentoService->request('/api/rest/orders', 'GET', null, array('Accept' => '*/*'));
                echo 'result: <pre>' . print_r(json_decode($result), true) . '</pre>';
//                createTasks($result);
            }
        }
        catch(TokenNotFoundException $e) {
            $url = $currentUri->getRelativeUri() . '?authenticate=true';
            header('Location: ' . $url);
        }
    }
    else {
        $url = $currentUri->getRelativeUri() . '?authenticate=true';

        echo '<a href="' . $url . '" title="Authenticate">Authenticate!</a>';
    }
}

function createTasks($orders_json) {
    $orders = array();

    $decoded = json_decode($orders_json);
    foreach ($decoded as $order) {
        $mapper = new JsonMapper();
        $order = $mapper->map($order, new Order());
        array_push($orders, $order);
    }

    foreach ($orders as $order) {
        $task = new PushTask('/tasks/order', (array)$order);
        $task->add();
    }
}

getOrders();

// TODO: Get bbcw_id attribute from Magento API
//$array = '{"1":{"entity_id":"1","status":"pending","coupon_code":null,"shipping_description":"Flat Rate - Fixed","customer_id":"1","base_discount_amount":"0.0000","base_grand_total":"105.0000","base_shipping_amount":"5.0000","base_shipping_tax_amount":"0.0000","base_subtotal":"100.0000","base_tax_amount":"0.0000","base_total_paid":null,"base_total_refunded":null,"discount_amount":"0.0000","grand_total":"105.0000","shipping_amount":"5.0000","shipping_tax_amount":"0.0000","store_to_order_rate":"1.0000","subtotal":"100.0000","tax_amount":"0.0000","total_paid":null,"total_refunded":null,"base_shipping_discount_amount":"0.0000","base_subtotal_incl_tax":"100.0000","base_total_due":null,"shipping_discount_amount":"0.0000","subtotal_incl_tax":"100.0000","total_due":null,"increment_id":"100000001","base_currency_code":"USD","discount_description":null,"remote_ip":"127.0.0.1","store_currency_code":"USD","store_name":"Main Website\nMain Website Store\nDefault Store View","created_at":"2015-07-14 18:07:27","shipping_incl_tax":"5.0000","payment_method":"checkmo","gift_message_from":null,"gift_message_to":null,"gift_message_body":null,"tax_name":null,"tax_rate":null,"addresses":[{"region":"Florida","postcode":"33134","lastname":"Melo","street":"514 Santander Ave\nApt 1","city":"Coral Gables","email":"nmelo.cu@gmail.com","telephone":"3057755707","country_id":"US","firstname":"Nelson","address_type":"billing","prefix":null,"middlename":null,"suffix":null,"company":null},{"region":"Florida","postcode":"33134","lastname":"Melo","street":"514 Santander Ave\nApt 1","city":"Coral Gables","email":"nmelo.cu@gmail.com","telephone":"3057755707","country_id":"US","firstname":"Nelson","address_type":"shipping","prefix":null,"middlename":null,"suffix":null,"company":null}],"order_items":[{"item_id":"1","parent_item_id":null,"sku":"Batman","name":"Batman","qty_canceled":"0.0000","qty_invoiced":"0.0000","qty_ordered":"1.0000","qty_refunded":"0.0000","qty_shipped":"0.0000","price":"100.0000","base_price":"100.0000","original_price":"100.0000","base_original_price":"100.0000","tax_percent":"0.0000","tax_amount":"0.0000","base_tax_amount":"0.0000","discount_amount":"0.0000","base_discount_amount":"0.0000","row_total":"100.0000","base_row_total":"100.0000","price_incl_tax":"100.0000","base_price_incl_tax":"100.0000","row_total_incl_tax":"100.0000","base_row_total_incl_tax":"100.0000"}],"order_comments":[{"is_customer_notified":"1","is_visible_on_front":"0","comment":null,"status":"pending","created_at":"2015-07-14 18:07:27"}]},"2":{"entity_id":"2","status":"pending","coupon_code":null,"shipping_description":"Flat Rate - Fixed","customer_id":"1","base_discount_amount":"0.0000","base_grand_total":"105.0000","base_shipping_amount":"5.0000","base_shipping_tax_amount":"0.0000","base_subtotal":"100.0000","base_tax_amount":"0.0000","base_total_paid":null,"base_total_refunded":null,"discount_amount":"0.0000","grand_total":"105.0000","shipping_amount":"5.0000","shipping_tax_amount":"0.0000","store_to_order_rate":"1.0000","subtotal":"100.0000","tax_amount":"0.0000","total_paid":null,"total_refunded":null,"base_shipping_discount_amount":"0.0000","base_subtotal_incl_tax":"100.0000","base_total_due":null,"shipping_discount_amount":"0.0000","subtotal_incl_tax":"100.0000","total_due":null,"increment_id":"100000002","base_currency_code":"USD","discount_description":null,"remote_ip":"127.0.0.1","store_currency_code":"USD","store_name":"Main Website\nMain Website Store\nDefault Store View","created_at":"2015-07-14 18:09:33","shipping_incl_tax":"5.0000","payment_method":"checkmo","gift_message_from":null,"gift_message_to":null,"gift_message_body":null,"tax_name":null,"tax_rate":null,"addresses":[{"region":"Florida","postcode":"33134","lastname":"Melo","street":"514 Santander Ave\nApt 1","city":"Coral Gables","email":"nmelo.cu@gmail.com","telephone":"3057755707","country_id":"US","firstname":"Nelson","address_type":"billing","prefix":null,"middlename":null,"suffix":null,"company":null},{"region":"Florida","postcode":"33134","lastname":"Melo","street":"514 Santander Ave\nApt 1","city":"Coral Gables","email":"nmelo.cu@gmail.com","telephone":"3057755707","country_id":"US","firstname":"Nelson","address_type":"shipping","prefix":null,"middlename":null,"suffix":null,"company":null}],"order_items":[{"item_id":"2","parent_item_id":null,"sku":"Batman","name":"Batman","qty_canceled":"0.0000","qty_invoiced":"0.0000","qty_ordered":"1.0000","qty_refunded":"0.0000","qty_shipped":"0.0000","price":"100.0000","base_price":"100.0000","original_price":"100.0000","base_original_price":"100.0000","tax_percent":"0.0000","tax_amount":"0.0000","base_tax_amount":"0.0000","discount_amount":"0.0000","base_discount_amount":"0.0000","row_total":"100.0000","base_row_total":"100.0000","price_incl_tax":"100.0000","base_price_incl_tax":"100.0000","row_total_incl_tax":"100.0000","base_row_total_incl_tax":"100.0000"}],"order_comments":[{"is_customer_notified":"1","is_visible_on_front":"0","comment":null,"status":"pending","created_at":"2015-07-14 18:09:34"}]}}';
//createTasks($array);


