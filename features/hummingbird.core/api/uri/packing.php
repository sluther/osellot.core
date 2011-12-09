<?php
class PackingTab_Billing_Hummingbird extends Extension_Tab_Billing_Hummingbird {
	
	public function showTab() {
		$tpl = DevblocksPlatform::getTemplateService();
		
// 		$items = DAO_Item::getWhere();
// 		$tpl->assign('items', $items);
		
		$products = DAO_Product::getAll();
		$tpl->assign('products', $products);
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
	
	public function saveBoxItemsAction() {
		@$rows = DevblocksPlatform::importGPC($_REQUEST['row'], 'array', array());
		@$startdate = DevblocksPlatform::importGPC($_REQUEST['startdate'], 'integer', 0);
		@$enddate = DevblocksPlatform::importGPC($_REQUEST['enddate'], 'integer', 0);
		
		foreach($rows as $row) {
			if(!empty($row['item'])) {
				if(null == ($box_item = DAO_BoxItem::get($row['id']))) {
					DAO_BoxItem::create(array(
						DAO_BoxItem::ITEM => $row['item'],
						DAO_BoxItem::SOURCE => $row['source'],
						DAO_BoxItem::ORIGIN => $row['origin'],
						DAO_BoxItem::UNIT => $row['unit'],
						DAO_BoxItem::WEIGHED => $row['weighed'],
						DAO_BoxItem::CASECOST => $row['casecost'],
						DAO_BoxItem::UNITSPERCASE => $row['unitspercase'],
						DAO_BoxItem::UNITCOST => $row['unitcost'],
						DAO_BoxItem::CASESNEEDED => $row['casesneeded'],
						DAO_BoxItem::CASESROUNDED => $row['casesrounded'],
						DAO_BoxItem::REMAINDER => $row['remainder'],
						DAO_BoxItem::GUIDANCE => $row['guidance'],
						DAO_BoxItem::STARTDATE => $startdate,
						DAO_BoxItem::ENDDATE => $enddate,
						DAO_BoxItem::TOTALCOST => $row['totalcost'],
						DAO_BoxItem::PRODUCTS => json_encode($row['products'])
					));
				} else {
					DAO_BoxItem::update($box_item->id, array(
						DAO_BoxItem::ITEM => $row['item'],
						DAO_BoxItem::SOURCE => $row['source'],
						DAO_BoxItem::ORIGIN => $row['origin'],
						DAO_BoxItem::UNIT => $row['unit'],
						DAO_BoxItem::WEIGHED => $row['weighed'],
						DAO_BoxItem::CASECOST => $row['casecost'],
						DAO_BoxItem::UNITSPERCASE => $row['unitspercase'],
						DAO_BoxItem::UNITCOST => $row['unitcost'],
						DAO_BoxItem::CASESNEEDED => $row['casesneeded'],
						DAO_BoxItem::CASESROUNDED => $row['casesrounded'],
						DAO_BoxItem::REMAINDER => $row['remainder'],
						DAO_BoxItem::GUIDANCE => $row['guidance'],
						DAO_BoxItem::TOTALCOST => $row['totalcost'],
						DAO_BoxItem::PRODUCTS => json_encode($row['products'])
					));
				}
			}
		}
		
// 		DevblocksPlatform::redirect(new DevblocksHttpResponse(array('billing')));
	}
}