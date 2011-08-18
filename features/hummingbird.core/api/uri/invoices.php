<?php
class InvoicesTab_Billing_Hummingbird extends Extension_Tab_Billing_Hummingbird {
	
	public function showTab() {
		$tpl = DevblocksPlatform::getTemplateService();
		
		$tpl->display('devblocks:hummingbird.core::billing/tabs/invoices.tpl');		
	}
}