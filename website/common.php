<?php
/**
 * Common File
 *
 * Open Vend System common file. Loads in all external libraries, sets up
 * template variables and initilises system variables
 *
 * @author James Hayward <jhayward1980@gmail.com>
 * @version 1.0
 */

// Import local settings
require_once('config.php');

define('ROOT_DIR', dirname(__FILE__) . '/');
define('SECURE_DIR', dirname(__FILE__) . '/../../int_secure/');
define('COMMON_DIR', ROOT_DIR . '_common/');
define('PHP_DIR', COMMON_DIR . 'php/');

define('COMMON_URL', ROOT_URL . '_common/');
define('IMG_URL', COMMON_URL . 'images/');
define('JS_URL', COMMON_URL . 'js/');
define('CSS_URL', COMMON_URL . 'css/');

// build up the server address
define('SERVER', $_SERVER["HTTP_HOST"]);
$sHTTP = "http://";
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) {
    $sHTTP = "https://";
}
define('HTTP', $sHTTP . SERVER);


require_once(PHP_DIR . 'smarty.php');
require_once(PHP_DIR . 'functions.php');
require_once(PHP_DIR . 'security.php');


require_once(SECURE_DIR . 'inst_db.php');

require_once(PHP_DIR . 'class.vendcomm.php');
$oVendComm = new VendComm();

?>
