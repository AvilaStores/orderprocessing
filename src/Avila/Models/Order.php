<?php


class Avila_Models_Order
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
            $product = $mapper->map($product, new Avila_Models_Product());
            array_push($items, $product);
        }
        $this->order_items = $items;
    }

    public function setOrderComments($order_comments) {
        $items = array();
        $mapper = new JsonMapper();
        foreach ($order_comments as $comment) {
            $item = $mapper->map($comment, new Avila_Models_Comment());
            array_push($items, $item);
        }
        $this->order_comments = $items;
    }

    public function setAddresses($addresses) {
        $items = array();
        $mapper = new JsonMapper();
        foreach ($addresses as $address) {
            $item = $mapper->map($address, new Avila_Models_PhysicalAddress());
            array_push($items, $item);
        }
        $this->addresses = $items;
    }

    public static function fromJSONArray($orders_json) {
        $orders = array();

        $decoded = json_decode($orders_json);
        foreach ($decoded as $order) {
            $mapper = new JsonMapper();
            $order = $mapper->map($order, new Avila_Models_Order());
            array_push($orders, $order);
        }
        return $orders;
    }
}
