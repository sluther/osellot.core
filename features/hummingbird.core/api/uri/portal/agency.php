<?php
class AgencyPortal_HummingbirdController extends Extension_Portal_Hummingbird_Controller {
	
	private function _getModules() {
		$modules = DevblocksPlatform::getExtensions('agency.portal.controller.hummingbird', true);
		
		$available_modules = array();
		if(is_array($modules))
		foreach($modules as $uri => $module) {
			// Must be menu renderable
			if(!empty($module->manifest->params['menu_title']) && !empty($uri) && $module instanceof Extension_Agency_Portal_Hummingbird_Controller) {
				$module_uri = $module->manifest->params['uri'];
				$available_modules[$module_uri] = $module;
			}
		}
		
		return $available_modules;
	}
	
	private function _getModuleByUri($stack) {
		array_shift($stack); // agency
		$module_uri = array_shift($stack);
		
		$modules = $this->_getModules();
		if(isset($modules[$module_uri])) {
			$module = $modules[$module_uri];
		} else {
			$module = $modules['login'];
		}
		
		return $module;
	}
	
    public function handleRequest(DevblocksHttpRequest $request) {
    	$stack = $request->path;
        array_shift($stack); // agency
    	$module_uri = array_shift($stack);
		
		switch($module_uri) {
			case 'ajax':
				$controller = new UmHbAjaxController(null);
				$controller->handleRequest(new DevblocksHttpRequest($stack));
				exit;
				break;
			
			default:
		        $modules = $this->_getModules();
				$controller = null;
				
		        if(isset($modules[$module_uri])) {
		        	$controller = $modules[$module_uri];
		        }
		        
		        array_unshift($stack, $module_uri);
		
				if(!is_null($controller))
					$controller->handleRequest(new DevblocksHttpRequest($stack));
					
				break;
		}
    }
    
	
	public function writeResponse(DevblocksHttpResponse $response) {
		$tpl = DevblocksPlatform::getTemplateService();
		$umsession = ChPortalHelper::getSession();
		
		$logged_in = $umsession->getProperty('agency', false);
		$active_profile = $umsession->getProperty('hb_login', null);
		$is_agency = $umsession->getProperty('agency', false);
		
		$stack = $response->path;
		
		array_shift($stack); // agency
		$module_uri = array_shift($stack);
		
		$modules = $this->_getModules();
		
		// Modules
		if(isset($modules[$module_uri])) {
			$module = $modules[$module_uri];
			array_unshift($stack, $module_uri);
		} else {
			// Are they logged in?
			$module = reset($modules);
			if($active_profile == null || !$is_agency) {
				$module = $modules['login'];
			} else {
				$module = $modules['account']; // account
			}
		}
		array_unshift($stack, 'agency'); // add agency back to the stack for login
		$tpl->assign('module', $module);
		$tpl->assign('module_response', new DevblocksHttpResponse($stack));
		
		$tpl->display('devblocks:hummingbird.core:portal_'.ChPortalHelper::getCode() . ":portal/agency/index.tpl");
	}
	
	public function getClass(DevblocksHttpResponse $response) {
		$stack = $response->path;
		$class = 'agency ';
		array_shift($stack); // agency
		@$module = array_shift($stack);
		@$section = array_shift($stack);
		
		if(null !== $module) {
			if ($module == 'order') {
				$class .= 'order ';
				if($section == null) {
					$class .= 'cart';
				} else {
					$class .= $section;
				}
			} else {			
				if(null !== $section) {
					$class .= $section;
				} else {
					$class .= $module;
				}
			}
		} else {
			$class .= 'splash';
		}
		return $class;
	}
	
	
	public function getTitle(DevblocksHttpResponse $response) {
		$stack = $response->path;
		
		$module = $this->_getModuleByUri($stack);
		return $module->getTitle($response);
	}
	
	public function getHeader(DevblocksHttpResponse $response) {
		$stack = $response->path;
		
		$module = $this->_getModuleByUri($stack);
		return $module->getHeader($response);
	}
	
	public function hasCustomMenu() {
		return true;
	}
	
	public function renderCustomMenu(DevblocksHttpResponse $response) {
		$url = DevblocksPlatform::getUrlService();
		$menu = array(
			0 => array('url' => 'http://thegoodfoodbox.ca/', 'title' => 'The Good Food Box'),
		);
		$stack = $response->path;
		
		$titles = array(
			'agency' => 'Agency Order Management',
			'agency/order' => 'Place an Order',
			'agency/order/modify' => 'Modify an Order');
		$path = '';
		
		foreach($stack as $part) {
			$path .= $part;
			if(!isset($titles[$path])) {
				break;
			}
			$menu[] = array('url' => $url->write($path), 'title' => $titles[$path]);
			$path .= '/';
		}
		
		return $menu;
	}
};