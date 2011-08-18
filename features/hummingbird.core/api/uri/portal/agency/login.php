<?php
class LoginAgencyPortal_HummingbirdController extends Extension_Agency_Portal_Hummingbird_Controller {	
	function isVisible() {
		return true;
	}
	
	function signoutAction() {
		$umsession = ChPortalHelper::getSession();
		$umsession->setProperty('agency', false);
		
		if(null != ($login_extension = UmHbApp::getLoginExtensionActive(ChPortalHelper::getCode()))) {
			if($login_extension->signoff()) {
				// ...
			}
		}
		
		// Globally destroy
		$umsession->destroy();
		
		DevblocksPlatform::redirect(new DevblocksHttpResponse(array('login')));
		exit;
	}
	
	function handleRequest(DevblocksHttpRequest $request) {
		$umsession = ChPortalHelper::getSession();

		$stack = $request->path;
		@array_shift($stack); // login
		
		@$a = DevblocksPlatform::importGPC($_REQUEST['a'],'string');
		
		if(empty($a)) {
    	    @$action = $stack[0] . 'Action';
		} else {
	    	@$action = $a . 'Action';
		}
		
		// Login extension
        // Try the extension subcontroller first (overload)
        if(null != ($login_extension = UmHbApp::getLoginExtensionActive(ChPortalHelper::getCode())) 
        	&& method_exists($login_extension, $action)) {
				call_user_func(array($login_extension, $action));
        
		// Then try the login controller
		} elseif(method_exists($this, $action)) {
			call_user_func(array($this, $action));
		}
	}
	
	function writeResponse(DevblocksHttpResponse $response) {
		$umsession = ChPortalHelper::getSession();
		$tpl = DevblocksPlatform::getTemplateService();

		$stack = $response->path;
		
        $login_extension_active = UmHbApp::getLoginExtensionActive(ChPortalHelper::getCode());
        $tpl->assign('login_extension_active', $login_extension_active);
		
		// Fall back
		if(null != ($login_extension = UmHbApp::getLoginExtensionActive(ChPortalHelper::getCode()))) {
			$login_extension->writeResponse(new DevblocksHttpResponse($stack));
		}
	}
	
	public function getTitle(DevblocksHttpResponse $response) {
		$stack = $response->path;
		$title = '';
		array_shift($stack); // login
		$section = array_shift($stack);
		switch($section) {
			case 'register':
				$title = 'Sign-up to order online';
				break;
			default:
				$title = 'Ordering';
				break;
		}
		
		return $title;
	}
	
	public function getHeader(DevblocksHttpResponse $response) {
		$stack = $response->path;
		$header = '';
		array_shift($stack); // login
		$section = array_shift($stack);
		switch($section) {
			case 'register':
				$header = 'Sign-up with the Good Food Box to order online';
				break;
			default:
				$header = 'Order Online from the Good Food Box';
				break;
		}
		
		return $header;
	}
	
	
// 	public function getClass(DevblocksHttpResponse $response) {
		
// 	} 
};