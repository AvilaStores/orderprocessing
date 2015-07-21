<?php


class TestAuthenticateWithMagento extends BaseTest {

    /**
     * Test Magento API Authenticates with test credentials
     *
     * @return void
     */
    public function testMagentoGetsRequestToken() {

        $url = $this->client->getRequestToken();
        assert($url != null);
    }


    /*
 *  * * * * * * * * * * * * * * * * * * * * * * * *
 *  Sequence of HTTP calls to order from BBCW
 *  * * * * * * * * * * * * * * * * * * * * * * * *
 *
 *  1. Get BBCW and store Session Cookie (xid_eb442):
 *
 *  GET / HTTP/1.1
 *  User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.132 Safari/537.36
 *  Cookie: xid_eb442=78a180b1628c1ff0ee0f6e433b13a409; store_language=en
 *  Host: www.bbcw.com
 *  Connection: close
 *
 *  2. Login with Session Cookie and Credentials:
 *
 *  POST /login.php HTTP/1.1
 *  Cookie: xid_eb442=78a180b1628c1ff0ee0f6e433b13a409
 *  X-Requested-With: XMLHttpRequest
 *  User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.132 Safari/537.36
 *  Origin: http://www.bbcw.com
 *  Host: www.bbcw.com
 *  Referer: http://www.bbcw.com/home.php
 *  Content-Type: application/x-www-form-urlencoded
 *  Connection: close
 *  Content-Length: 116
 *
 *  xid_eb442=0177d1f3a19e765e1d4ac39bc687afa4&is_remember=&mode=login&username=bbcw%40avilastores.com&password=P1V2bbcw
 *
 *  3. Add an item (or more) to the cart:
 *
 *  POST /cart.php HTTP/1.1
 *  Cookie: xid_eb442=78a180b1628c1ff0ee0f6e433b13a409
 *  X-Requested-With: XMLHttpRequest
 *  User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.132 Safari/537.36
 *  Origin: http://www.bbcw.com
 *  Host: www.bbcw.com
 *  Referer: http://www.bbcw.com/product.php?productid=26194&cat=252&page=1
 *  Content-Type: application/x-www-form-urlencoded
 *  Connection: close
 *  Content-Length: 44
 *
 *  mode=add&productid=26194&cat=&page=&amount=1
 *
 *  4. Update address:
 *
 *  POST /cart.php?mode=checkout HTTP/1.1
 *  Cookie: xid_eb442=78a180b1628c1ff0ee0f6e433b13a409
 *  X-Requested-With: XMLHttpRequest
 *  User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.132 Safari/537.36
 *  Origin: http://www.bbcw.com
 *  Host: www.bbcw.com
 *  Referer: http://www.bbcw.com/cart.php?mode=checkout
 *  Content-Type: application/x-www-form-urlencoded
 *  Connection: close
 *  Content-Length: 652
 *
 *  usertype=C&anonymous=&email=bbcw%40avilastores.com&ship2diff=Y&existing_address%5BS%5D=2170&address_book%5BS%5D%5Bid%5D=2170&address_book%5BS%5D%5Bfirstname%5D=Ibis&address_book%5BS%5D%5Blastname%5D=Arrastia&address_book%5BS%5D%5Baddress%5D=7625+Parkview+Way&address_book%5BS%5D%5Baddress_2%5D=&address_book%5BS%5D%5Bcity%5D=Coral+Springs&address_book%5BS%5D%5Bstate%5D=FL&address_book%5BS%5D%5Bcountry%5D=US&address_book%5BS%5D%5Bzipcode%5D=33065&address_book%5BS%5D%5Bphone%5D=9542052615&address_book%5BS%5D%5Bfax%5D=&address_book%5BS%5D%5Bno_address%5D=&firstname=Ibis&lastname=Arrastia&company=Avila+Stores+LLC&additional_values%5B2%5D=Residential+
 *
 *  5. Place order:
 *
 *  POST /payment/payment_offline.php HTTP/1.1
 *  Cookie: xid_eb442=78a180b1628c1ff0ee0f6e433b13a409
 *  User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.132 Safari/537.36
 *  Origin: http://www.bbcw.com
 *  Host: www.bbcw.com
 *  Referer: http://www.bbcw.com/cart.php?mode=checkout
 *  Content-Type: application/x-www-form-urlencoded
 *  Connection: close
 *  Content-Length: 139
 *
 *  paymentid=4&action=place_order&xid_eb442=78a180b1628c1ff0ee0f6e433b13a409&payment_method=Credit+Card+On+File&Customer_Notes=&accept_terms=Y
*/

//syslog(LOG_INFO, "hello world");

    // 1 result //$result = '{"entity_id":"1","status":"pending","coupon_code":null,"shipping_description":"Flat Rate - Fixed","customer_id":"1","base_discount_amount":"0.0000","base_grand_total":"105.0000","base_shipping_amount":"5.0000","base_shipping_tax_amount":"0.0000","base_subtotal":"100.0000","base_tax_amount":"0.0000","base_total_paid":null,"base_total_refunded":null,"discount_amount":"0.0000","grand_total":"105.0000","shipping_amount":"5.0000","shipping_tax_amount":"0.0000","store_to_order_rate":"1.0000","subtotal":"100.0000","tax_amount":"0.0000","total_paid":null,"total_refunded":null,"base_shipping_discount_amount":"0.0000","base_subtotal_incl_tax":"100.0000","base_total_due":null,"shipping_discount_amount":"0.0000","subtotal_incl_tax":"100.0000","total_due":null,"increment_id":"100000001","base_currency_code":"USD","discount_description":null,"remote_ip":"127.0.0.1","store_currency_code":"USD","store_name":"Main Website\nMain Website Store\nDefault Store View","created_at":"2015-07-14 18:07:27","shipping_incl_tax":"5.0000","payment_method":"checkmo","gift_message_from":null,"gift_message_to":null,"gift_message_body":null,"tax_name":null,"tax_rate":null,"addresses":[{"region":"Florida","postcode":"33134","lastname":"Melo","street":"514 Santander Ave\nApt 1","city":"Coral Gables","email":"nmelo.cu@gmail.com","telephone":"3057755707","country_id":"US","firstname":"Nelson","address_type":"billing","prefix":null,"middlename":null,"suffix":null,"company":null},{"region":"Florida","postcode":"33134","lastname":"Melo","street":"514 Santander Ave\nApt 1","city":"Coral Gables","email":"nmelo.cu@gmail.com","telephone":"3057755707","country_id":"US","firstname":"Nelson","address_type":"shipping","prefix":null,"middlename":null,"suffix":null,"company":null}],"order_items":[{"item_id":"1","parent_item_id":null,"sku":"Batman","name":"Batman","qty_canceled":"0.0000","qty_invoiced":"0.0000","qty_ordered":"1.0000","qty_refunded":"0.0000","qty_shipped":"0.0000","price":"100.0000","base_price":"100.0000","original_price":"100.0000","base_original_price":"100.0000","tax_percent":"0.0000","tax_amount":"0.0000","base_tax_amount":"0.0000","discount_amount":"0.0000","base_discount_amount":"0.0000","row_total":"100.0000","base_row_total":"100.0000","price_incl_tax":"100.0000","base_price_incl_tax":"100.0000","row_total_incl_tax":"100.0000","base_row_total_incl_tax":"100.0000"}],"order_comments":[{"is_customer_notified":"1","is_visible_on_front":"0","comment":null,"status":"pending","created_at":"2015-07-14 18:07:27"}]}';
    // 2 results //$array = '{"1":{"entity_id":"1","status":"pending","coupon_code":null,"shipping_description":"Flat Rate - Fixed","customer_id":"1","base_discount_amount":"0.0000","base_grand_total":"105.0000","base_shipping_amount":"5.0000","base_shipping_tax_amount":"0.0000","base_subtotal":"100.0000","base_tax_amount":"0.0000","base_total_paid":null,"base_total_refunded":null,"discount_amount":"0.0000","grand_total":"105.0000","shipping_amount":"5.0000","shipping_tax_amount":"0.0000","store_to_order_rate":"1.0000","subtotal":"100.0000","tax_amount":"0.0000","total_paid":null,"total_refunded":null,"base_shipping_discount_amount":"0.0000","base_subtotal_incl_tax":"100.0000","base_total_due":null,"shipping_discount_amount":"0.0000","subtotal_incl_tax":"100.0000","total_due":null,"increment_id":"100000001","base_currency_code":"USD","discount_description":null,"remote_ip":"127.0.0.1","store_currency_code":"USD","store_name":"Main Website\nMain Website Store\nDefault Store View","created_at":"2015-07-14 18:07:27","shipping_incl_tax":"5.0000","payment_method":"checkmo","gift_message_from":null,"gift_message_to":null,"gift_message_body":null,"tax_name":null,"tax_rate":null,"addresses":[{"region":"Florida","postcode":"33134","lastname":"Melo","street":"514 Santander Ave\nApt 1","city":"Coral Gables","email":"nmelo.cu@gmail.com","telephone":"3057755707","country_id":"US","firstname":"Nelson","address_type":"billing","prefix":null,"middlename":null,"suffix":null,"company":null},{"region":"Florida","postcode":"33134","lastname":"Melo","street":"514 Santander Ave\nApt 1","city":"Coral Gables","email":"nmelo.cu@gmail.com","telephone":"3057755707","country_id":"US","firstname":"Nelson","address_type":"shipping","prefix":null,"middlename":null,"suffix":null,"company":null}],"order_items":[{"item_id":"1","parent_item_id":null,"sku":"Batman","name":"Batman","qty_canceled":"0.0000","qty_invoiced":"0.0000","qty_ordered":"1.0000","qty_refunded":"0.0000","qty_shipped":"0.0000","price":"100.0000","base_price":"100.0000","original_price":"100.0000","base_original_price":"100.0000","tax_percent":"0.0000","tax_amount":"0.0000","base_tax_amount":"0.0000","discount_amount":"0.0000","base_discount_amount":"0.0000","row_total":"100.0000","base_row_total":"100.0000","price_incl_tax":"100.0000","base_price_incl_tax":"100.0000","row_total_incl_tax":"100.0000","base_row_total_incl_tax":"100.0000"}],"order_comments":[{"is_customer_notified":"1","is_visible_on_front":"0","comment":null,"status":"pending","created_at":"2015-07-14 18:07:27"}]},"2":{"entity_id":"2","status":"pending","coupon_code":null,"shipping_description":"Flat Rate - Fixed","customer_id":"1","base_discount_amount":"0.0000","base_grand_total":"105.0000","base_shipping_amount":"5.0000","base_shipping_tax_amount":"0.0000","base_subtotal":"100.0000","base_tax_amount":"0.0000","base_total_paid":null,"base_total_refunded":null,"discount_amount":"0.0000","grand_total":"105.0000","shipping_amount":"5.0000","shipping_tax_amount":"0.0000","store_to_order_rate":"1.0000","subtotal":"100.0000","tax_amount":"0.0000","total_paid":null,"total_refunded":null,"base_shipping_discount_amount":"0.0000","base_subtotal_incl_tax":"100.0000","base_total_due":null,"shipping_discount_amount":"0.0000","subtotal_incl_tax":"100.0000","total_due":null,"increment_id":"100000002","base_currency_code":"USD","discount_description":null,"remote_ip":"127.0.0.1","store_currency_code":"USD","store_name":"Main Website\nMain Website Store\nDefault Store View","created_at":"2015-07-14 18:09:33","shipping_incl_tax":"5.0000","payment_method":"checkmo","gift_message_from":null,"gift_message_to":null,"gift_message_body":null,"tax_name":null,"tax_rate":null,"addresses":[{"region":"Florida","postcode":"33134","lastname":"Melo","street":"514 Santander Ave\nApt 1","city":"Coral Gables","email":"nmelo.cu@gmail.com","telephone":"3057755707","country_id":"US","firstname":"Nelson","address_type":"billing","prefix":null,"middlename":null,"suffix":null,"company":null},{"region":"Florida","postcode":"33134","lastname":"Melo","street":"514 Santander Ave\nApt 1","city":"Coral Gables","email":"nmelo.cu@gmail.com","telephone":"3057755707","country_id":"US","firstname":"Nelson","address_type":"shipping","prefix":null,"middlename":null,"suffix":null,"company":null}],"order_items":[{"item_id":"2","parent_item_id":null,"sku":"Batman","name":"Batman","qty_canceled":"0.0000","qty_invoiced":"0.0000","qty_ordered":"1.0000","qty_refunded":"0.0000","qty_shipped":"0.0000","price":"100.0000","base_price":"100.0000","original_price":"100.0000","base_original_price":"100.0000","tax_percent":"0.0000","tax_amount":"0.0000","base_tax_amount":"0.0000","discount_amount":"0.0000","base_discount_amount":"0.0000","row_total":"100.0000","base_row_total":"100.0000","price_incl_tax":"100.0000","base_price_incl_tax":"100.0000","row_total_incl_tax":"100.0000","base_row_total_incl_tax":"100.0000"}],"order_comments":[{"is_customer_notified":"1","is_visible_on_front":"0","comment":null,"status":"pending","created_at":"2015-07-14 18:09:34"}]}}';


    //$address_book_entry = [
    //    'usertype' => 'C',
    //    'anonymous' => '',
    //    'email' => 'bbcw@avilastores.com',
    //    'ship2diff' => 'Y',
    //    'existing_address' => [
    //        'S' => '2170'
    //    ],
    //    'address_book' => [
    //        'S' => [
    //            'id' => '2170',
    //            'firstname' => 'Ibis1',
    //            'lastname' => 'Arrastia',
    //            'address' => '7625 Parkview Way',
    //            'address_2' => '',
    //            'city' => 'Coral Springs',
    //            'state' => 'FL',
    //            'country' => 'US',
    //            'zipcode' => '33065',
    //            'phone' => '9542052615',
    //            'fax' => '',
    //            'no_address' => '',
    //        ]
    //    ],
    //    'firstname' => 'Ibis',
    //    'lastname' => 'Arrastia',
    //    'company' => 'Avila Stores LLC',
    //    'additional_values' => [
    //        '2' => 'Residential'
    //    ]
    //];

    //$product_id = "26194";
    //$amount = 1;

}
