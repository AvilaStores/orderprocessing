<?php

require_once 'vendor/autoload.php';

use GuzzleHttp\Client as Client;

class BBCW_OrderGenerator {

    public $client;
    public $base_uri = 'http://www.bbcw.com';
    public $session_cookie;
    public $site_password;

    function __construct() {
        // Use shared client, preserve cookies
        $this->client = new Client([
                'base_uri' => $this->base_uri,
                'cookies' => true,
                'timeout'  => 2.0,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.132 Safari/537.36'
                ]
            ]
        );

        // Initialize the session
        $this->init();
    }

    function __destruct() {
        unset($this->client);
        unset($this->base_uri);
    }

    private function init() {
        // Issue a simple get to generate a session cookie
        $response = $this->client->get('/');

        if ($response->getStatusCode() != 200) {
            // Do something meaningful here.
            return false;
        }
        else {
            // Initial GET worked, we should have a session cookie now.
            $this->session_cookie = $this->get_session_cookie();

            print "Session Cookie: " . $this->session_cookie;
        }

        // Read password from file and store in class
        $this->site_password = $this->get_password();
    }

    public function get_session_cookie() {
        $cookie_jar = $this->client->getConfig("cookies");

        foreach($cookie_jar->getIterator() as $set_cookie) {
            if($set_cookie->getName() === "xid_eb442") {
                return $set_cookie->getValue();
            }
        }
    }

    private function get_password() {
        $password_file = 'password.txt';
        $myfile = fopen($password_file, "r") or die("Password file not found. Please create a 'password.txt' on the root folder with just the BBWC password on it.");
        $password = fread($myfile,filesize($password_file));
        fclose($myfile);

        return $password;
    }

    public function login() {

        // Login -- Body arguments:
        // xid_eb442    : [session cookie]
        // is_remember  : [can be null]
        // mode=login   : (static value)
        // username     : bbcw%40avilastores.com
        // password     : P1V2bbcw

        $response = $this->client->post('login.php',[
            'form_params' => [
                'xid_eb442' => $this->session_cookie,
                'is_remember' => '',
                'mode' => 'login',
                'username' => 'bbcw@avilastores.com',
                'password' => $this->site_password
            ]
        ]);

        if ($response->getStatusCode() != 200) {
            // Do something meaningful here.
            return false;
        }
        else {
            // Login worked, our session should be authenticated now.
            return true;
        }
    }

    public function add_product_to_cart($product_id, $amount) {
        // Add to cart -- Body arguments:
        // mode         : add (static value)
        // cat          : [can be null]
        // page         : [can be null]
        // productid    : argument
        // amount       : argument

        $response = $this->client->post('cart.php',[
            'form_params' => [
                'mode' => 'add',
                'cat' => '',
                'page' => '',
                'productid' => $product_id,
                'amount' => $amount
            ]
        ]);

        if ($response->getStatusCode() != 200) {
            // Do something meaningful here.
            return false;
        }
        else {
            // Add product to cart worked
            return true;
        }
    }

    public function checkout_product($address_book_entry) {
        // Add to cart -- Body arguments:
        // usertype:C
        // anonymous:
        // email:bbcw@avilastores.com
        // ship2diff:Y
        // existing_address[S]:2170
        // address_book[S][id]:2170
        // address_book[S][firstname]:Ibis1
        // address_book[S][lastname]:Arrastia
        // address_book[S][address]:7625 Parkview Way
        // address_book[S][address_2]:
        // address_book[S][city]:Coral Springs
        // address_book[S][state]:FL
        // address_book[S][country]:US
        // address_book[S][zipcode]:33065
        // address_book[S][phone]:9542052615
        // address_book[S][fax]:
        // address_book[S][no_address]:
        // firstname:Ibis
        // lastname:Arrastia
        // company:Avila Stores LLC
        // additional_values[2]:Residential


        $response = $this->client->post('/cart.php?mode=checkout',[
            'form_params' => $address_book_entry
        ]);

        if ($response->getStatusCode() != 200) {
            // Do something meaningful here.
            return false;
        }
        else {
            // Add product to cart worked
            print $response->getBody();
            return true;
        }
    }

    public function place_order() {

        // Place order -- Body arguments:
        // 'paymentid' : '4'
        // 'action' : 'place_order'
        // 'xid_eb442' : [session cookie]
        // 'payment_method' : 'Credit+Card+On+File'
        // 'Customer_Notes' : ''''
        // 'accept_terms' : 'Y'

        // # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
        // EXECUTING THIS STEP COSTS MONEY. ORDERS MADE HERE ARE NOT CANCELLABLE.
        // ONLY REMOVE THE exit CLAUSE BELOW IF YOU KNOW WHAT YOU ARE DOING
        // # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
        exit;

        $response = $this->client->post('/payment/payment_offline.php',[
            'form_params' => [
                'paymentid' => '4',
                'action' => 'place_order',
                'xid_eb442' => $this->session_cookie,
                'payment_method' => 'Credit+Card+On+File',
                'Customer_Notes' => '',
                'accept_terms' => 'Y'
            ]
        ]);

        if ($response->getStatusCode() != 200) {
            // Do something meaningful here.
            return false;
        }
        else {
            // Place order worked
            return true;
        }
    }
}

// ###########################################################################

// Order product once
//order_product_from_bbcw($product_id, $amount, $address_book_entry);

function order_product_from_bbcw($product_id, $amount, $address_book_entry) {

    $generator = new BBCW_OrderGenerator();
    if (! $generator->login() ) {
        print "Login failed";
        exit;
    }

    if (! $generator->add_product_to_cart($product_id, $amount) ) {
        print "Adding product to cart failed";
        exit;
    }

    if (! $generator->checkout_product($address_book_entry) ) {
        print "Adding product to cart failed";
        exit;
    }

    if (! $generator->place_order() ) {
        print "Placing order failed";
        exit;
    }
}
