<?php
class DashboardTab_Billing_Hummingbird extends Extension_Tab_Billing_Hummingbird {
	
	public function showTab() {
		$tpl = DevblocksPlatform::getTemplateService();
		
// 		$items = DAO_Item::getWhere();
// 		$tpl->assign('items', $items);
		
		$products = DAO_Product::getWhere();
		$tpl->assign('products', $products);
		
		$tpl->display('devblocks:hummingbird.core::billing/tabs/dashboard.tpl');		
	}
	
	public function calculateAction() {
		$tpl = DevblocksPlatform::getTemplateService();
		
		$row = DevblocksPlatform::importGPC($_REQUEST['row'], 'array', array());
		
		// Extract the array indexes into normal vars for easier handling
		
		print $id;
		print $item;
		foreach($products as $product) {
			// multiply units * total boxes then add to casesneeded			
		}
		
// 		cases needed = ((units per box * total boxes of that type) + (repeat the previous one per each kind of box...) / units per case
// 		cases needed = ((units per box * total boxes of that type) + (repeat the previous one per each kind of box…)) / units per case
// 		remainder in units = round_down( (rounded up cases needed - cases needed) * units per case )
		
// 		Guidance is… if remainder in units < (0.03 * rounded up cases needed * units per case)  = tight, else if remainder in units > (0.1 * rounded up cases needed * units per case) = loose,
// 		unit cost = case/units per case
// 		total cost = case/cases needed rounded up
// 		cost per case = units per case * unit cost
		
	}
}