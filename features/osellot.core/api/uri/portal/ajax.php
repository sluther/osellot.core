<?php
class UmOsellotAjaxController extends Extension_UmScController {
	function __construct($manifest=null) {
		parent::__construct($manifest);
		
		$tpl = DevblocksPlatform::getTemplateService();
		$umsession = ChPortalHelper::getSession();
		
        @$active_contact = $umsession->getProperty('hb_login',null);
        $tpl->assign('active_contact', $active_contact);

		// Usermeet Session
		if(null == ($fingerprint = ChPortalHelper::getFingerprint())) {
			die("A problem occurred.");
		}
        $tpl->assign('fingerprint', $fingerprint);
	}
	
	function handleRequest(DevblocksHttpRequest $request) {
		@$path = $request->path;
		@$a = DevblocksPlatform::importGPC($_REQUEST['a'],'string');
	    
		if(empty($a)) {
    	    @$action = array_shift($path) . 'Action';
		} else {
	    	@$action = $a . 'Action';
		}
		
	    switch($action) {
	        default:
			    // Default action, call arg as a method suffixed with Action
				if(method_exists($this,$action)) {
					call_user_func(array($this, $action), new DevblocksHttpRequest($path)); // Pass HttpRequest as arg
				}
	            break;
	    }
	}
	
	public function getCheckoutFormAction() {	
		$id = DevblocksPlatform::importGPC($_REQUEST['id'], 'string', '');
		
		if(null !== $plugin = DevblocksPlatform::getExtension($id, true)) {
			if($plugin instanceof Extension_Gateway_Osellot) {
				$plugin->checkout();	
			}
		}
	}
	
};