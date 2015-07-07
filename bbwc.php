<?php

require_once 'vendor/autoload.php';

date_default_timezone_set("America/New_York");

use Behat\Mink\Mink,
    Behat\Mink\Session,
    Behat\Mink\Driver\GoutteDriver,
    Behat\Mink\Driver\Goutte\Client as GoutteClient;


$startUrl = 'http://www.bbcw.com/product.php?productid=26194';

// init Mink and register sessions
$mink = new Mink(array(
    'goutte1' => new Session(new GoutteDriver(new GoutteClient()))
));

// set the default session name
$mink->setDefaultSessionName('goutte1');

// visit a page
$session = $mink->getSession();
$session->visit($startUrl);

$page = $session->getPage();

// fill username and password fields
$username_field = $page->findField('username');
$password_field = $page->findField('password');

$password_file = 'password.txt';

$myfile = fopen($password_file, "r") or die("Password file not found. Please create a 'password.txt' on the root folder with just the BBWC password on it.");
$password = fread($myfile,filesize($password_file));
fclose($myfile);

$page->fillField('username', 'bbcw@avilastores.com');
$page->fillField('password', $password);

// submit login page
$submit = $page->findButton('Submit');
$submit->click();

// navigate away from the cart and into the product page.
$session->visit($startUrl);

echo $mink->getSession()->getPage()->hasContent("Ibis Arrastia");
