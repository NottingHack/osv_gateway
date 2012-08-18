<?php
define('COMMON_DIR',  dirname(__FILE__) . '/' . '_common/php/');
require_once(COMMON_DIR . 'websockets.php');

class echoServer extends WebSocketServer {
	//protected $maxBufferSize = 1048576; //1MB... overkill for an echo server, but potentially plausible for other applications.
	
	protected function send ($user, $message) {
		$this->stdout("> $message");
		parent::send($user, $message);
	}
	
	protected function process ($user, $message) {
		$this->stdout("< $message");
		if (substr($message, 0, 9) == "MACHINEID") {
			$this->send($user,"STATUS");
		}
		//$this->send($user,$message);
	}
	
	protected function connected ($user) {
		// Do nothing: This is just an echo server, there's no need to track the user.
		// However, if we did care about the users, we would probably have a cookie to
		// parse at this step, would be looking them up in permanent storage, etc.
		$this->send($user, "MACHINEID");
	}
	
	protected function closed ($user) {
		// Do nothing: This is where cleanup would go, in case the user had any sort of
		// open files or other objects associated with them.  This runs after the socket 
		// has been closed, so there is no need to clean up the socket itself here.
	}
	
	protected function processProtocol($protocol) {
		return "Sec-WebSocket-Protocol: $protocol"; // return either "Sec-WebSocket-Protocol: SelectedProtocolFromClientList\r\n" or return an empty string.  
				   // The carriage return/newline combo must appear at the end of a non-empty string, and must not
				   // appear at the beginning of the string nor in an otherwise empty string, or it will be considered part of 
				   // the response body, which will trigger an error in the client as it will not be formatted correctly.
	}
}

$echo = new echoServer('10.0.0.5', 1402);


?>
