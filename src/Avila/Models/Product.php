<?php
/**
 * Created by PhpStorm.
 * User: nmelo
 * Date: 18/07/15
 * Time: 4:41 PM
 */

function get_property($object) {
    foreach(get_object_vars($object) as $prop) {
        return $prop;
    }
    return null;
}

class Avila_Models_Product {
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

    /**
     * @return mixed
     */
    public function getBbcwId()
    {
        return $this->bbcw_id;
    }

    public static function fromJSON($product_json) {
        $decoded = json_decode($product_json);

        $prod = get_property($decoded);

        $mapper = new JsonMapper();
        $product = $mapper->map($prod, new Avila_Models_Product());
        return $product;
    }
}