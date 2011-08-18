<?php
class UmHummingbirdApp extends Extension_UsermeetTool {
	
	private function _getModules() {
		
		$manifests = DevblocksPlatform::getExtensions(self::POINT);
		$controllers = array();
		
		if(is_array($manifests)) {
			foreach($manifests as $manifest) {
				if(isset($manifest->params['uri']))
					$controllers[$manifest->params['uri']] = $manifest->createInstance();
			}
		}
		
		return $controllers;
	}
	
	public function handleRequest(DevblocksHttpRequest $request) {
		$stack = $request->path;
		
		array_shift($stack); // hummingbird_portal 
		@$module_uri = array_shift($stack);
		
		switch($module_uri) {
			case NULL:
				// [TODO] Index/page render
				break;
				//
			default:
		        $modules = $this->_getModules();
				$module = null;
				
		        if(isset($modules[$module_uri])) {
		        	$module = $modules[$module_uri];
		        }
		        
		        array_unshift($stack, $module_uri);
		
				if(!is_null($module))
					$module->handleRequest(new DevblocksHttpRequest($stack));
					
				break;
			}
	}
	
	public function writeResponse(DevblocksHttpResponse $response) {
		$tpl = DevblocksPlatform::getTemplateService();
		
		$stack = $response->path;
		
		array_shift($stack); // hummingbrd_portal
		$module_uri = array_shift($stack);
		
		switch($module_uri) {
			case NULL:
				break;
			
			default:
				$modules = $this->_getModules();
				
				if(isset($modules[$module_uri])) {
					$module = $modules[$module_uri];
					$tpl->assign('module', $module);
					array_unshift($stack, $module_uri);
					$tpl->assign('module_response', new DevblocksHttpResponse($stack));
				}
				
		}

		$tpl->display('devblocks:hummingbird.core::portal/index.tpl');
	}
};