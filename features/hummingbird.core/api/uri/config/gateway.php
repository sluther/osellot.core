<?php
class PageMenuItem_GatewayMenu_Setup_Hummingbird extends Extension_PageMenuItem {

	public function render() {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->display('devblocks:hummingbird.core::configuration/section/gateway/menu_item.tpl');
	}
};

class Gateway_SetupSection_Hummingbird extends Extension_PageSection {
	
	public function render() {
		$tpl = DevblocksPlatform::getTemplateService();
		$visit = CerberusApplication::getVisit();
		$request = DevblocksPlatform::getHttpRequest();
		
		$gateways = DAO_Gateway::getAll();
		$tpl->assign('gateways', $gateways);
		
		$tpl->display('devblocks:hummingbird.core::configuration/section/gateway/index.tpl');
	}
	
	public function editGatewayAction() {
		$id = DevblocksPlatform::importGPC($_REQUEST['id'], 'integer', 0);
		
		$tpl = DevblocksPlatform::getTemplateService();
		
		if(null !== $gateway = DAO_Gateway::get($id)) {
			if(null !== $plugin = DevblocksPlatform::getExtension($gateway->extension_id, true)) {
				if($plugin instanceof Extension_Gateway_Hummingbird) {
					$tpl->assign('settings', $plugin->getSettings());
					$tpl->assign('plugin', $plugin);			
				}
			}
			$tpl->assign('gateway', $gateway);
		}
		
		$tpl->display('devblocks:hummingbird.core::configuration/section/gateway/configure.tpl');
	}
	
	public function saveGatewaySettingsAction() {
		$id = DevblocksPlatform::importGPC($_REQUEST['id'], 'integer', 0);
		@$enabled = DevblocksPlatform::importGPC($_REQUEST['enabled'], 'bool', 0);
		@$settings = DevblocksPlatform::importGPC($_REQUEST['settings'], 'array', array());
		
		try {
			if(null === $gateway = DAO_Gateway::get($id))
				throw new Exception("The gateway you specified is invalid.");
			if(null === $plugin = DevblocksPlatform::getExtension($gateway->extension_id, true))
				throw new Exception("The gateway you specified is invalid.");
			if(!$plugin instanceof Extension_Gateway_Hummingbird)
				throw new Exception("The gateway you specified is invalid.");
			
			DAO_Gateway::update($id, array(DAO_Gateway::ENABLED => $enabled));
			$plugin->saveSettings($settings);
		
			
			echo json_encode(array('status'=>true));
			return;
				
		} catch(Exception $e) {
			echo json_encode(array('status'=>false,'error'=>$e->getMessage()));
			return;
				
		}
	}
}