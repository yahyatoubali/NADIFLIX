<?php

/*
    Database Configuration
*/
define('DB_HOST', 'sql113.epizy.com'); // Your mySQL Host (usually Localhost)
define('DB_USER', 'epiz_28069717'); // Your mySQL Databse username
define('DB_PASS', 'wRxtWg0RpGm8HQs'); // Your mySQL Databse Password
define('DB_NAME', 'epiz_28069717_nadi'); // The database where you have dumped the included sql file


/*
    Set main domain
    Example : http://my-super-domain.com
*/
define('MAIN_DOMAIN','http://nadiflix.koora-fire.ml');


/*
    Application firewall
    val : true/false
*/
define('FIREWALL', true);


/*
    Application debug mode
    val : true/false
*/
define('DEBUG', false);


/*
    If you install script on sub folder, insert that folder name here
    example : cdn1.mydomain.com/loadbalancer
    define('PROOT', '/loadbalancer');
*/
define('PROOT', '');


/*
    Application root directory
*/
define('ROOT',dirname(__FILE__,2));


define('STREAM_DEBUG', false);
define('CURL_MAX_SPEED', 250 * 1024);
define('GDRIVE_API', 'AIzaSyD43F1N3Wvj2vfqpgyImQgv81eQylP-bJk');
define('_SEC_LOCK', '#$wel');
define('GDRIVE_IDENTIFY','__001');


$config = [];

function dnd($data)
{
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    die();
}