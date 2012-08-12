<?php

class VendComm {
	
	
	public function __construct() {
		
	}
	
	public function checkStock($iMachineID, $iHopperID) {
		return true;
	}
	
	public function vend($iMachineID, $iHopperID) {
		var_dump($iMachineID);
		var_dump($iHopperID);
		return true;
	}
	
}

?>
