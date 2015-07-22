<?php

require_once "../vendor/autoload.php";
require_once realpath(dirname(__FILE__) . '/../src/Avila/autoload.php');

use \google\appengine\api\mail\Message;

// ###########################################################################

// Order product once
//order_product_from_bbcw($product_id, $amount, $address_book_entry);

class OrderProcessingException extends Exception
{
    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}


/**
 * @param $product_id
 * @param $amount
 * @param $address_book_entry
 * @throws OrderProcessingException
 */
function order_product_from_bbcw($product_id, $amount, $address_book_entry) {

    $generator = new Avila_Generators_BBCW();
    if (! $generator->login() ) {
        throw new OrderProcessingException("Login to BBCW failed");
    }

    if (! $generator->add_product_to_cart($product_id, $amount) ) {
        throw new OrderProcessingException("Adding product to cart failed");
    }

    if (! $generator->checkout_product($address_book_entry) ) {
        throw new OrderProcessingException("Adding product to cart failed");
    }

    if (! $generator->place_order() ) {
        throw new OrderProcessingException("Placing order failed");
    }

    return true;
}


function parseOrder() {

    syslog(LOG_INFO, "Contents of POST: \n " . var_export($_POST, true));

    $order_id = $_POST['entity_id'];
    syslog(LOG_INFO, "Order ID: $order_id");

    // Get shipping address
    $shipping_address = null;
    if($_POST['addresses'][0]["address_type"] === "shipping") {
        $shipping_address = $_POST['addresses'][0];
    }
    elseif($_POST['addresses'][1]["address_type"] === "shipping") {
        $shipping_address = $_POST['addresses'][1];
    }
    else {
        syslog(LOG_INFO, "Shipping address not found: " . $_POST['addresses']);
        return false;
    }

    syslog(LOG_INFO, "Using shipping address: " .  var_export($shipping_address, true));

    // Get Product ID and quantity
    $item_id = $_POST["order_items"][0]["bbcw_id"];
    $quantity = $_POST["order_items"][0]["qty_ordered"];

    if (! is_numeric($item_id)) {
        syslog(LOG_INFO, "Product ID should be a number: $item_id. Failing task.");
        return false;
    }
    if (! is_numeric($quantity)) {
        syslog(LOG_INFO, "Quantity should be a number: $quantity. Failing task.");
        return false;
    }

    $shipping_email = $shipping_address["email"];
    if ($shipping_email == null) {
        syslog(LOG_INFO, "Shipping email not found. Failing task.");
        return false;
    }

    // Get address for shipping
    $address_book_entry = [
        'usertype' => 'C',
        'anonymous' => '',
        'email' => $shipping_email,
        'ship2diff' => 'Y',
        'existing_address' => [
            'S' => '2170'
        ],
        'address_book' => [
            'S' => [
                'id' => '2170',
                'firstname' => $shipping_address["firstname"],
                'lastname' => $shipping_address["lastname"],
                'address' => $shipping_address["street"],
                'address_2' => '',
                'city' => $shipping_address["city"],
                'state' => $shipping_address["region"],
                'country' => $shipping_address["country_id"],
                'zipcode' => $shipping_address["postcode"],
                'phone' => $shipping_address["telephone"],
                'fax' => '',
                'no_address' => '',
            ]
        ],
        'firstname' => $shipping_address["firstname"],
        'lastname' => $shipping_address["lastname"],
        'company' => '',
        'additional_values' => [
            '2' => 'Residential'
        ]
    ];

    return new Avila_Models_BBCWOrder($order_id, $item_id, $quantity, $address_book_entry);
}

function placeOrder($bbcw_order) {
    // Order from BBCW
    try {
        syslog(LOG_INFO, "Starting order for $bbcw_order->amount items with ID: $bbcw_order->product_id \r\n Using the following Shipping info: ". var_export($bbcw_order->address_book_entry, true));
        order_product_from_bbcw($bbcw_order->product_id, $bbcw_order->amount, $bbcw_order->address_book_entry);
        return true;
    }
    catch(OrderProcessingException $e) {
        syslog(LOG_ERR, $e->getMessage());
        return false;
    }
}

/**
 * @param $order
 */
function markOrderAsCompleted($order) {
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

    $params = array('status'=>"processing");
    $result = $api->request('orders/' . $order->getOrderId(), 'PUT', $params);

    echo 'result: <pre>' . print_r(json_decode($result), true) . '</pre>';

    return true;
}

function emailConfirmation($order)
{
    try
    {
        $message = new Message();
        $message->setSender("nmelo.cu@gmail.com");
        $message->addTo("nmelo.cu@gmail.com");
        $message->addTo("ibis@avilastores.com");
        $message->setSubject("[$order->order_id] Order sent: For product $order->product_id.");
        $message->setTextBody("Customer: " . var_export($order->address_book_entry, true));
        $message->send();
    } catch (InvalidArgumentException $e) {
        syslog(LOG_INFO, "Email failed");
        return false;
    }

    return true;
}

$order = parseOrder();
if ($order === false) {
    return false;
}

$success = placeOrder($order);

if($success) {
    $success = markOrderAsCompleted($order);
}

if($success) {
    $success = emailConfirmation($order);
}

