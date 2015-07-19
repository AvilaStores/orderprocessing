<?php

require_once "../vendor/autoload.php";
require_once realpath(dirname(__FILE__) . '/../src/Avila/autoload.php');

use google\appengine\api\taskqueue\PushTask;
use google\appengine\api\app_identity\AppIdentityService;

use OAuth\Common\Storage\Exception\TokenNotFoundException;

// Create a Magento API instance depending on the environment
if ($_SERVER['APPLICATION_ID'] != "dev~None") {
    $api = new Avila_Magento_API_Client('http://104.131.73.201',
        '11932204fb41f45e1e3b97bebf341887',
        '7347d53132ce15e74e6b467b4793d4b0', 'Magento');
}
else {
    $api = new Avila_Magento_API_Client('http://magento2.site',
        '920b324e02330f55c1d53dd19e87c8db',
        'e66d927c017765b607ec0cb72663130b', 'MagentoDev');
}

// Handle request
if (isset($_GET['rejected'])) {
    echo '<p>OAuth authentication was cancelled.</p>';
} elseif (isset($_GET['authenticate'])) {
    // get a request token from magento
    $url = $api->getRequestToken();

    syslog(LOG_INFO, "Got request token. Redirecting user to: ". $url);
    header('Location: ' . $url);
} elseif (!empty($_GET['oauth_token'])) {
    $url = $api->getAccessToken();

    syslog(LOG_INFO, "Got access token. Redirecting user to: ". $url);
    header('Location: ' . $url);
} elseif (!empty($_GET['request'])) {

    try {
        if (substr( $_GET['request'], 0, 8 ) === "products") {
            $result = $api->request("products/3");
            echo 'result: <pre>' . print_r(json_decode($result), true) . '</pre>';
        } elseif ($_GET['request'] == "orders") {
            $result = $api->request('orders');
            echo 'result: <pre>' . print_r(json_decode($result), true) . '</pre>';

            $orders = Avila_Models_Order::fromJSONArray($result);

            syslog(LOG_INFO, "Orders fetched from Magento API:");
            syslog(LOG_INFO, var_export($orders, true));

            $pending_orders = array();

            foreach ($orders as $order) {
                // Ignore non pending orders
                if ($order->status != "pending") {
                    continue;
                }

                // We don't get Custom Magento Attributes on /orders calls, so we need to
                // get /product for each item on each order and manually set the BBCW_ID
                foreach ($order->order_items as $item) {
                    $result = $api->request('products/?order=entity_id&filter[0][attribute]=sku&filter[0][in][0]=' . $item->sku);

                    if(isset(json_decode($result)->messages)) //Isset also will make sure $content is set
                    {
                        syslog(LOG_INFO, "On order with ID: " . $order->entity_id . " the product with ID: " . $item->item_id . " was not found. Skipping...");
                        break;
                    }

                    // Set BBCW Id for each product
                    $product = Avila_Models_Product::fromJSON($result);
                    $item->setBBCW_Id($product->getBbcwId());
                }
                array_push($pending_orders, $order);
            }

            // Create a task to process each pending order independently
            foreach ($pending_orders as $order) {
                syslog(LOG_INFO, "Creating a task with order:");
                syslog(LOG_INFO, var_export($order, true));

                $task = new PushTask('/tasks/order', (array)$order);
                $task->add();
            }
        }
    } catch (TokenNotFoundException $e) {
        // Back to Magento AUTH screen if we don't have a valid token.
        $url = $api->getCurrentURL() . '?authenticate=true';
        syslog(LOG_INFO, "Token not found. Redirecting user to: ". $url);
        header('Location: ' . $url);
    }
} else {
    $url = $api->getCurrentURL() . '?authenticate=true';
    echo '<a href="' . $url . '" title="Authenticate">Authenticate!</a>';
}


