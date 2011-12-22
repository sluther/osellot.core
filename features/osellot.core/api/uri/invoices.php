<?php
class InvoicesTab_Billing_Osellot extends Extension_Tab_Billing_Osellot {
	
	public function showTab() {
		$tpl = DevblocksPlatform::getTemplateService();
		
		$tpl->display('devblocks:osellot.core::billing/tabs/invoices.tpl');		
	}
}