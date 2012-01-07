<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2010, WebGroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/
/*
 * IMPORTANT LICENSING NOTE from your friends on the Cerberus Helpdesk Team
 * 
 * Sure, it would be so easy to just cheat and edit this file to use the 
 * software without paying for it.  But we trust you anyway.  In fact, we're 
 * writing this software for you! 
 * 
 * Quality software backed by a dedicated team takes money to develop.  We 
 * don't want to be out of the office bagging groceries when you call up 
 * needing a helping hand.  We'd rather spend our free time coding your 
 * feature requests than mowing the neighbors' lawns for rent money. 
 * 
 * We've never believed in hiding our source code out of paranoia over not 
 * getting paid.  We want you to have the full source code and be able to 
 * make the tweaks your organization requires to get more done -- despite 
 * having less of everything than you might need (time, people, money, 
 * energy).  We shouldn't be your bottleneck.
 * 
 * We've been building our expertise with this project since January 2002.  We 
 * promise spending a couple bucks [Euro, Yuan, Rupees, Galactic Credits] to 
 * let us take over your shared e-mail headache is a worthwhile investment.  
 * It will give you a sense of control over your inbox that you probably 
 * haven't had since spammers found you in a game of 'E-mail Battleship'. 
 * Miss. Miss. You sunk my inbox!
 * 
 * A legitimate license entitles you to support from the developers,  
 * and the warm fuzzy feeling of feeding a couple of obsessed developers 
 * who want to help you get more done.
 *
 * - Jeff Standen, Darren Sugita, Dan Hildebrandt, Joe Geck, Scott Luther,
 * 		and Jerry Kanoholani. 
 *	 WEBGROUP MEDIA LLC. - Developers of Cerberus Helpdesk
 */
class OsellotPage extends CerberusPageExtension {
	private $_TPL_PATH = '';
	private $cerb5;
	private $base_url;
	private $results = array();
	private $errors = array();
	
	function __construct($manifest) {
		$this->_TPL_PATH = dirname(dirname(dirname(__FILE__))) . '/templates/';
		parent::__construct($manifest);
	}
		
	function isVisible() {
		// check login
		$visit = CerberusApplication::getVisit();
		
		if(empty($visit)) {
			return false;
		} else {
			return true;
		}
	}
	
	function getActivity() {
		return new Model_Activity('activity.activity');
	}
	
	function render() {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('path', $this->_TPL_PATH);
		
		$response = DevblocksPlatform::getHttpResponse();
		$tpl->assign('request_path', implode('/',$response->path));

		// Remember the last tab/URL
		$visit = CerberusApplication::getVisit();
		
		// Tab set explicitly in URL?
		if(null == ($selected_tab = @$response->path[1])) {
			$selected_tab = $visit->get(Extension_Tab_Billing_Osellot::POINT, '');
		}
		$tpl->assign('selected_tab', $selected_tab);

		// Path
		$stack = $response->path;
		array_shift($stack); // billing
		
		// test DAO
		
		// Tab Manifests
		
		$tab_manifests = DevblocksPlatform::getExtensions('tab.billing.osellot');
		$tpl->assign('tab_manifests', $tab_manifests);
		$tpl->display('devblocks:osellot.core::billing/index.tpl');
		
//		var_dump($config);
//		var_dump(debug_backtrace());
//		var_dump($stack);
//		if($stack[0] == 'display') {
//			$this->display();
//		}

		
//		$authnet->processTransaction($transaction);
	}
	
	// Ajax
	function showTabAction() {
		@$ext_id = DevblocksPlatform::importGPC($_REQUEST['ext_id'],'string','');
		
		$visit = CerberusApplication::getVisit();
		
		if(null != ($tab_mft = DevblocksPlatform::getExtension($ext_id)) 
			&& null != ($inst = $tab_mft->createInstance()) 
			&& $inst instanceof Extension_Tab_Billing_Osellot) {
				$visit->set(Extension_Tab_Billing_Osellot::POINT, $inst->manifest->params['uri']);
				$inst->showTab();
		}
	}
	
	/*
	* [TODO] Proxy any func requests to be handled by the tab directly,
	* instead of forcing tabs to implement controllers.  This should check
	* for the *Action() functions just as a handleRequest would
	*/
	function handleTabActionAction() {
		@$tab = DevblocksPlatform::importGPC($_REQUEST['tab'],'string','');
		@$action = DevblocksPlatform::importGPC($_REQUEST['action'],'string','');
	
		if(null != ($tab_mft = DevblocksPlatform::getExtension($tab))
		&& null != ($inst = $tab_mft->createInstance())
		&& $inst instanceof Extension_Tab_Billing_Osellot) {
			if(method_exists($inst,$action.'Action')) {
				call_user_func(array(&$inst, $action.'Action'));
			}
		}
	}
	
	public function createAction() {		
		
		$gateway_plugins = DevblocksPlatform::getExtensions('wgm.osellot.gateway.cc');

		$authnet = DevblocksPlatform::getExtension('wgm.osellot.gateway.cc.authnet', true);
		
		$transaction = new stdClass();
		
//		$transaction->cc->number = '4007000000027';
//		$transaction->cc->exp_date = '04/17';
//		$transaction->cc->card_code = '782';
//		$transaction->amount = '9.95';
//		$transaction->first_name = 'Scott';
//		$transaction->last_name = 'Luther';
		
		$customer = new stdClass();
		$customer->description = "Test Description";
//		$customer->merchantCustomerId = time().rand(1,150);
		$customer->email = "sluther@bitpiston.com";
		
		
		
		$paymentProfile = new stdClass();
		$paymentProfile->customerType = 'individual';
		$paymentProfile->payment->creditCard->cardNumber = '4007000000027';
		$paymentProfile->payment->creditCard->expirationDate = '2017-04';
		$customer->paymentProfiles[] = $paymentProfile;
		
		$authnet->createCustomerProfile($customer);

		
	}
	
	public function displayAction() {
		// Path
		$request = DevblocksPlatform::getHttpRequest();
		$stack = $request->path;
		
		array_shift($stack); // osellot
		array_shift($stack); // display
		$profileId = array_shift($stack);
		
		$gateway_plugins = DevblocksPlatform::getExtensions('wgm.osellot.gateway.cc');

		$authnet = DevblocksPlatform::getExtension('wgm.osellot.gateway.cc.authnet', true);
		
		$transaction = new stdClass();
		
//		$transaction->cc->number = '4007000000027';
//		$transaction->cc->exp_date = '04/17';
//		$transaction->cc->card_code = '782';
//		$transaction->amount = '9.95';
//		$transaction->first_name = 'Scott';
//		$transaction->last_name = 'Luther';
		
		$customer = new stdClass();
		$customer->description = "Test Description";
		$customer->email = "sluther@bitpiston.com";
		
		
		
		$paymentProfile = new stdClass();
		$paymentProfile->customerType = 'individual';
		$paymentProfile->payment->creditCard->cardNumber = '4007000000027';
		$paymentProfile->payment->creditCard->expirationDate = '2017-04';
		$customer->paymentProfiles[] = $paymentProfile;
		
//		$authnet->createCustomerProfile($customer);
		$authnet->getCustomerProfile($profileId);
		
	}
	
	public function deleteAction() {
		// Path
		$request = DevblocksPlatform::getHttpRequest();
		$stack = $request->path;
		
		array_shift($stack); // osellot
		array_shift($stack); // display
		$profileId = array_shift($stack);
		$authnet = DevblocksPlatform::getExtension('wgm.osellot.gateway.cc.authnet', true);
		
		$authnet->deleteCustomerProfile($profileId);
		
		
	}

	
};


if(class_exists('Extension_PageSection')):
class SetupSection_Osellot extends Extension_PageSection {
	const ID = 'setup.osellot.core';

	function render() {
		// check whether extensions are loaded or not
// 		$extensions = array(
// 			'oauth' => extension_loaded('oauth')
// 		);
		$tpl = DevblocksPlatform::getTemplateService();
		$response = DevblocksPlatform::getHttpResponse();
		
		$visit = CerberusApplication::getVisit();
		$visit->set(ChConfigurationPage::ID, 'osellot');
		
		$tab_manifests = DevblocksPlatform::getExtensions('tab.setup.osellot.core');
		
		$stack = $response->path;
		array_shift($stack); // config
		array_shift($stack); // osellot
		
		if(!empty($stack)) {
			@$tab_selected = array_shift($stack);
			if(empty($tab_selected))
			$tab_selected = 'settings';
					$tpl->assign('tab_selected', $tab_selected);
		
		}
		$tpl->assign('tab_manifests', $tab_manifests);
		$tpl->display('devblocks:osellot.core::configuration/section/billing/index.tpl');
	}
	
	function showTabAction() {
		@$ext_id = DevblocksPlatform::importGPC($_REQUEST['ext_id'],'string','');
		
		$visit = CerberusApplication::getVisit();
		
		if(null != ($tab_mft = DevblocksPlatform::getExtension($ext_id)) 
			&& null != ($inst = $tab_mft->createInstance()) 
			&& $inst instanceof Extension_OsellotSetupTab) {
				$visit->set(self::ID, $inst->manifest->params['uri']);
				$inst->showTab();
		}
	}

	function saveJsonAction() {
		try {
			@$consumer_key = DevblocksPlatform::importGPC($_REQUEST['consumer_key'],'string','');
			@$consumer_secret = DevblocksPlatform::importGPC($_REQUEST['consumer_secret'],'string','');
				
			if(empty($consumer_key) || empty($consumer_secret))
			throw new Exception("Both the API Auth Token and URL are required.");
				
			DevblocksPlatform::setPluginSetting('wgm.twitter','consumer_key',$consumer_key);
			DevblocksPlatform::setPluginSetting('wgm.twitter','consumer_secret',$consumer_secret);
				
			echo json_encode(array('status'=>true,'message'=>'Saved!'));
			return;
				
		} catch (Exception $e) {
			echo json_encode(array('status'=>false,'error'=>$e->getMessage()));
			return;
		}
	}




};
endif;

abstract class Extension_Portal_Osellot_Controller extends DevblocksExtension implements DevblocksHttpRequestHandler {
	private $portal = '';

	/*
	 * Site Key
	 * Site Name
	 * Site URL
	 */

	/**
	 * @param DevblocksHttpRequest
	 * @return DevblocksHttpResponse
	 */
	public function handleRequest(DevblocksHttpRequest $request) {
		$path = $request->path;
		@$a = DevblocksPlatform::importGPC($_REQUEST['a'],'string');

		if(empty($a)) {
			@array_shift($path); // controller
			@$action = array_shift($path) . 'Action';
		} else {
			@$action = $a . 'Action';
		}
		
		switch($action) {
			case NULL:
				// [TODO] Index/page render
				break;
				//
			default:
				// Default action, call arg as a method suffixed with Action
				if(method_exists($this,$action)) {
					call_user_func(array(&$this, $action)); // [TODO] Pass HttpRequest as arg?
				}
				break;
		}
	}
	
	public function writeResponse(DevblocksHttpResponse $response) {
		/* Expect Overload */
	}
	
	public function renderSidebar(DevblocksHttpResponse $response) {
		/* Expect Overload */
		return;
	}
	
	public function isVisible() {
		/* Expect Overload */
		return true;
	}
	
	public function configure(Model_CommunityTool $instance) {
		// [TODO] Translate
		echo "This module has no configuration options.<br><br>";
	}
	
	public function saveConfiguration(Model_CommunityTool $instance) {
		/* Expect Overload */
	}
	
	public function getTitle(DevblocksHttpResponse $response) {
		/* Expect Overload */
	}
	
	public function getHeader(DevblocksHttpResponse $response) {
		/* Expect Overload */
	}
	
	public function getClass(DevblocksHttpResponse $response) {
		$stack = $response->path;
		$class = 'order ';
		
		@$module = array_shift($stack);
		@$section = array_shift($stack);
		
		if(null !== $module) {
			if ($module == 'order') {
				$module = 'cart';
			}
			if(null !== $section) {
				$class .= $section;
			} else {
				$class .= $module;
			}
		} else {
			$class .= 'login';
		}
		return $class;
	}
	
	public function hasCustomMenu() {
		/* False by default, can be set per-module */
		return false;
	}
};

abstract class Extension_OsellotLoginAuthenticator extends DevblocksExtension {

	abstract function writeResponse(DevblocksHttpResponse $response);

	/**
	 * release any resources tied up by the authenticate process, if necessary
	 */
	function signoff() {
		$umsession = ChPortalHelper::getSession();
		$umsession->logout();
	}
};

class UmOsellotApp extends Extension_UsermeetTool {
	const PARAM_PAGE_TITLE = 'common.page_title';
	const PARAM_DEFAULT_LOCALE = 'common.locale';
	const PARAM_LOGIN_EXTENSIONS = 'common.login_extensions';
	const PARAM_VISIBLE_MODULES = 'common.visible_modules';
	
	const SESSION_CAPTCHA = 'write_captcha';
	
    private function _getModules() {
    	static $modules = null;
		
    	// Lazy load
    	if(null == $modules) {
	    	$umsession = ChPortalHelper::getSession();
			@$active_contact = $umsession->getProperty('hb_login',null);
    		
			@$visible_modules = unserialize(DAO_CommunityToolProperty::get(ChPortalHelper::getCode(), self::PARAM_VISIBLE_MODULES, ''));
			
			if(is_array($visible_modules))
			foreach($visible_modules as $module_id => $visibility) {
				// Disabled
				if(0==strcmp($visibility, '2'))
					continue;

				// Must be logged in
				if(0==strcmp($visibility, '1') && empty($active_contact))
					continue;
				
				$module = DevblocksPlatform::getExtension($module_id,true,true); /* @var $module Extension_UmOsellotController */
				
				if(empty($module) || !$module instanceof Extension_Portal_Osellot_Controller)
					continue;
				
				@$module_uri = $module->manifest->params['uri'];
	
				if($module->isVisible())
					$modules[$module_uri] = $module;
			}
    	}
		
    	return $modules;
    }
    
    public static function getLoginExtensions() {
		$login_extensions = DevblocksPlatform::getExtensions('usermeet.login.authenticator');
		uasort($login_extensions, create_function('$a, $b', "return strcasecmp(\$a->name,\$b->name);\n"));
		return $login_extensions;
    }
    
    public static function getLoginExtensionsEnabled($instance_id) {
    	$login_extensions = self::getLoginExtensions();
		
    	$enabled = array();

		if(null != ($str = DAO_CommunityToolProperty::get($instance_id, self::PARAM_LOGIN_EXTENSIONS, ''))) {
			$ids = explode(',', $str);
			foreach($ids as $id) {
				if(isset($login_extensions[$id]))
					$enabled[$id] = $login_extensions[$id];
			}
		}
		
		return $enabled;
    }
    
    public static function getLoginExtensionActive($instance_id, $as_instance=true) {
    	$umsession = ChPortalHelper::getSession();
    	$enabled = self::getLoginExtensionsEnabled($instance_id);
    	
    	$login_method = $umsession->getProperty('login_method', '');
    	$manifest = null;

    	// If we have a preference cookied, return it
    	if(isset($enabled[$login_method]))
    		$manifest = $enabled[$login_method];

    	// Otherwise try to default to email+pass
    	if(empty($manifest) && isset($enabled['hb.login.auth.default']))
    		$manifest = $enabled['hb.login.auth.default'];
    		
    	// If all else fails, return the first enabled login handler
    	if(empty($manifest))
    		$manifest = array_shift($enabled);

    	if(empty($manifest))
    		return NULL;
    		
    	if($as_instance)
    		return $manifest->createInstance();
    	else
    		return $manifest;
    }
    
    public function handleRequest(DevblocksHttpRequest $request) {
    	$stack = $request->path;
        $module_uri = array_shift($stack);
        
		// Set locale in scope
        $default_locale = DAO_CommunityToolProperty::get(ChPortalHelper::getCode(), self::PARAM_DEFAULT_LOCALE, 'en_US');
		DevblocksPlatform::setLocale($default_locale);
		
		switch($module_uri) {
			case 'ajax':
				$controller = new UmOsellotAjaxController(null);
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
        $umsession = ChPortalHelper::getSession();
		$stack = $response->path;
		
		$module_uri = array_shift($stack);
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('portal_code', ChPortalHelper::getCode());
		
		$page_title = DAO_CommunityToolProperty::get(ChPortalHelper::getCode(), self::PARAM_PAGE_TITLE, 'Order');
		$tpl->assign('page_title', $page_title);

       	@$visible_modules = unserialize(DAO_CommunityToolProperty::get(ChPortalHelper::getCode(), self::PARAM_VISIBLE_MODULES, ''));
		$tpl->assign('visible_modules', $visible_modules);
		
        @$active_profile = $umsession->getProperty('hb_login', null);
        $tpl->assign('active_profile', $active_profile);

        $login_extensions_enabled = UmOsellotApp::getLoginExtensionsEnabled(ChPortalHelper::getCode());
        $tpl->assign('login_extensions_enabled', $login_extensions_enabled);
        
		// Usermeet Session
		if(null == ($fingerprint = ChPortalHelper::getFingerprint())) {
			die("A problem occurred.");
		}
        $tpl->assign('fingerprint', $fingerprint);
        
		switch($module_uri) {
			case 'rss':
				$controller = new UmOsellotRssController(null);
				$controller->handleRequest(new DevblocksHttpRequest($stack));
				break;
				
			case 'captcha':
				@$color = DevblocksPlatform::parseCsvString(DevblocksPlatform::importGPC($_REQUEST['color'],'string','40,40,40'));
				@$bgcolor = DevblocksPlatform::parseCsvString(DevblocksPlatform::importGPC($_REQUEST['bgcolor'],'string','240,240,240'));
				
				// Sanitize colors
				// [TODO] Sanitize numeric range for elements 0-2
				if(3 != count($color))
					$color = array(40,40,40);
				if(3 != count($bgcolor))
					$color = array(240,240,240);
				
                header('Cache-control: max-age=0', true); // 1 wk // , must-revalidate
                header('Expires: ' . gmdate('D, d M Y H:i:s',time()-604800) . ' GMT'); // 1 wk
				header('Content-type: image/jpeg');

		        // Get CAPTCHA secret passphrase
				$phrase = CerberusApplication::generatePassword(4);
		        $umsession->setProperty(UmOsellotApp::SESSION_CAPTCHA, $phrase);
                
				$im = @imagecreate(150, 70) or die("Cannot Initialize new GD image stream");
				$background_color = imagecolorallocate($im, $bgcolor[0], $bgcolor[1], $bgcolor[2]);
				$text_color = imagecolorallocate($im, $color[0], $color[1], $color[2]);
				$font = DEVBLOCKS_PATH . 'resources/font/ryanlerch_-_Tuffy_Bold(2).ttf';
				imagettftext($im, 24, mt_rand(0,20), 5, 60+6, $text_color, $font, $phrase);
				imagejpeg($im,null,85);
				imagedestroy($im);
				exit;
				break;
			
			case 'captcha.check':
				$entered = DevblocksPlatform::importGPC($_REQUEST['captcha'],'string','');
				$captcha = $umsession->getProperty(UmOsellotApp::SESSION_CAPTCHA, '');
				
				if(!empty($entered) && !empty($captcha) && 0 == strcasecmp($entered, $captcha)) {
					echo 'true';
					exit;
				}
				
				echo 'false';
				exit;
				
				break;
			
	    	default:
				// Build the menu
				$modules = $this->_getModules();
				$available_modules = array();
				if(is_array($modules))
				foreach($modules as $uri => $module) {
					// Must be menu renderable
					if(!empty($module->manifest->params['menu_title']) && !empty($uri)) {
						$available_modules[$uri] = $module;
					}
				}
		        
				// Modules
		        if(isset($modules[$module_uri])) {
					$module = $modules[$module_uri];
					array_unshift($stack, $module_uri);
		        } else {
					// Are they logged in?
		        	$module = reset($available_modules);
					if($active_profile == null) {
						$module = array_shift($available_modules);
						array_unshift($available_modules, $module);
					} else {
						array_shift($available_modules); // login
						$module = array_shift($available_modules); // account
					}
		        }
				
				$tpl->assign('module', $module);
				$tpl->assign('module_response', new DevblocksHttpResponse($stack));
				
   				$tpl->display('devblocks:osellot.core:portal_'.ChPortalHelper::getCode() . ":portal/index.tpl");
		    	break;
		}
	}
	
	/**
	 * @param $instance Model_CommunityTool 
	 */
    public function configure(Model_CommunityTool $instance) {
        $tpl = DevblocksPlatform::getTemplateService();
        
		// Locales
		
        $default_locale = DAO_CommunityToolProperty::get($instance->code, self::PARAM_DEFAULT_LOCALE, 'en_US');
		$tpl->assign('default_locale', $default_locale);
		
		$locales = DAO_Translation::getDefinedLangCodes();
		$tpl->assign('locales', $locales);

		// Personalization

        $page_title = DAO_CommunityToolProperty::get($instance->code, self::PARAM_PAGE_TITLE, 'Order');
		$tpl->assign('page_title', $page_title);

		// Modules

        @$visible_modules = unserialize(DAO_CommunityToolProperty::get($instance->code, self::PARAM_VISIBLE_MODULES, ''));
		$tpl->assign('visible_modules', $visible_modules);
		
		$all_modules = DevblocksPlatform::getExtensions('portal.controller.osellot', true, true);
		$modules = array();
		
		// Sort the enabled modules first, in order.
		if(is_array($visible_modules))
		foreach($visible_modules as $module_id => $visibility) {
			if(!isset($all_modules[$module_id]))
				continue;
			$module = $all_modules[$module_id];
			$modules[$module_id] = $module;
			unset($all_modules[$module_id]);
		}
		
		// Append the unused modules
		if(is_array($all_modules))
		foreach($all_modules as $module_id => $module) {
			$modules[$module_id] = $module;
			$modules = array_merge($modules, $all_modules);
		}
		
		$tpl->assign('modules', $modules);
		
        $tpl->display("devblocks:osellot.core::portal/hb/config/index.tpl");
    }
    
    public function saveConfiguration(Model_CommunityTool $instance) {
        @$aVisibleModules = DevblocksPlatform::importGPC($_POST['visible_modules'],'array',array());
        @$aIdxModules = DevblocksPlatform::importGPC($_POST['idx_modules'],'array',array());
        @$sPageTitle = DevblocksPlatform::importGPC($_POST['page_title'],'string','Contact Us');

		// Modules (toggle + sort)
		$aEnabledModules = array();
		foreach($aVisibleModules as $idx => $visible) {
			// If not hidden
			if(0 != strcmp($aVisibleModules[$idx],'2'))
				$aEnabledModules[$aIdxModules[$idx]] = $aVisibleModules[$idx];
		}
			
        DAO_CommunityToolProperty::set($instance->code, self::PARAM_VISIBLE_MODULES, serialize($aEnabledModules));
        DAO_CommunityToolProperty::set($instance->code, self::PARAM_PAGE_TITLE, $sPageTitle);

		// Default Locale
        @$sDefaultLocale = DevblocksPlatform::importGPC($_POST['default_locale'],'string','en_US');
		DAO_CommunityToolProperty::set($instance->code, self::PARAM_DEFAULT_LOCALE, $sDefaultLocale);

		// Allow modules to save their own config
		$modules = DevblocksPlatform::getExtensions('portal.controller.osellot',true,true);
		foreach($modules as $module) { /* @var $module Extension_Portal_Osellot_Controller */
			// Only save enabled
			if(!isset($aEnabledModules[$module->manifest->id]))
				continue;
				
			$module->saveConfiguration($instance);
		}

    }
};

class UmOsellotLoginAuthenticator extends Extension_OsellotLoginAuthenticator {
	function writeResponse(DevblocksHttpResponse $response) {
		$tpl = DevblocksPlatform::getTemplateService();
		$umsession = ChPortalHelper::getSession();

		$stack = $response->path;
		@$module = array_shift($stack);
		@$section = array_shift($stack);
		switch($module) {
			case 'agency':
				$tpl->display("devblocks:osellot.core:portal_".ChPortalHelper::getCode().":portal/agency/login/login.tpl");
				break;			
			case 'register':
				if(isset($section) && 0==strcasecmp('confirmed', $section)) {
					$tpl->display("devblocks:osellot.core:portal_".ChPortalHelper::getCode().":portal/login/registered.tpl");
				} else {
					$tpl->assign('title', 'Sign-up with the Good Food Box to order online');
					$tpl->display("devblocks:osellot.core:portal_".ChPortalHelper::getCode().":portal/login/register.tpl");
				}
				break;
			case 'forgot':
				if(isset($section) && 0==strcasecmp('confirm', $section)) {
					$tpl->display("devblocks:osellot.core:portal_".ChPortalHelper::getCode().":portal/login/forgot_confirm.tpl");
				} else {
					$tpl->display("devblocks:osellot.core:portal_".ChPortalHelper::getCode().":portal/login/forgot.tpl");
				}
				break;
			default:
				$tpl->display("devblocks:osellot.core:portal_".ChPortalHelper::getCode().":portal/login/login.tpl");
			break;
		}
	}

	function doRegisterAction() {
		@$email = DevblocksPlatform::importGPC($_REQUEST['email'],'string','');
		@$cemail = DevblocksPlatform::importGPC($_REQUEST['cemail'],'string','');
		@$pass = DevblocksPlatform::importGPC($_REQUEST['password'], 'string', '');
		@$cpass = DevblocksPlatform::importGPC($_REQUEST['cpassword'], 'string', '');
		@$name = DevblocksPlatform::importGPC($_REQUEST['name'], 'string', '');
		@$phone = DevblocksPlatform::importGPC($_REQUEST['phone'], 'string', '');
		
		$tpl = DevblocksPlatform::getTemplateService();
		$url_writer = DevblocksPlatform::getUrlService();
		$umsession = ChPortalHelper::getSession();

		try {
			// Validate
			$address_parsed = imap_rfc822_parse_adrlist($email,'host');
			if(empty($email) || empty($address_parsed) || !is_array($address_parsed) || empty($address_parsed[0]->host) || $address_parsed[0]->host=='host')
				throw new Exception("The email address you provided is invalid.");
				
			// Check to see if the address is currently assigned to an account
			if(null != ($address = DAO_Address::lookupAddress($email, false)) && !empty($address->contact_person_id))
				throw new Exception("The provided email address is already associated with an account.");
			
			// Check that email addresses are the same
			if(!$email == $cemail)
				throw new Exception("The provided email addresses did not match.");

			if(strlen($pass) < 12)
				throw new Exception("The provided password was not long enough");
				
			// Check that passwords are the same
			if(!$pass == $cpass)
				throw new Exception("The provided passwords did not match.");
			
			$name = explode(' ', $name);
			$parts = count($name);
			$first_name = array_shift($name);
			$last_name = array_pop($name);
			
			if(!$parts > 1)
				throw new Exception("You must enter both a First and Last name");
				
			if(empty($phone))
				throw new Exception("You must enter a phone number.");
			
//			Update the preferred email address
			$umsession->setProperty('register.email', $email);
			
			$fields = array(
				DAO_Address::EMAIL => $email,
				DAO_Address::FIRST_NAME => $first_name,
				DAO_Address::LAST_NAME => $last_name
			);
			
			if(null == $address = DAO_Address::lookupAddress($email)) {
				$id = DAO_Address::create($fields);
				$address = DAO_Address::get($id);
			} else {
				DAO_Address::update($address->id, $fields);
			}
			
			// Create the contact
			$salt = CerberusApplication::generatePassword(8);
			$fields = array(
				DAO_ContactPerson::EMAIL_ID => $address->id,
				DAO_ContactPerson::LAST_LOGIN => time(),
				DAO_ContactPerson::CREATED => time(),
				DAO_ContactPerson::AUTH_SALT => $salt,
				DAO_ContactPerson::AUTH_PASSWORD => md5($salt.md5($pass)),
				DAO_ContactPerson::PHONE => $phone
			);
			$contact_person_id = DAO_ContactPerson::create($fields);
			
			if(empty($contact_person_id) || null == ($contact = DAO_ContactPerson::get($contact_person_id)))
				throw new Exception("There was an error creating your account.");
			
			// Link email
			DAO_Address::update($address->id, array(
				DAO_Address::CONTACT_PERSON_ID => $contact_person_id,
			));
			
			
			// Quick send
			$msg = sprintf(
			"Thank you for registering: %s",
				urlencode($fields[DAO_ConfirmationCode::CONFIRMATION_CODE])
			);
			CerberusMail::quickSend($email,"Please confirm your email address", $msg);
			
			// Log in the session
			$umsession->login($contact);			

		} catch(Exception $e) {
			$tpl->assign('error', $e->getMessage());
			DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('portal',ChPortalHelper::getCode(),'login','register')));
			return;
		}

		DevblocksPlatform::redirect(new DevblocksHttpResponse(array('portal',ChPortalHelper::getCode(),'login','register','confirmed')));
	}

	function doRecoverAction() {
		@$email = DevblocksPlatform::importGPC($_REQUEST['email'],'string','');

		$tpl = DevblocksPlatform::getTemplateService();
		$url_writer = DevblocksPlatform::getUrlService();

		try {
			// Verify email is a contact
			if(null == ($address = DAO_Address::lookupAddress($email, false))) {
				throw new Exception("The email address you provided is not registered.");
			}
				
			if(empty($address->contact_person_id) || null == ($contact = DAO_ContactPerson::get($address->contact_person_id))) {
				throw new Exception("The email address you provided is not registered.");
			}
				
			// Generate + send confirmation
			$fields = array(
				DAO_ConfirmationCode::CONFIRMATION_CODE => CerberusApplication::generatePassword(8),
				DAO_ConfirmationCode::NAMESPACE_KEY => 'support_center.login.recover',
				DAO_ConfirmationCode::META_JSON => json_encode(array(
						'contact_id' => $contact->id,
						'address_id' => $address->id,
				)),
				DAO_ConfirmationCode::CREATED => time(),
			);
			DAO_ConfirmationCode::create($fields);

			// Quick send
			$msg = sprintf(
				"Your confirmation code: %s",
			urlencode($fields[DAO_ConfirmationCode::CONFIRMATION_CODE])
			);
			CerberusMail::quickSend($address->email,"Please confirm your email address", $msg);
				
			$tpl->assign('email', $address->email);
				
		} catch (Exception $e) {
			$tpl->assign('error', $e->getMessage());
			DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('portal',ChPortalHelper::getCode(),'login','forgot')));
			return;
		}

		DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('portal',ChPortalHelper::getCode(),'login','forgot','confirm')));
	}

	function recoverAccountAction() {
		@$email = DevblocksPlatform::importGPC($_REQUEST['email'],'string','');
		@$confirm = DevblocksPlatform::importGPC($_REQUEST['confirm'],'string','');

		$umsession = ChPortalHelper::getSession();
		$url_writer = DevblocksPlatform::getUrlService();
		$tpl = DevblocksPlatform::getTemplateService();

		try {
			// Verify email is a contact
			if(null == ($address = DAO_Address::lookupAddress($email, false))) {
				throw new Exception("The email address you provided is not registered.");
			}
				
			$tpl->assign('email', $address->email);
				
			if(empty($address->contact_person_id) || null == ($contact = DAO_ContactPerson::get($address->contact_person_id))) {
				throw new Exception("The email address you provided is not registered.");
			}
				
			// Lookup code
			if(null == ($code = DAO_ConfirmationCode::getByCode('support_center.login.recover', $confirm)))
			throw new Exception("Your confirmation code is invalid.");
				
			// Compare to contact
			if(!isset($code->meta['contact_id']) || $contact->id != $code->meta['contact_id'])
			throw new Exception("Your confirmation code is invalid.");

			// Compare to email address
			if(!isset($code->meta['address_id']) || $address->id != $code->meta['address_id'])
			throw new Exception("Your confirmation code is invalid.");

			// Success (delete token and one-time log in token)
			DAO_ConfirmationCode::delete($code->id);
			$umsession->login($contact);
			header("Location: " . $url_writer->write('c=account&a=password', true));
			exit;
				
		} catch (Exception $e) {
			$tpl->assign('error', $e->getMessage());
				
		}

		DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('portal',ChPortalHelper::getCode(),'login','forgot','confirm')));
	}

	/**
	 * pull auth info out of $_POST, check it, return user_id or false
	 *
	 * @return boolean whether login succeeded
	 */
	function authenticateAction() {
		$umsession = ChPortalHelper::getSession();
		$tpl = DevblocksPlatform::getTemplateService();
		$url_writer = DevblocksPlatform::getUrlService();
		$request = DevblocksPlatform::getHttpRequest();
		$stack = $request->path;
		
		array_shift($stack); // portal
		array_shift($stack); // portal id
		
		@$email = DevblocksPlatform::importGPC($_REQUEST['email']);
		@$pass = DevblocksPlatform::importGPC($_REQUEST['password']);
		
		$response = array('portal',ChPortalHelper::getCode(), 'login');
		// Clear the past session
		$umsession->logout();
		try {
			// Find the address
			if(null == ($addy = DAO_Address::lookupAddress($email, false)))
				throw new Exception("Login failed.");

			// Not registered
			if(empty($addy->contact_person_id) || null == ($contact = DAO_ContactPerson::get($addy->contact_person_id)))
				throw new Exception("Login failed.");
				
			// Compare salt
			if(0 != strcmp(md5($contact->auth_salt.md5($pass)),$contact->auth_password))
				throw new Exception("Login failed.");
				
			$umsession->login($contact);
			
			header("Location: " . $url_writer->write('', true));
			exit;
		} catch (Exception $e) {
			$tpl->assign('error', $e->getMessage());
		}
		

		DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse($response));
	}
};

abstract class Extension_Tab_Billing_Osellot extends DevblocksExtension {
	const POINT = 'tab.billing.osellot'; 
	
	public function showTab() {}
}

abstract class Extension_Gateway_Osellot extends DevblocksExtension {
	
	public function getSettings() {
		return json_decode($this->getParam('settings'), true);
	}
	
	public function saveSettings($settings = array()) {
		$this->setParam('settings', json_encode($settings));		
	}
	
	public function checkout() {}

	public function processTransaction() {}

	public function connect() {}
	
	public function configure() {}
	
};

class PageMenu_Setup_Osellot extends Extension_PageMenu {
	
	public function render() {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->display('devblocks:osellot.core::configuration/section/billing/menu.tpl');
	}
};