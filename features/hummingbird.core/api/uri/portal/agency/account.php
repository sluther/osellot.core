<?php
class AccountAgencyPortal_HummingbirdController extends Extension_Agency_Portal_Hummingbird_Controller {	
	public function writeResponse(DevblocksHttpResponse $response) {
		$tpl = DevblocksPlatform::getTemplateService();
		$umsession = ChPortalHelper::getSession();
		$active_profile = $umsession->getProperty('hb_login', null);
		
		$stack = $response->path;
		@array_shift($stack); // agency
		@array_shift($stack); // account
		$section = array_shift($stack);
		switch($section) {
			case 'history':
				$invoices = DAO_Invoice::getAllByAccount($active_profile->id);
				$tpl->assign('invoices', $invoices);
				$tpl->display('devblocks:hummingbird.core::portal/agency/account/history.tpl');
				break;
			default:
				$tpl->display('devblocks:hummingbird.core::portal/agency/account/index.tpl');
				break;
		}
	}
	
	function configure(Model_CommunityTool $instance) {

	}
	
	function saveConfiguration(Model_CommunityTool $instance) {

	}
	
	public function getTitle(DevblocksHttpResponse $response) {
		$stack = $response->path;
		$title = '';
		array_shift($stack); // login
		$section = array_shift($stack);
		switch($section) {
			case 'edit':
				$title = 'Edit account';
				break;
			case 'history':
				$title = 'Online order history';
				break;
			default:
				$title = 'My account';
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
			case 'edit':
				$header = 'Edit Account';
				break;
			case 'history':
				$header = 'Online Order History';
				break;
			default:
				$header = 'Order Online from the Good Food Box';
			break;
		}
	
		return $header;
	}
};