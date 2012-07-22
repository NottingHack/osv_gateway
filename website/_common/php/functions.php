<?php
/**
 * General Functions
 *
 * Extra functions
 *
 * @author James Hayward <jhayward1980@gmail.com>
 * @version 1.0
 */

function errorDie($sTitle, $sErrorCode) {
	global $oSmarty, $aAdmin;
	
	$oSmarty->assign("title", "Fatal Error");
	$aParas = array(
					'Sorry, but there has been an error with the system.',
					'Please report this to <a href="mailto:' . $aAdmin['email'] . '">' . $aAdmin['name'] . '</a> giving the following error code:',
					$sErrorCode,
					);
	$oSmarty->assign("message", $aParas);
	
	$oSmarty->display('message.tpl');
	die;
}

?>
