<?php
require_once('common.php');

if (isset($_GET['tx'])) {
	$bPayPal = true;
	$sPayPalTx = $_GET['tx'];
}
elseif (isset($_GET['govend'])) {
	$bPayPal = false;
	$iTrans = intval($_GET['govend']);
}
else {
	errorDie("Fatal Error", "VM001");
}

if ($bPayPal == true) {
	// Get transaction details from paypal and verify
	
	// read the post from PayPal system and add 'cmd'
	$sReq = 'cmd=_notify-synch';
	$sReq .= '&tx=' . $sPayPalTx . '&at=' . $aPayPal['token'];
	
	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_URL, $aPayPal['url']);
	curl_setopt($oCurl, CURLOPT_POST, 1);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, $sReq);
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, 1);
	//set cacert.pem verisign certificate path in curl using 'CURLOPT_CAINFO' field here,
	//if your server does not bundled with default verisign certificates.
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, array('Host: ' . $aPayPal['hostname']));
	$sRes = curl_exec($oCurl);
	curl_close($oCurl);
	
	if(!$sRes) {
		//HTTP ERROR
		refund_msg('HTTP001');
	}
	else{
		// parse the data
		$aLines = explode("\n", $sRes);
		$aTransData = array();
		if (strcmp($aLines[0], "SUCCESS") == 0) {
			for ($i = 1; $i < count($aLines); $i++){
				if ($aLines[$i] != "") {
					list($sKey, $sVal) = explode("=", $aLines[$i]);
					$aTransData[urldecode($sKey)] = urldecode($sVal);
				}
			}
		}
		else {
			//HTTP ERROR
			var_dump($aLines);
			refund_msg('HTTP002');
		}
	}
	
	// ok, let's verify the transaction
	// first, let's get the details of the transaction paypal says we are dealing with
	$oResult = $oInstDB->query('call sp_get_transaction(' . $aTransData['custom'] . ')');
	if ($oResult->num_rows > 0) {
		$aTrans = $oResult->fetch_assoc();
	}
	else {
		refund_msg('VM002');
	}
	$oInstDB->next_result();
	$oResult->close();
	
	// now let's do some checks
	// we assume the transaction is ok
	$bTrans = true;
	
	// Check the machine ID and hopper ID
	list($iMachine, $iHopper) = explode("-", $aTransData['item_number']);
	if ($iMachine != $aTrans['machine_id']) {
		$bTrans = false;
		$sError = "VM011";
	}
	if ($iHopper != $aTrans['hopper_id']) {
		$bTrans = false;
		$sError = "VM012";
	}
	
	// check product details
	if (($aTransData['mc_gross'] * 100) != $aTrans['price']) {
		$bTrans = false;
		$sError = "VM013";
	}
	if ($aTransData['item_name'] != $aTrans['name']) {
		$bTrans = false;
		$sError = "VM014";
	}
	
	// Check payment and transaction statuses
	if ($aTransData['payment_status'] != "Completed") {
		$bTrans = false;
		$sError = "VM015";
	}
	if ($aTrans['status'] != "pending") {
		$bTrans = false;
		$sError = "VM016";
	}
	
	if ($bTrans == true) {
		// phew!  all looks good
		// add the buyer in
		$sName = $aTransData['first_name'] . ' ' . $aTransData['last_name'];
		$oResult = $oInstDB->query('call sp_add_buyer("' . $sName . '", "' . $aTransData['payer_email'] . '")');
		if ($oResult->num_rows > 0) {
			$aBuyer = $oResult->fetch_assoc();
		}
		else {
			refund_msg('VM003');
		}
		$oInstDB->next_result();
		$oResult->close();
		
		
		// change the transaction status and show the user a vend button
		$oResult = $oInstDB->query('call sp_update_trans(' . $aTransData['custom'] . ', "to vend", ' . $aBuyer['id'] . ')');
		if ($oResult->num_rows > 0) {
			$aResult = $oResult->fetch_assoc();
		}
		else {
			refund_msg('VM004');
		}
		$oInstDB->next_result();
		$oResult->close();
		
		$oSmarty->assign("trans", $aTransData['custom']);
		$oSmarty->assign("product", $aTrans['name']);
		$oSmarty->display("vendproduct.tpl");
	}
	else {
		// nope.
		refund_msg($sError);
	}
}
else {
	//  Vend!
	
	// Get transaction details
	$oResult = $oInstDB->query('call sp_get_transaction(' . $iTrans . ')');
	if ($oResult->num_rows > 0) {
		$aTrans = $oResult->fetch_assoc();
	}
	else {
		machine_failed('VM005');
	}
	$oInstDB->next_result();
	$oResult->close();
	
	if (!$oVendComm->vend($aTrans['machine_id'], $aTrans['hopper_id'])) {
		// machine failed to vend
		machine_failed('VM005');
	}
	else {
		// we're done, the machine says it has vended
		// update transaction and remove stock
		$oResult = $oInstDB->query('call sp_update_trans(' . $iTrans . ', "complete", 0)');
		if ($oResult->num_rows > 0) {
			$aResult = $oResult->fetch_assoc();
		}
		else {
			// log this somewhere, noone will actually care!
			var_dump("failed");
			die;
		}
		$oInstDB->next_result();
		$oResult->close();
		
		$oSmarty->assign('admin', $aAdmin);
		$oSmarty->display('complete.tpl');
	}
}




function refund_msg($sCode) {
	global $oSmarty, $aAdmin;
	
	$oSmarty->assign("title", "Problem verifying purchase");
	$aParas = array(
					'Sorry, but we\'ve had a problem verifying the purchase.',
					'Please report this to <a href="mailto:' . $aAdmin['email'] . '">' . $aAdmin['name'] . '</a> to process a refund, quoting ' . $sCode . '.',
					);
	$oSmarty->assign("message", $aParas);
	
	$oSmarty->display('message.tpl');
	die;
}

function machine_failed($sCode) {
	global $oSmarty, $aAdmin;
	
	$oSmarty->assign("title", "Problem with the vending machine");
	$aParas = array(
					'Sorry, but the vending machine has reported an issue with vending your purchase.',
					'Please report this to <a href="mailto:' . $aAdmin['email'] . '">' . $aAdmin['name'] . '</a> to process a refund, quoting ' . $sCode . '.',
					);
	$oSmarty->assign("message", $aParas);
	
	$oSmarty->display('message.tpl');
	die;
}


?>
