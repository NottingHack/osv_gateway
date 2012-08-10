<?php
require_once(PHP_DIR . 'smarty/Smarty.class.php');

class OVS_Smarty extends Smarty {
	public function __construct() {
		// Class Constructor.
		// These automatically get set with each new instance.
		
		parent::__construct();
		
		$this->setTemplateDir(COMMON_DIR . 'templates');
		$this->setCompileDir(COMMON_DIR . 'templates_c');
		$this->setConfigDir(COMMON_DIR . 'config');
		$this->setCacheDir(COMMON_DIR . 'cache');
		
		$this->caching = Smarty::CACHING_LIFETIME_CURRENT;
	}
}

$oSmarty = new OVS_Smarty();

$oSmarty->assign('http', HTTP);
$oSmarty->assign('root_url', ROOT_URL);
$oSmarty->assign('common_url', COMMON_URL);
$oSmarty->assign('img_url', IMG_URL);
$oSmarty->assign('js_url', JS_URL);
$oSmarty->assign('css_url', CSS_URL);
?>
