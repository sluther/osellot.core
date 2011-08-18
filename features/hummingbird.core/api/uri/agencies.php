<?php
class AgenciesTab_Billing_Hummingbird extends Extension_Tab_Billing_Hummingbird {
	const EXTENSION_ID = 'agencies.tab.billing.hummingbird';
	const VIEW_ACTIVITY_AGENCIES = 'agencies';
	
	public function showTab() {
		$tpl = DevblocksPlatform::getTemplateService();
		$visit = CerberusApplication::getVisit();
		$translate = DevblocksPlatform::getTranslationService();
		$active_worker = CerberusApplication::getActiveWorker();
		
		// Index
		$defaults = new C4_AbstractViewModel();
		$defaults->class_name = 'View_Agency';
		$defaults->id = self::VIEW_ACTIVITY_AGENCIES;
		$defaults->name = $translate->_('agencies.tab.billing');
		$defaults->renderSortBy = SearchFields_ContactPerson::POSITION;
		$defaults->renderSortAsc = 0;
		
		$view = C4_AbstractViewLoader::getView(self::VIEW_ACTIVITY_AGENCIES, $defaults);
		
		$view->addParamsRequired(array(
			SearchFields_ContactPerson::IS_AGENCY => new DevblocksSearchCriteria(SearchFields_ContactPerson::IS_AGENCY,'=',1),
		), true);
		
		C4_AbstractViewLoader::setView($view->id, $view);
		
		$quick_search_type = $visit->get('crm.opps.quick_search_type');
		$tpl->assign('quick_search_type', $quick_search_type);
		
		$tpl->assign('view', $view);
		
		$tpl->display('devblocks:hummingbird.core::billing/tabs/agencies.tpl');		
	}
	
	public function showAgencyPanelAction() {
		$tpl = DevblocksPlatform::getTemplateService();
		
		@$agency_id = DevblocksPlatform::importGPC($_REQUEST['id'],'integer',0);
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id'],'string','');
		@$email = DevblocksPlatform::importGPC($_REQUEST['email'],'string','');
		
		$tpl->assign('view_id', $view_id);
		$tpl->assign('email', $email);
		
		// Handle context links ([TODO] as an optional array)
		@$context = DevblocksPlatform::importGPC($_REQUEST['context'],'string','');
		@$context_id = DevblocksPlatform::importGPC($_REQUEST['context_id'],'integer','');
		$tpl->assign('context', $context);
		$tpl->assign('context_id', $context_id);
		
		if(!empty($agency_id) && null != ($agency = DAO_Contact_Person::getAgency($agency_id))) {
			$tpl->assign('agency', $agency);
				
			if(null != ($address = DAO_Address::get($agency->email_id))) {
				$tpl->assign('address', $address);
			}
		}
		
// 		$custom_fields = DAO_CustomField::getByContext(CerberusContexts::CONTEXT_OPPORTUNITY);
// 		$tpl->assign('custom_fields', $custom_fields);
		
// 		if(!empty($opp_id)) {
// 			$custom_field_values = DAO_CustomFieldValue::getValuesByContextIds(CerberusContexts::CONTEXT_OPPORTUNITY, $opp_id);
// 			if(isset($custom_field_values[$opp->id]))
// 			$tpl->assign('custom_field_values', $custom_field_values[$opp->id]);
// 		}
		
// 		Comments
// 		$comments = DAO_Comment::getByContext(CerberusContexts::CONTEXT_OPPORTUNITY, $opp_id);
// 		$last_comment = array_shift($comments);
// 		unset($comments);
// 		$tpl->assign('last_comment', $last_comment);

		
		$tpl->display('devblocks:hummingbird.core::billing/tabs/agencies/panel.tpl');
	}
	
	function saveAgencyPanelAction() {
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id'],'string','');
	
		@$agency_id = DevblocksPlatform::importGPC($_REQUEST['agency_id'],'integer',0);
		@$name = DevblocksPlatform::importGPC($_REQUEST['name'],'string','');
		@$email = DevblocksPlatform::importGPC($_REQUEST['email'],'string','');
		@$password = DevblocksPlatform::importGPC($_REQUEST['password'],'string','');
		@$address_line1 = DevblocksPlatform::importGPC($_REQUEST['address_line1'], 'string', '');
		@$address_line2 = DevblocksPlatform::importGPC($_REQUEST['address_line2'], 'string', '');
		@$address_city = DevblocksPlatform::importGPC($_REQUEST['address_city'], 'string', '');
		@$address_province = DevblocksPlatform::importGPC($_REQUEST['address_province'], 'string', '');
		@$address_postal = DevblocksPlatform::importGPC($_REQUEST['address_postal'], 'string', '');
		@$do_delete = DevblocksPlatform::importGPC($_REQUEST['do_delete'],'integer',0);
		
		// Dates
		$created_date = time();
				
		// Worker
		$active_worker = CerberusApplication::getActiveWorker();
	
		// Save
		if($do_delete) {
			if(null != ($agency = DAO_Agency::get($agency_id)) && $active_worker->hasPriv('crm.opp.actions.create')) {
				DAO_Agency::delete($agency_id);
				$agency_id = null;
			}
				
		} elseif(empty($agency_id)) {
			// Check privs
			
			// One agency per provided e-mail address
			if(null == ($address = DAO_Address::lookupAddress($email, true)))
				return;
			
			if(null !== ($agency = DAO_ContactPerson::getAgency($address->contact_person_id)))
				return;
			
			if(empty($password))
				return;
			
			if($address->contact_person_id)
				return;
			
// 			if(empty($password)) {
// 				$password = CerberusApplication::generatePassword(8);
// 			}
			
			$salt = CerberusApplication::generatePassword(8);
			$fields = array(
				DAO_ContactPerson::NAME => $name,
				DAO_ContactPerson::EMAIL_ID => $address->id,
				DAO_ContactPerson::CREATED => intval($created_date),
				DAO_ContactPerson::AUTH_PASSWORD => md5($salt.md5($password)),
				DAO_ContactPerson::AUTH_SALT => $salt,
				DAO_ContactPerson::ADDRESS_LINE1 => $address_line1,
				DAO_ContactPerson::ADDRESS_LINE2 => $address_line2,
				DAO_ContactPerson::ADDRESS_CITY => $address_city,
				DAO_ContactPerson::ADDRESS_PROVINCE => $address_province,
				DAO_ContactPerson::ADDRESS_POSTAL => $address_postal,
				DAO_ContactPerson::IS_AGENCY => 1
			);
			$agency_id = DAO_ContactPerson::create($fields);
			DAO_Address::update($address->id, array(DAO_Address::CONTACT_PERSON_ID => $agency_id));
			
			// Force new agencies to the end
			DAO_ContactPerson::update($agency_id, array(DAO_Agency::POSITION => $agency_id));				
		} else {
			if(empty($agency_id))
				return;
			
			if(null == ($address = DAO_Address::lookupAddress($email, true)))
				return;
			
			$fields = array(
				DAO_ContactPerson::EMAIL_ID => $address->id,
				DAO_ContactPerson::ADDRESS_LINE1 => $address_line1,
				DAO_ContactPerson::ADDRESS_LINE2 => $address_line2,
				DAO_ContactPerson::ADDRESS_CITY => $address_city,
				DAO_ContactPerson::ADDRESS_PROVINCE => $address_province,
				DAO_ContactPerson::ADDRESS_POSTAL => $address_postal
			);
			
			if(!empty($password)) {
				$salt = CerberusApplication::generatePassword(8);
				$fields[DAO_ContactPerson::AUTH_PASSWORD] = md5($salt.md5($password));
				$fields[DAO_ContactPerson::AUTH_SALT] = $salt;
			}
			// Valid agency?
			if(null != ($agency = DAO_ContactPerson::getAgency($agency_id))) {
				DAO_ContactPerson::update($agency_id, $fields);
				DAO_Address::update($address->id, array(DAO_Address::CONTACT_PERSON_ID => $agency->id));
			}
		}
	
		// Reload view (if linked)
		if(!empty($view_id) && null != ($view = C4_AbstractViewLoader::getView($view_id))) {
			$view->render();
		}
	
		exit;
	}
	
}