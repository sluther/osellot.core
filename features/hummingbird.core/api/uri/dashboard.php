<?php
class DashboardTab_Billing_Hummingbird extends Extension_Tab_Billing_Hummingbird {
	
	public function showTab() {
		$tpl = DevblocksPlatform::getTemplateService();
		
		$products = DAO_Product::getWhere();
		
		$tpl->assign('products', $products);
		
		$tpl->display('devblocks:hummingbird.core::billing/tabs/dashboard.tpl');		
	}
}