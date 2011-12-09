<?php
class PackingTab_Billing_Hummingbird extends Extension_Tab_Billing_Hummingbird {
	
	public function showTab() {
		$tpl = DevblocksPlatform::getTemplateService();
		
// 		$items = DAO_Item::getWhere();
// 		$tpl->assign('items', $items);
		
		$products = DAO_Product::getAll();
		$startdate = strtotime("December 1, 2011");
		$enddate = strtotime("January 11, 2012");
		
		$products = DAO_Product::getAll();
		
		$items = DAO_BoxItem::getByDateRange($startdate, $enddate);
		$sources = DAO_BoxItemSource::getAll();
		
		$tpl->assign('startdate', $startdate);
		$tpl->assign('enddate', $enddate);
		$tpl->assign('products', $products);
		$tpl->assign('items', $items);
		$tpl->assign('sources', $sources);
		$tpl->display('devblocks:hummingbird.core::billing/tabs/packing.tpl');		
	}
	
	public function calculateAction() {
		$tpl = DevblocksPlatform::getTemplateService();
		
		$row = DevblocksPlatform::importGPC($_REQUEST['row'], 'array', array());
		$item_totals = DevblocksPlatform::importGPC($_REQUEST['item_totals'], 'array', array());
		
// 		DAO_Invoice::getPaidByCutoffDate()

		$excluded = array('id', 'item', 'weighed', 'source', 'origin', 'unit', 'casesneeded');
		$zeroed = array('casesneeded');
		foreach($row as $index => $value) {
			// Force zero value for calculated fields
			if(in_array($index, $zeroed))
				$row[$index] = 0;
			elseif(empty($row[$index]) && !in_array($index, $excluded))
				$row[$index] = 1;
		}
		
		$row['unitcost'] = round($row['casecost'] / $row['unitspercase'], 2);
		
		foreach($row['products'] as $key => $value) {
// 			multiply units * total boxes then add to casesneeded
			$row['casesneeded'] += $item_totals[$key] * $value;
			$row['productscost'][$key] = $row['unitcost'] * $value;
		}
		$row['casesneeded'] /= $row['unitspercase'];
		$row['roundedup'] = ceil($row['casesneeded']);
		$row['remainder'] = ($row['casesrounded'] - $row['casesneeded']) * $row['unitspercase'];
		
		if($row['remainder'] < (0.03 * $row['roundedup'] * $row['unitspercase']))
			$row['guidance'] = 'tight';
		elseif($row['remainder'] > (0.1 * $row['roundedup'] * $row['unitspercase']))
			$row['guidance'] = 'loose';
		else
			$row['guidance'] = 'ok';
		
		$row['totalcost'] = ceil($row['casecost'] * $row['casesneeded']);
		
		unset($row['products']);
		
		
// 		unit cost = case/units per case
// 		total cost = case/cases needed rounded up
// 		cost per case = units per case * unit cost
		
		echo json_encode($row);
	}
	
	public function printPackingSheetAction() {
		$tpl = DevblocksPlatform::getTemplateService();

		@$product_id = DevblocksPlatform::importGPC($_REQUEST['product_id'], 'integer', 0);
	
		$startdate = strtotime("December 1, 2011");
		$enddate = strtotime("January 11, 2012");
		if(null !== ($product = DAO_Product::get($product_id))) {
			$products = array($product->id => $product);
		} else {
			$products = DAO_Product::getAll();
		}
	
		$items = DAO_BoxItem::getByDateRange($startdate, $enddate);
		$sources = DAO_BoxItemSource::getAll();
	
		$tpl->assign('startdate', $startdate);
		$tpl->assign('enddate', $enddate);
		$tpl->assign('products', $products);
		$tpl->assign('items', $items);
		$tpl->assign('sources', $sources);
		$tpl->display('devblocks:hummingbird.core::billing/tabs/packing/print.tpl');
	}
}