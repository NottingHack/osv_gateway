<?php

require_once(PHP_DIR . "class.websocket_client.php");

class VendComm {
	
	private $oVM;
	
	public $bConnected;
	
	private $aMachines;
	
	public function __construct() {
		global $oInstDB;
		
		$this->oVM = new WebsocketClient;
		// This is communicating with the local websocket
		if ($this->oVM->connect('92.27.7.173', 1402, '/osvend', 'osvgateway')) {
			$this->bConnected = true;
		}
		else {
			$this->bConnected = false;
		}
		
		// Get the vending machine IDs
		$oResult = $oInstDB->query('call sp_get_machines()');
		if ($oResult->num_rows > 0) {
			while ($aRow = $oResult->fetch_assoc()) {
				$aMachine = array(
								  'mac'			=>	$aRow['mac'],
								  'name'		=>	$aRow['name'],
								  'location'	=>	$aRow['location']
								  );
				$this->aMachines[$aRow['id']] = $aMachine;
			}
		}
		$oInstDB->next_result();
		$oResult->close();
	}
	
	public function checkStatus($iMachineID) {
		$this->oVM->sendData("STATUS:" . $this->aMachines[$iMachineID]['mac']);
		$sResponse = $this->oVM->getData();
		if (preg_match("/STATUS:(GOOD|FAULT|UNKNOWN):(.*)/", $sResponse['payload'], $aMatches)) {
			if ($aMatches[1] == "GOOD") {
				return "online";
			}
			elseif ($aMatches[1] == "FAULT") {
				return $aMatches[2];
			}
			else {
				return "vending machine not connected";
			}
		}
		else {
			return "incorrect response from vending machine";
		}
	}
	
	public function checkStock($iMachineID, $iHopperID) {
		$this->oVM->sendData("STOCK:" . $this->aMachines[$iMachineID]['mac'] . ":" . $iHopperID);
		$sResponse = $this->oVM->getData();
		if (preg_match("/STOCK:" . $iHopperID . ":(YES|NO|ERR)/", $sResponse['payload'], $aMatches)) {
			if ($aMatches[1] == "YES") {
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}
	
	public function vend($iMachineID, $iHopperID) {
		$this->oVM->sendData("VEND:" . $this->aMachines[$iMachineID]['mac'] . ":" . $iHopperID);
		$sResponse = $this->oVM->getData();
		if ($sResponse['payload'] == "VEND:ACK") {
			$this->oVM->sendData(" ");
			// this needs to time out at some point??
			$sResponse = $this->oVM->getData();
			if (preg_match("/VEND:(FAILURE|SUCCESS):(.*)/", $sResponse['payload'], $aMatches)) {
				if ($aMatches[1] == "FAILURE") {
					return false;
				}
				else {
					return true;
				}
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}
	
	public function test() {
		var_dump($this->checkStatus(1));
	}
	
}

?>
