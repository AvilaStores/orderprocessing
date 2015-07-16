<?php

class Product {
    public $item_id;
    public $parent_item_id;
    public $sku;
    public $bbcw_id;
    public $name;
    public $qty_canceled;
    public $qty_invoiced;
    public $qty_ordered;
    public $qty_refunded;
    public $qty_shipped;
    public $price;
    public $base_price;
    public $original_price;
    public $base_original_price;
    public $tax_percent;
    public $tax_amount;
    public $base_tax_amount;
    public $discount_amount;
    public $base_discount_amount;
    public $row_total;
    public $base_row_total;
    public $price_incl_tax;
    public $base_price_incl_tax;
    public $row_total_incl_tax;
    public $base_row_total_incl_tax;

    public function setBBCW_Id($bbcw_id) {
        $this->bbcw_id = $bbcw_id;
    }
}

class PhysicalAddress {
    public $region;
    public $postcode;
    public $lastname;
    public $street;
    public $city;
    public $email;
    public $telephone;
    public $country_id;
    public $firstname;
    public $address_type;
    public $prefix;
    public $middlename;
    public $suffix;
    public $company;
}

class Comment {
    public $is_customer_notified;
    public $is_visible_on_front;
    public $comment;
    public $status;
    public $created_at;
}

class Order
{
    public $entity_id;
    public $status;
    public $coupon_code;
    public $shipping_description;
    public $customer_id;
    public $base_discount_amount;
    public $base_grand_total;
    public $base_shipping_amount;
    public $base_shipping_tax_amount;
    public $base_subtotal;
    public $base_tax_amount;
    public $base_total_paid;
    public $base_total_refunded;
    public $discount_amount;
    public $grand_total;
    public $shipping_amount;
    public $shipping_tax_amount;
    public $store_to_order_rate;
    public $subtotal;
    public $tax_amount;
    public $total_paid;
    public $total_refunded;
    public $base_shipping_discount_amount;
    public $base_subtotal_incl_tax;
    public $base_total_due;
    public $shipping_discount_amount;
    public $subtotal_incl_tax;
    public $total_due;
    public $increment_id;
    public $base_currency_code;
    public $discount_description;
    public $remote_ip;
    public $store_currency_code;
    public $store_name;
    public $created_at;
    public $shipping_incl_tax;
    public $payment_method;
    public $gift_message_from;
    public $gift_message_to;
    public $gift_message_body;
    public $tax_name;
    public $tax_rate;
    public $addresses; // Array of Addresses
    public $order_items; // Array of Products
    public $order_comments; // Array of Comments

    function __construct()
    {

    }

    function __destruct()
    {
        unset($this->client);
        unset($this->base_uri);
    }

    public function setOrderItems($order_items) {
        $items = array();
        $mapper = new JsonMapper();
        foreach ($order_items as $product) {
            $product = $mapper->map($product, new Product());
            array_push($items, $product);
        }
        $this->order_items = $items;
    }

    public function setOrderComments($order_comments) {
        $items = array();
        $mapper = new JsonMapper();
        foreach ($order_comments as $comment) {
            $item = $mapper->map($comment, new Comment());
            array_push($items, $item);
        }
        $this->order_comments = $items;
    }

    public function setAddresses($addresses) {
        $items = array();
        $mapper = new JsonMapper();
        foreach ($addresses as $address) {
            $item = $mapper->map($address, new PhysicalAddress());
            array_push($items, $item);
        }
        $this->addresses = $items;
    }
}
