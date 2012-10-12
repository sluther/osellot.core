<?php

class OsellotInvoicesPage extends CerberusPageExtension {
	
	function isVisible() {
		// The current session must be a logged-in worker to use this page.
		if(null == ($worker = CerberusApplication::getActiveWorker()))
			return false;
		return true;
	}
	
	function saveInvoicePeekAction() {
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id'],'string','');
		
		@$id = DevblocksPlatform::importGPC($_REQUEST['id'],'integer',0);
		@$price = DevblocksPlatform::importGPC($_REQUEST['price'], 'integer', 0);
		@$price_setup = DevblocksPlatform::importGPC($_REQUEST['price_setup'], 'integer', 0);
		@$recurring = DevblocksPlatform::importGPC($_REQUEST['recurring'], 'integer', 0);
		@$taxable = DevblocksPlatform::importGPC($_REQUEST['taxable'], 'integer', 0);
		@$sku = DevblocksPlatform::importGPC($_REQUEST['sku'], 'string', '');
		@$name = DevblocksPlatform::importGPC($_REQUEST['name'], 'string', '');
		@$description = DevblocksPlatform::importGPC($_REQUEST['description'], 'string', '');
		@$do_delete = DevblocksPlatform::importGPC($_REQUEST['do_delete'],'integer',0);
		
		// Dates
		$created_date = time();
		
		// Worker
		$active_worker = CerberusApplication::getActiveWorker();
	
		// Name must be set
		if(empty($name))
			return;
		
		// Save
		if(!empty($product_id) && !empty($do_delete)) {
			if(null != ($product = DAO_Product::get($product_id))) {
				DAO_Product::delete($product_id);
			}
		} else {
			$fields = array(
				DAO_Product::PRICE => $price,
				DAO_Product::PRICE_SETUP => $price_setup,
				DAO_Product::RECURRING => $recurring,
				DAO_Product::SKU => $sku,
				DAO_Product::NAME => $name,
				DAO_Product::DESCRIPTION => $description
			);
			
			if(!empty($id)) {
				// SKU is unique
				if(null !== ($product = DAO_Product::getBySKU($sku))) {
					if($product->id != $id) {
						return;
					}
				}
				
				// Valid product?
				if(null !== ($product = DAO_Product::get($id))) {
					DAO_Product::update($id, $fields);
				}
			} else {
				// SKU is unique
				if(null !== ($product = DAO_Product::getBySKU($sku))) {
					return;
				}
				
				// Name must be set
				if(empty($name))
					return;
				
				//$fields[DAO_Product::]
				$id = DAO_Product::create($fields);
				
				// View marquee
				if(!empty($id) && !empty($view_id)) {
					C4_AbstractView::setMarqueeContextCreated($view_id, 'osellot.contexts.product', $id);
				}
			}
		}
		exit;
	}
	
	function showImportPopupAction() {
		@$layer = DevblocksPlatform::importGPC($_REQUEST['layer'],'string');
		@$context = DevblocksPlatform::importGPC($_REQUEST['context'],'string','');
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id'],'string','');
		
		if(null == ($context_ext = Extension_DevblocksContext::get($context)))
			return;
		
		
		$visit = CerberusApplication::getVisit();
		
		// Delete old CSV
		$csv_file = $visit->get('import.last.csv','');
		@unlink($csv_file); // nuke the imported file
		
		// [TODO] ACL
// 		$active_worker = CerberusApplication::getActiveWorker();
// 		if(!$active_worker->hasPriv('crm.opp.actions.import'))
// 			return;

		if(!($context_ext instanceof IDevblocksContextImport))
			return;
		
		$tpl = DevblocksPlatform::getTemplateService();

		// Template
		
		$tpl->assign('layer', $layer);
		$tpl->assign('context', $context_ext->id);
		$tpl->assign('view_id', $view_id);
		
		$tpl->display('devblocks:osellot.core::invoices/import/upload.tpl');
	}
	
	function showParsePopupAction() {
		@$layer = DevblocksPlatform::importGPC($_REQUEST['layer'],'string');
		@$context = DevblocksPlatform::importGPC($_REQUEST['context'],'string','');
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id'],'string','');
	
		$tpl = DevblocksPlatform::getTemplateService();
		$visit = CerberusApplication::getVisit();
		
		if(null == ($context_ext = Extension_DevblocksContext::get($context)))
			return;
		
		// [TODO] ACL
		// 		$active_worker = CerberusApplication::getActiveWorker();
		// 		if(!$active_worker->hasPriv('crm.opp.actions.import'))
		// 			return;
	
		if(!($context_ext instanceof IDevblocksContextImport))
			return;
		
		$line_number = 0;
		
		// Read the first line from the file
		$csv_file = $visit->get('import.last.csv','');
		$fp = fopen($csv_file, 'rt');
		$cols = fgetcsv($fp, 8192, ',', '"');
		$rows = array();
		
		$station_products = array();
		
		$columns = array($cols[2], $cols[3], $cols[4]);
		
		while(!feof($fp)) {
			$line_number++;
			
			$row = fgetcsv($fp, 8192, ',', '"');
			
			if(empty($row) || (1==count($row) && is_null($row[0])))
				continue;
			
			$station_number = $row[1];
			if(!isset($station_products[$station_number]))
				if(null !== ($station = DAO_Station::getByNumber($station_number))) {
					$links = DAO_ContextLink::getContextLinks('dejero.contexts.station', $station->id, 'osellot.contexts.product');
					$station_products[$station_number] = array();
					if(!empty($links)) {
						$link = array_shift($links[$station->id]);
						$station_products[$station_number][$link->context_id] = $link;
					}
				}
			
			$rows[$line_number] = array($row[1], $row[2], $row[3], $row[4]);
		}
		
		fclose($fp);
		
		$tpl->assign('columns', $columns);
		$tpl->assign('rows', $rows);
		
		// Products
		$products = DAO_Product::getAll();
		$tpl->assign('products', $products);
		
		// Stations
		$tpl->assign('station_products', $station_products);
		
		// Template
		$tpl->assign('layer', $layer);
		$tpl->assign('context', $context_ext->id);
		$tpl->assign('view_id', $view_id);
		
		$tpl->display('devblocks:osellot.core::invoices/import/parse.tpl');
	}
	
	function parseImportFileAction() {
		@$csv_file = $_FILES['csv_file'];
		
		// [TODO] Return false in JSON if file is empty, etc.
		
		if(!is_array($csv_file) || !isset($csv_file['tmp_name']) || empty($csv_file['tmp_name'])) {
			exit;
		}
		
		$filename = basename($csv_file['tmp_name']);
		$newfilename = APP_TEMP_PATH . '/' . $filename;
		
		if(!rename($csv_file['tmp_name'], $newfilename)) {
			exit;
		}
		
		$visit = CerberusApplication::getVisit();
		$visit->set('import.last.csv', $newfilename);
		
		exit;
	}
	
	function doImportAction() {
		$transmitters = DevblocksPlatform::importGPC($_REQUEST['transmitters'], array(), array());
		$is_preview = DevblocksPlatform::importGPC($_REQUEST['is_preview'], 'integer', 0);
		
		$active_worker = CerberusApplication::getActiveWorker();
		
		$accounts = array();
		
		foreach($transmitters as $line_number => $transmitter) {
			$transmitter_product = $transmitter['transmitter_product'];
			$save_plan = $transmitter['save_plan'];
			
			if(null == ($product = DAO_Product::get($transmitter_product))) {
				continue;
			}
			
			// Get attributes
			$attributes = $product->getAttributes();
			
			$transmitter['product'] = $product;
			
			// Calculate the total included minutes
			if($attributes['pooled']) {
				if(!isset($accounts[$transmitter['station_number']]))
					$accounts[$transmitter['station_number']] = array(
						'total_sd' => 0,
						'total_hd' => 0,
						'available_sd' => 0,
						'available_hd' => 0,
						'used_sd' => 0,
						'used_hd' => 0
					);
				$accounts[$transmitter['station_number']]['total_hd'] += $attributes['included_hd'];
				$accounts[$transmitter['station_number']]['total_sd'] += $attributes['included_sd'];
				$accounts[$transmitter['station_number']]['available_hd'] += $attributes['included_hd'];
				$accounts[$transmitter['station_number']]['available_sd'] += $attributes['included_sd'];
			} else {
				$transmitter['total_hd'] = $attributes['included_hd'];
				$transmitter['total_sd'] = $attributes['included_sd'];
				$transmitter['available_hd'] = $attributes['included_hd'];
				$transmitter['available_sd'] = $attributes['included_sd'];
			}
			
			$transmitters[$line_number] = $transmitter;
		}
		
// 		print '<pre>' . print_r($accounts, 1) . '</pre>';
// 		if(!$active_worker->hasPriv('crm.opp.actions.import'))
// 			return;
		
		$visit = CerberusApplication::getVisit();
		
		// Counters
		$line_number = 0;
		
		// CSV
		$csv_file = $visit->get('import.last.csv','');
		
		$fp = fopen($csv_file, "rt");
		if(!$fp)
			return;
		
		// Do we need to consume a first row of headings?
		@fgetcsv($fp, 8192, ',', '"');
		
// 		[0] => MobileUnitID
// 		[1] => StationID
// 		[2] => MobileUnitName
// 		[3] => StationName
// 		[4] => Serial
// 		[5] => HDUsage
// 		[6] => SDUsage
// 		[7] => FileTransferUsage
// 		[8] => ClipHDUsage
// 		[9] => ClipHDTransferUsage
// 		[10] => ClipSDUsage
// 		[11] => ClipSDTransferUsage
// 		[12] => BillHDUsage
// 		[13] => BillSDUsage
// 		[14] => BillFileTransferUsage
// 		[15] => BillClipHDUsage
// 		[16] => BillClipHDTransferUsage
// 		[17] => BillClipSDUsage
// 		[18] => BillClipSDTransferUsage
		
		$rows = array();
		$bill_sd_usage_amount = 0;
		$bill_hd_usage_amount = 0;
		while(!feof($fp)) {
			$line_number++;
			
			$row = fgetcsv($fp, 8192, ',', '"');
			
			if(empty($row) || (1==count($row) && is_null($row[0])))
				continue;
			
			$transmitter_product = $transmitters[$line_number]['transmitter_product'];
			
			if(null !== ($station = DAO_Station::getByNumber($row[1])))
				$products = DAO_ContextLink::getContextLinks('dejero.contexts.station', $station->id, 'osellot.contexts.product');
			
			print '<pre>' . print_r($products, 1) . '</pre>';
			// Station
			$station_number = $row[1];
			$station_name = $row[3];
			
			$fields = array(
				DAO_Station::NUMBER => $station_number,
				DAO_Station::NAME => $station_name,
			);
			
			if(null !== ($station = DAO_Station::getByNumber($station_number)))	{
				DAO_Station::update($station->id, $fields);
				$station_id = $station->id;
			} else {
				$station_id = DAO_Station::create($fields);
			}
			
			if($transmitters[$line_number]['save_plan']) {
				print 'Saving plan<br>';
				DAO_ContextLink::setLink('dejero.contexts.station', $station_id, 'osellot.contexts.product', $transmitter_product);
			}
			
			// Transmitter
			$transmitter_id = &$row[0];
			$transmitter_name = &$row[2];
			$transmitter_serial = &$row[4];
			
			// Total Live Usage
			$total_hd_usage = &$row[5];
			$total_sd_usage = &$row[6];
			
			// Total File Transfer Usage
			$total_file_transfer = &$row[7];
			
			// Total Clip Usage
			$total_clip_hd_usage_minutes = &$row[8];
			$total_clip_hd_usage_megabytes = &$row[9];
			$total_clip_sd_usage_minutes = &$row[10];
			$total_clip_sd_usage_megabytes = &$row[11];
			
			// Bill Live Usage
			$bill_hd_usage = &$row[12];
			$bill_sd_usage = &$row[13];
			
			// Bill File Transfer Usage
			$bill_file_transfer = &$row[14];
			
			// Bill Clip Usage
			$bill_clip_hd_usage_minutes = &$row[15];
			$bill_clip_hd_usage_megabytes = &$row[16];
			$bill_clip_sd_usage_minutes = &$row[17];
			$bill_clip_sd_usage_megabytes = &$row[18];
			
			// Convert to seconds
			sscanf($total_hd_usage, "%d:%d:%d", $hours, $minutes, $seconds);
			$total_hd_usage = round($hours * 60 +  $minutes + $seconds/60, 1);
			
			sscanf($total_sd_usage, "%d:%d:%d", $hours, $minutes, $seconds);
			$total_sd_usage = round($hours * 60 +  $minutes + $seconds/60, 1);
			
			sscanf($total_clip_sd_usage_minutes, "%d:%d:%d", $hours, $minutes, $seconds);
			$total_clip_hd_usage_minutes = round($hours * 60 +  $minutes + $seconds/60, 1);
			
			sscanf($total_clip_hd_usage_minutes, "%d:%d:%d", $hours, $minutes, $seconds);
			$row[10] = round($hours * 60 +  $minutes + $seconds/60, 1);
			
			sscanf($bill_hd_usage, "%d:%d:%d", $hours, $minutes, $seconds);
			$bill_hd_usage = round($hours * 60 +  $minutes + $seconds/60, 1);
			
			sscanf($bill_sd_usage, "%d:%d:%d", $hours, $minutes, $seconds);
			$bill_sd_usage = round($hours * 60 +  $minutes + $seconds/60, 1);
			
			sscanf($bill_clip_hd_usage_minutes, "%d:%d:%d", $hours, $minutes, $seconds);
			$bill_clip_hd_usage_minutes = round($hours * 60 +  $minutes + $seconds/60, 1);
			
			sscanf($bill_clip_sd_usage_minutes, "%d:%d:%d", $hours, $minutes, $seconds);
			$bill_clip_sd_usage_minutes = round($hours * 60 +  $minutes + $seconds/60, 1);
			
			// Parse data sizes
			$pattern = '/[\w.]+/i';
			
			preg_match($pattern, $total_file_transfer, $matches);
			$total_file_transfer = $matches[0];
			
			preg_match($pattern, $total_clip_hd_usage_megabytes, $matches);
			$total_clip_hd_usage_megabytes = $matches[0];
			
			preg_match($pattern, $total_clip_sd_usage_megabytes, $matches);
			$total_clip_sd_usage_megabytes = $matches[0];
			
			preg_match($pattern, $bill_clip_hd_usage_megabytes, $matches);
			$bill_clip_hd_usage_megabytes = $matches[0];
			
			preg_match($pattern, $bill_clip_sd_usage_megabytes, $matches);
			$bill_clip_sd_usage_megabytes = $matches[0];
			
			$product = $transmitters[$line_number]['product'];
			
			$attributes = $product->getAttributes();
			
			// Calculate ratios
			$included = $attributes['included_sd'] / $attributes['included_hd'];
			$overage = (1 / $attributes['rate_sd_minutes']) / (1 / $attributes['rate_hd_minutes']);
			
			if($included >= $overage) {
				// Subtract SD usage first
				if($attributes['pooled']) {
					print $station_number . '<br>';
					$total_sd = $accounts[$station_number]['total_sd'];
					$total_hd = $accounts[$station_number]['total_hd'];
					$available_sd = &$accounts[$station_number]['available_sd'];
					$available_hd = &$accounts[$station_number]['available_hd'];
					$used_sd = &$accounts[$station_number]['used_sd'];
					$used_hd = &$accounts[$station_number]['used_sd'];
				} else {
					$total_sd = $transmitters[$line_number]['total_sd'];
					$total_hd = $transmitters[$line_number]['total_hd'];
					$available_sd = &$transmitters[$line_number]['available_sd'];
					$available_hd = &$transmitters[$line_number]['available_hd'];
					$used_sd = 0;
					$used_hd = 0;
				}
				$available_sd -= $bill_sd_usage;
				$used_sd += $bill_sd_usage;
				
				if($available_sd > 0) {
					$available_hd = $available_sd / $total_sd * $total_hd;
				} else {
					$available_hd = 0;
				}
			} else {
				// Subtract HD usage first
				if(isset($accounts[$station_number])) {
					print $station_number . '<br>';
					$total_sd = $accounts[$station_number]['total_sd'];
					$total_hd = $accounts[$station_number]['total_hd'];
					$available_sd = &$accounts[$station_number]['available_sd'];
					$available_hd = &$accounts[$station_number]['available_hd'];
					$used_sd = &$accounts[$station_number]['used_sd'];
					$used_hd = &$accounts[$station_number]['used_sd'];
				} else {
					$total_sd = $transmitter['total_sd'];
					$total_hd = $transmitter['total_hd'];
					$available_sd = &$transmitter['available_sd'];
					$available_hd = &$transmitter['available_hd'];
				}
				
				$available_hd -= $bill_hd_usage;
				$used_hd += $bill_hd_usage;
				
				if($available_hd > 0) {
					$available_sd = $available_sd / $total_sd * $total_hd;
				} else {
					$available_sd = 0;
				}
			}
			
			if($available_hd < 0) {
				print abs($available_hd) . ' * ' . $attributes['rate_hd_minutes'] . ' = ';
				$bill_hd_usage_amount = abs($available_hd) * $attributes['rate_hd_minutes'];
				print '$' . $bill_hd_usage_amount . '<br>';
			}
			
			if($available_sd < 0) {
				print abs($available_sd) . ' * ' . $attributes['rate_sd_minutes'] . ' = ';
				$bill_sd_usage_amount = abs($available_sd) * $attributes['rate_sd_minutes'];
				print '$' . $bill_sd_usage_amount . '<br>';
			}
			
			$row[19] = $bill_hd_usage_amount;
			$row[20] = $bill_sd_usage_amount;
			
// 			print '<pre>' . print_r($attributes, 1) . '</pre>';
// 			print '<pre>' . print_r($row, 1) . '</pre>';
			if(null == $transmitter = DAO_Transmitter::getByStationAndSerial($station->id, $row[4])) {
				$fields = array(
					DAO_Transmitter::STATION_ID => $station->id,
					DAO_Transmitter::TRANSMITTER_ID => $transmitter_id,
					DAO_Transmitter::PRODUCT_ID => $transmitter_product,
					DAO_Transmitter::NAME => $transmitter_name,
					DAO_Transmitter::SERIAL => $transmitter_serial,
					DAO_Transmitter::TOTAL_HD_USAGE => $row[5],
					DAO_Transmitter::TOTAL_SD_USAGE => $row[6],
					DAO_Transmitter::TOTAL_FILE_TRANSFER_USAGE => $row[7],
					DAO_Transmitter::TOTAL_CLIP_HD_MINUTE_USAGE => $row[8],
					DAO_Transmitter::TOTAL_CLIP_SD_MINUTE_USAGE => $row[10],
					DAO_Transmitter::TOTAL_CLIP_HD_MEGABYTE_USAGE => $row[9],
					DAO_Transmitter::TOTAL_CLIP_SD_MEGABYTE_USAGE => $row[11],
					DAO_Transmitter::BILL_HD_USAGE => $row[12],
					DAO_Transmitter::BILL_SD_USAGE => $row[13],
					DAO_Transmitter::BILL_FILE_TRANSFER_USAGE => $row[14],
					DAO_Transmitter::BILL_CLIP_HD_MINUTE_USAGE => $row[15],
					DAO_Transmitter::BILL_CLIP_SD_MINUTE_USAGE => $row[17],
					DAO_Transmitter::BILL_CLIP_HD_MEGABYTE_USAGE => $row[16],
					DAO_Transmitter::BILL_CLIP_SD_MEGABYTE_USAGE => $row[18]
				);
				if(!$is_preview) {
					DAO_Transmitter::create($fields);
				}
				print '<pre>' . print_r($fields, 1) . '</pre>';
			}
			
			// Check for dupes
// 			$meta['object_id'] = null;
			
// 			if(!empty($sync_fields)) {
// 				$view->addParams($sync_fields, true);
// 				$view->renderLimit = 1;
// 				$view->renderPage = 0;
// 				$view->renderTotal = false;
// 				list($results) = $view->getData();
				
// 				if(!empty($results)) {
// 					$meta['object_id'] = key($results);
// 				}
// 			}
		}
		
		if(!$is_preview) {
			@unlink($csv_file); // nuke the imported file}
		}
	}
};