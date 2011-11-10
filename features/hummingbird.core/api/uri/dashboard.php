<?php
class DashboardTab_Billing_Hummingbird extends Extension_Tab_Billing_Hummingbird {
	
	public function showTab() {
		$tpl = DevblocksPlatform::getTemplateService();
		
// 		$items = DAO_Item::getWhere();
// 		$tpl->assign('items', $items);
		
		$products = DAO_Product::getAll();
		$tpl->assign('products', $products);
		
		$start = strtotime("Second wednesday of last month");
		$end = strtotime("Second wednesday of this month");
		
		$statuses = array('1', '2', '3');
		$orders = DAO_Invoice::getByDateRangeAndStatus($start, $end, $statuses);
		
		// Init item_totals
		$item_totals = array();
		
		foreach($products as $product) {
			$item_totals[$product->id] = 0;
		}		
		
		foreach($orders as $order) {
			$items = $order->getItems();
			foreach($items as $item) {
				$item_totals[$item->product_id] += $item->quantity;
			}	 
		}
		
		$tpl->assign('item_totals', $item_totals);
		$tpl->display('devblocks:hummingbird.core::billing/tabs/dashboard.tpl');		
	}
	
	public function calculateAction() {
		$tpl = DevblocksPlatform::getTemplateService();
		
		$row = DevblocksPlatform::importGPC($_REQUEST['row'], 'array', array());
		$item_totals = DevblocksPlatform::importGPC($_REQUEST['item_totals'], 'array', array());
		
// 		DAO_Invoice::getPaidByCutoffDate()

		$excluded = array('id', 'item', 'source', 'origin', 'unit', 'casesneeded');
		$zeroed = array('casesneeded');
		foreach($row as $index => $value) {
			// Force zero value for calculated fields
			if(in_array($index, $zeroed))
				$row[$index] = 0;
			elseif(empty($row[$index]) && !in_array($index, $excluded))
				$row[$index] = 1;
		}
		
		$row['unitcost'] = round($row['case'] / $row['unitscase'], 2);
		
		foreach($row['products'] as $key => $value) {
// 			multiply units * total boxes then add to casesneeded
			$row['casesneeded'] += $item_totals[$key] * $value;
			$row['productscost'][$key] = $row['unitcost'] * $value;
		}
		$row['casesneeded'] /= $row['unitscase'];
		$row['roundedup'] = ceil($row['casesneeded']);
		$row['remainder'] = ($row['roundedup'] - $row['casesneeded']) * $row['unitscase'];
		
		if($row['remainder'] < (0.03 * $row['roundedup'] * $row['unitscase']))
			$row['guidance'] = 'tight';
		elseif($row['remainder'] > (0.1 * $row['roundedup'] * $row['unitscase']))
			$row['guidance'] = 'loose';
		else
			$row['guidance'] = 'ok';
		
		$row['totalcost'] = ceil($row['case'] * $row['casesneeded']);
		
		unset($row['products']);
		
		
// 		unit cost = case/units per case
// 		total cost = case/cases needed rounded up
// 		cost per case = units per case * unit cost
		
		echo json_encode($row);
	}
}