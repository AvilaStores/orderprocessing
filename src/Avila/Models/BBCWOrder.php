<?php


class Avila_Models_BBCWOrder {
    public $order_id;
    public $product_id;
    public $amount;
    public $address_book_entry;
    public $status;

    function __construct($product_id, $amount, $address_book_entry) {
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
}
