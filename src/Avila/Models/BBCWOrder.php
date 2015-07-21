<?php


class Avila_Models_BBCWOrder {
    public $order_id;
    public $product_id;
    public $amount;
    public $address_book_entry;
    public $status;

    function __construct($order_id, $product_id, $amount, $address_book_entry) {
        $this->order_id = $order_id;
        $this->product_id = $product_id;
        $this->amount = $amount;
        $this->address_book_entry = $address_book_entry;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->order_id;
    }

    /**
     * @param mixed $order_id
     */
    public function setOrderId($order_id)
    {
        $this->order_id = $order_id;
    }
}
