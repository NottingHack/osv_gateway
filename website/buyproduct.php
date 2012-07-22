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

var_dump($aProduct);


?>
