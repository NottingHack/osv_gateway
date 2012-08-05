<?php
require_once('common.php');

$pp_hostname = "www.sandbox.paypal.com"; 
 
// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-synch';
 
$tx_token = $_GET['tx'];
$auth_token = $aPayPal['token'];
$req .= "&tx=$tx_token&at=$auth_token";
 
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://$pp_hostname/cgi-bin/webscr");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
//set cacert.pem verisign certificate path in curl using 'CURLOPT_CAINFO' field here,
//if your server does not bundled with default verisign certificates.
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host: $pp_hostname"));
$res = curl_exec($ch);
curl_close($ch);
 
if(!$res) {
    //HTTP ERROR
}
else{
     // parse the data
    $lines = explode("\n", $res);
    $keyarray = array();
    if (strcmp ($lines[0], "SUCCESS") == 0) {
        for ($i=1; $i<count($lines);$i++){
        	list($key,$val) = explode("=", $lines[$i]);
        	$keyarray[urldecode($key)] = urldecode($val);
    	}
   }
    var_dump($keyarray);
}

?>
