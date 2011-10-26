<?php
class AccountPortal_HummingbirdController extends Extension_Portal_Hummingbird_Controller {	
	public function writeResponse(DevblocksHttpResponse $response) {
		$tpl = DevblocksPlatform::getTemplateService();
		$umsession = ChPortalHelper::getSession();
		$active_profile = $umsession->getProperty('hb_login', null);
		
		$stack = $response->path;
		@array_shift($stack); // account
		$section = array_shift($stack);
		switch($section) {
			case 'history':
				$invoices = DAO_Invoice::getPaidByAccount($active_profile->id);
				$tpl->assign('invoices', $invoices);
				$tpl->display('devblocks:hummingbird.core::portal/account/history.tpl');
				break;
			case 'edit':
				$tpl->display('devblocks:hummingbird.core::portal/account/edit.tpl');
				break;
			default:
				$tpl->display('devblocks:hummingbird.core::portal/account/index.tpl');
				break;
		}
	}
	
	function configure(Model_CommunityTool $instance) {

	}
	
	function saveConfiguration(Model_CommunityTool $instance) {

	}
	
	public function doEditAction() {
		@$current_email = DevblocksPlatform::importGPC($_REQUEST['current_email'],'string','');
		@$current_phone = DevblocksPlatform::importGPC($_REQUEST['current_phone'],'string','');
		@$current_pass = DevblocksPlatform::importGPC($_REQUEST['current_password'],'string','');
		@$email = DevblocksPlatform::importGPC($_REQUEST['email'],'string','');
		@$cemail = DevblocksPlatform::importGPC($_REQUEST['cemail'],'string','');
		@$pass = DevblocksPlatform::importGPC($_REQUEST['password'], 'string', '');
		@$cpass = DevblocksPlatform::importGPC($_REQUEST['cpassword'], 'string', '');
		@$phone = DevblocksPlatform::importGPC($_REQUEST['phone'], 'string', '');
		
		$tpl = DevblocksPlatform::getTemplateService();
		$url_writer = DevblocksPlatform::getUrlService();
		$umsession = ChPortalHelper::getSession();
		$active_profile = $umsession->getProperty('hb_login', null);
		
		$current_address = DAO_Address::lookupAddress($current_email, false);
		$contact_fields = array();
		$updated = false;
		
		try {
			
			if($active_profile->auth_password != md5($active_profile->auth_salt.md5($current_pass)))
				throw new Exception("The password you entered was incorrect.");
			
			// Validate
			$address_parsed = imap_rfc822_parse_adrlist($email,'host');
			if(empty($email) || empty($address_parsed) || !is_array($address_parsed) || empty($address_parsed[0]->host) || $address_parsed[0]->host=='host')
				throw new Exception("The email address you provided is invalid.");
			
			// Check to see if the address is currently assigned to an account
			if(null != ($address = DAO_Address::lookupAddress($email, false)) && !empty($address->contact_person_id) && $address->contact_person_id != $active_profile->id)
				throw new Exception("The provided email address is already associated with another account.");
			
			// Check that email addresses are the same
			if(!$email = $cemail)
				throw new Exception("The provided email addresses did not match.");
			
			if(!empty($pass)) {
				if(strlen($pass) < 12)
					throw new Exception("The provided password was not long enough");
				
				// Check that passwords are the same
				if(!$pass == $cpass)
					throw new Exception("The provided passwords did not match.");
				$updated = true;
				$salt = CerberusApplication::generatePassword(8);
				$contact_fields[DAO_ContactPerson::AUTH_SALT] = $salt;
				$contact_fields[DAO_ContactPerson::AUTH_PASSWORD] = md5($salt.md5($pass));
			}

			if($current_phone != $phone) {
				$updated = true;
				$contact_fields[DAO_ContactPerson::PHONE] = $phone;
			}
				
			// Update the preferred email address
			$umsession->setProperty('register.email', $email);
			
			if($current_email != $email) {
				
				$updated = true;
				$fields = array(
					DAO_Address::EMAIL => $email,
					DAO_Address::FIRST_NAME => $current_address->first_name,
					DAO_Address::LAST_NAME => $current_address->last_name
				);
				
				if(null == $address = DAO_Address::lookupAddress($email)) {
					$id = DAO_Address::create($fields);
					$address = DAO_Address::get($id);
				} else {
					DAO_Address::update($address->id, $fields);
				}
				
				$contact_fields[DAO_ContactPerson::EMAIL_ID] = $address->id;
				
				// Link email
				DAO_Address::update($address->id, array(
					DAO_Address::CONTACT_PERSON_ID => $active_profile->id,
				));
				
				// Unlink old email
				DAO_Address::update($current_address->id, array(
					DAO_Address::CONTACT_PERSON_ID => 0,
				));
			}
			
			if($updated) {
				DAO_ContactPerson::update($active_profile->id, $contact_fields);
				$umsession->setProperty('hb_login', DAO_ContactPerson::get($active_profile->id));
			}
		} catch(Exception $e) {
			$tpl->assign('error', $e->getMessage());
			return;
		}
		
	}
	
	public function getTitle(DevblocksHttpResponse $response) {
		$stack = $response->path;
		$title = '';
		array_shift($stack); // account
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
		array_shift($stack); // account
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