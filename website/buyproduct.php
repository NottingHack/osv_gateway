<?php
require_once('common.php');

if (isset($_GET['machine']) and isNumber($_GET['machine'])) {
	$iMachineID = intval($_GET['machine']);
	if (isset($_GET['hopper']) and isNumber($_GET['hopper'])) {
		$iHopperID = intval($_GET['hopper']);
	}
	else {
		errorDie("Fatal Error", "ID002");
	}
}
else {
	errorDie("Fatal Error", "ID001");
}

/* get product details */
$oResult = $oInstDB->query('call sp_get_product(' . $iMachineID . ', ' . $iHopperID . ')');

if ($oResult->num_rows > 0) {
	$aProduct = $oResult->fetch_assoc();
}
$oResult->close();

$aProduct['machine_id'] = $_GET['machine'];
$aProduct['hopper_id'] = $_GET['hopper'];

/* Does the machine think it has stock? */
if (!$oVendComm->checkStock($iMachineID, $iHoppeID)) {
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


$oSmarty->assign("product", $aProduct);
$oSmarty->display("buyproduct.tpl");


?>
