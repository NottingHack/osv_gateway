<?php
require_once('common.php');

/* Check transactions table and remove old ones */
$oResult = $oInstDB->query('call sp_check_trans()');
if ($oResult->num_rows > 0) {
	$aResult = $oResult->fetch_assoc();
}
$oInstDB->next_result();
if (isset($aResult['err'])) {
	errorDie("Fatal Error", "BP000: " . $aResult['err']);
}

/* let's get on with the buying! */

if (isset($_GET['machine']) and isNumber($_GET['machine'])) {
	$iMachineID = intval($_GET['machine']);
	if (isset($_GET['hopper']) and isNumber($_GET['hopper'])) {
		$iHopperID = intval($_GET['hopper']);
	}
	else {
		errorDie("Fatal Error", "BP002");
	}
}
else {
	errorDie("Fatal Error", "BP001");
}

$oResult = $oInstDB->query('call sp_get_product(' . $iMachineID . ', ' . $iHopperID . ')');

if ($oResult->num_rows > 0) {
	$aProduct = $oResult->fetch_assoc();
}
else {
	$oSmarty->assign("title", "Product Doesn't Exist");
	$aParas = array(
					'Sorry, but we don\'t have any details for the product.',
					'Please try another product.',
					);
	$oSmarty->assign("message", $aParas);
	
	$oSmarty->display('message.tpl');
	die;
}
$oInstDB->next_result();
$oResult->close();

$aProduct['machine_id'] = $iMachineID;
$aProduct['hopper_id'] = $iHopperID;

/* Is the machine connected? */
$sMacStatus = $oVendComm->checkStatus($iMachineID); 
if ($sMacStatus != "online") {
	$oSmarty->assign("title", "Problem with Vending Machine");
	$aParas = array(
					'Sorry, but there is a problem with the vending machine.',
					'Error:',
					$sMacStatus,
					);
	$oSmarty->assign("message", $aParas);
	
	$oSmarty->display('message.tpl');
	die;
}


/* Does the machine think it has stock? */
if (!$oVendComm->checkStock($iMachineID, $iHopperID)) {
	$oSmarty->assign("title", "Out of Stock");
	$aParas = array(
					'Sorry, but the vending machine says it is out of stock of ' . $aProduct['name'] . '.',
					'Please try another product.',
					);
	$oSmarty->assign("message", $aParas);
	
	$oSmarty->display('message.tpl');
	die;
}

/* Do we think we have stock? */
if (($aProduct['stock'] - $aProduct['reserved']) <= 0) {
	$oSmarty->assign("title", "Out of Stock");
	$aParas = array(
					'Sorry, but the stock in the vending machine has already been bought and is reserved.',
					'Please try another product.',
					);
	$oSmarty->assign("message", $aParas);
	
	$oSmarty->display('message.tpl');
	die;
}

/* We have stock, reserve a unit and let them buy it! */
$oResult = $oInstDB->query('call sp_reserve_stock(' . $iMachineID . ', ' . $iHopperID . ')');

if ($oResult->num_rows > 0) {
	$aResult = $oResult->fetch_assoc();
}
else {
	errorDie("Fatal Error", "BP003");
}

if (isset($aResult['err'])) {
	errorDie("Fatal Error", "BP004: " . $aResult['err']);
}

$oSmarty->assign("trans_id", $aResult['trans']);

$oSmarty->assign("product", $aProduct);
$oSmarty->assign("paypal", $aPayPal);
$oSmarty->assign("currency", $aCurrency);
$oSmarty->display("buyproduct.tpl");


?>
