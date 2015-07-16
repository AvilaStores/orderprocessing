<?php

require_once 'vendor/autoload.php';

require_once 'models.php';
require_once 'order.php';

date_default_timezone_set("America/New_York");

function executeTask() {

    $shipping_address = null;
    if($_POST['addresses'][0]["address_type"] === "shipping") {
        $shipping_address = $_POST['addresses'][0];
    }
    elseif($_POST['addresses'][1]["address_type"] === "shipping") {
        $shipping_address = $_POST['addresses'][1];
    }
    else {
        syslog(LOG_INFO, "Shipping address not found: " . $_POST['addresses']);
        return;
    }

    $item_id = $_POST["order_items"][0]["bbcw_id"];
    $quantity = $_POST["order_items"][0]["qty_ordered"];

    if (! is_numeric($item_id)) {
        syslog(LOG_INFO, "Product ID should be a number: " . $item_id . ". Failing task.");
        return;
    }
    if (! is_numeric($quantity)) {
        syslog(LOG_INFO, "Quantity should be a number: " . $quantity . ". Failing task.");
        return;
    }

    $address_book_entry = [
        'usertype' => 'C',
        'anonymous' => '',
        'email' => $shipping_address["email"],
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

    order_product_from_bbcw($item_id, $quantity, $address_book_entry);
}

executeTask();


