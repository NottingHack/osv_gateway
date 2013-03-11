<?php
require_once('websockets.php');

class VendMachine extends WebSocketUser {
	public $iMachineID;
	public $aHoppers;
	
	public $sProtocol;
	
	public $bOnline;
	
	public $sStateReason;
	
	public $aPending;
	
	public $bVending = false; 
	
	public function __construct($id, $socket) {
		parent::__construct($id, $socket);
		
		$this->aHoppers = array();
		$this->aPending = array();
	}
	
	public function removeRequests($aRemove) {
		$aNew = array();
		for ($i = 0; $i < count($this->aPending) ; $i++) {
			if (!in_array($i, $aRemove)) {
				$aNew = $this->aPending[$i];
			}
		}
		$this->aPending = $aNew;
	}
}

class VendControl extends WebSocketServer {
	private $bDebug = true;
		
	private $iOsvVersion = 1;
	private $iSupVersion = 1;
	
	private $aMachines;
	
	protected $userClass = "VendMachine";
	
	function __construct($addr, $port, $bufferLength = 2048) {
		parent::__construct($addr, $port, $bufferLength);
		
		$this->aMachines = array();
	}
	
	// Overload function for debugging
	protected function send ($user, $message) {
		if ($this->bDebug) {
			$this->stdout("> $message");
		}
		parent::send($user, $message);
	}
	
	protected function process ($user, $message) { 
		if ($this->bDebug) {
			$this->stdout("< $message");
			$this->stdout("p " . $user->sProtocol);
		}
		
		if ($user->sProtocol == "osvend") {
			$this->processVendMsg($user, $message);
		}
		elseif ($user->sProtocol == "osvgateway") {
			$this->processGatewayMsg($user, $message);
		}
	}
	
	protected function connected ($user) {
		global $iOsvVersion;
		
		// Store the users sub-protocol
		$user->sProtocol = $user->headers['sec-websocket-protocol'];
		
		
		// Ask for the machine ID
		if ($user->sProtocol == "osvend") {
			$this->send($user, "MACHINEID:" . $this->iOsvVersion);
		}
	}
	
	protected function closed ($user) {
		// Do nothing: This is where cleanup would go, in case the user had any sort of
		// open files or other objects associated with them.  This runs after the socket 
		// has been closed, so there is no need to clean up the socket itself here.
		
		$this->stdout("***connection closed");
	}
	
	protected function processProtocol($protocol) {
		return "Sec-WebSocket-Protocol: $protocol"; // return either "Sec-WebSocket-Protocol: SelectedProtocolFromClientList\r\n" or return an empty string.  
				   // The carriage return/newline combo must appear at the end of a non-empty string, and must not
				   // appear at the beginning of the string nor in an otherwise empty string, or it will be considered part of 
				   // the response body, which will trigger an error in the client as it will not be formatted correctly.
	}
	
	private function processVendMsg($user, $message) {
		if (preg_match("/MACHINEID:([a-fA-F0-9\:\-]+):(\d+)/", $message, $aMatches)) {
			if ($aMatches[2] > $this->iSupVersion) {
				$this->send($user,"INFO:The controller version is not supported");
				// need to disconnect here
			} 
			
			$user->iMachineID = $aMatches[1];
			$this->aMachines[$user->iMachineID] = $user;
			
			// Get the machine's status
			$this->send($user,"STATUS");
		}
		if (preg_match("/STATUS:(.*):(GOOD|FAULT):(.*)/", $message, $aMatches)) {
			if ($aMatches[2] != "GOOD") {
				$user->bOnline = false;
				$user->sStateReason = $aMatches[3];
			}
			else {
				$user->bOnline = true;
			}
			
			if ($aMatches[1] != "") {
				$aHoppers = explode(",", $aMatches[1]);
				
				foreach ($aHoppers as $sHopper) {
					$sHopper = trim($sHopper, "[]");
					$aHopper = explode("-", $sHopper);
					$user->aHoppers[$aHopper[0]] = array(
														 'state'	=>	$aHopper[1],
														 'stock'	=>	$aHopper[2],
														 );
				}
				
			}
			
			//$aTest = array_keys($this->aHoppers);
			
			//$this->send($user,"VEND:" . $aTest[0]);
		}
		if (preg_match("/STOCK:(\d+):(YES|NO|ERR)/", $message, $aMatches)) {
			// have we got pending requests?
			// if so, respond to them
			if (count($user->aPending) > 0) {
				$aRemove = array();
				for ($i = 0; $i < count($user->aPending) ; $i++) {
					if ($user->aPending[$i]['msg'] == "STOCK" and $user->aPending[$i]['hopper'] == $aMatches[1]) {
						$aRemove[] = $i;
						if (isset($user->aPending[$i]['timer'])) {
							$this->removeTimeout($user->aPending[$i]['timer']);
						}
						$this->send($user->aPending[$i]['user'],"STOCK:" . $aMatches[1] . ":" . $aMatches[2]); // Basically sending on the message!
					}
				}
				
				//now remove completed requests
				$user->removeRequests($aRemove);
			}
		}
		if (preg_match("/VEND:(\d+):ACK/", $message, $aMatches)) {
			// we dont care, weve already sent an ACK
		}
		if (preg_match("/VEND:(\d+):(SUCCESS|FAILURE):(.*)/", $message, $aMatches)) {
			// have we got pending requests?
			// if so, respond to them
			if (count($user->aPending) > 0) {
				$aRemove = array();
				for ($i = 0; $i < count($user->aPending) ; $i++) {
					if ($user->aPending[$i]['msg'] == "VEND" and $user->aPending[$i]['hopper'] == $aMatches[1]) {
						$aRemove[] = $i;
						$this->send($user->aPending[$i]['user'],"VEND:" . $aMatches[2] . ":" . $aMatches[3]);
					}
				}
				
				//now remove completed requests
				$user->removeRequests($aRemove);
			}
		}
	}
	
	private function processGatewayMsg($user, $message) {
		if (preg_match("/STATUS:([a-fA-F0-9\:\-]+)/", $message, $aMatches)) {
			// do we know about the machine?
			if (!isset($this->aMachines[$aMatches[1]])) {
				$this->send($user,"STATUS:UNKNOWN:");
			}
			else {
				// what's the status of the machine
				$oMachine = $this->aMachines[$aMatches[1]];
				if ($oMachine->bOnline) {
					$this->send($user,"STATUS:GOOD:");
				}
				else {
					$this->send($user,"STATUS:FAULT:" . $oMachine->sStateReason);
				}
			}
		}
		if (preg_match("/STOCK:([a-fA-F0-9\:\-]+):(\d+)/", $message, $aMatches)) {
			// do we know about the machine?
			if (!isset($this->aMachines[$aMatches[1]])) {
				$this->send($user,"STOCK:" . $aMatches[2] . ":UNKNOWN");
			}
			else {
				$oMachine = $this->aMachines[$aMatches[1]];
				// do we know about the hopper?
				if (!isset($oMachine->aHoppers[$aMatches[2]])) {
					$this->send($user,"STOCK:" . $aMatches[2] . ":ERR");
				}
				else {
					// ask the VMC
					$timerID = $this->addTimeout($this->aMachines[$aMatches[1]]);
					$this->aMachines[$aMatches[1]]->aPending[] = array(
																	   'msg'	=>	'STOCK',
																	   'user'	=>	$user,
																	   'hopper'	=>	$aMatches[2],
																	   'timer'	=>	$timerID,
																	   );
					
					$this->send($this->aMachines[$aMatches[1]], "STOCK:" . $aMatches[2]);
				}
			}
		}
		if (preg_match("/VEND:([a-fA-F0-9\:\-]+):(\d+)/", $message, $aMatches)) {
			$this->send($user,"VEND:ACK");
			// do we know about the machine?
			if (!isset($this->aMachines[$aMatches[1]])) {
				$this->send($user,"VEND:FAILURE:unknown machine");
			}
			else {
				$oMachine = $this->aMachines[$aMatches[1]];
				// do we know about the hopper?
				if (!isset($oMachine->aHoppers[$aMatches[2]])) {
					var_dump($oMachine->aHoppers);
					$this->send($user,"VEND:FAILURE:unknown hopper");
				}
				else {
					// ask the VMC
					$this->aMachines[$aMatches[1]]->aPending[] = array(
																	   'msg'	=>	'VEND',
																	   'user'	=>	$user,
																	   'hopper'	=>	$aMatches[2]
																	   );
					$this->send($this->aMachines[$aMatches[1]], "VEND:" . $aMatches[2]);
				}
			}
		}
	}
	
	
}

$oVC = new vendControl('10.0.0.5', 1402);


?>
