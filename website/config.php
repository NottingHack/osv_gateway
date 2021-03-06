<?php
/**
 * Config File
 *
 * Open Vend System config file. Stores all the variables unique to
 * an installation.
 *
 * @author James Hayward <jhayward1980@gmail.com>
 * @version 1.0
 */

// Root URL
define('ROOT_URL', '/osv/');

// Admin contact, outputs on error messages if things go wrong
$aAdmin = array(
				'name'	=>	'Nottingham Hackspace',
				'email'	=>	'info@nottinghack.org.uk',
				);

// Currency
$aCurrency = array(
				   'code'	=>	'GBP',
				   );


// PayPal settings
$aPayPal = array(
				 'hostname'	=>	'www.sandbox.paypal.com',
				 'business'	=>	'osv_1344176713_biz@purplegecko.co.uk',
				 'token'	=>	'spFWMH-CRNk16eKURRgmDhHRwyJkhanwRi6tM2MCz_WS_mobdQpnYTqFeWq',
				 );
$aPayPal['url'] = 'https://' . $aPayPal['hostname'] . '/cgi-bin/webscr';
?>
