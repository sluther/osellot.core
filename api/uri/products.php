<?php

class OsellotProductsPage extends CerberusPageExtension {
	
	function isVisible() {
		// The current session must be a logged-in worker to use this page.
		if(null == ($worker = CerberusApplication::getActiveWorker()))
			return false;
		return true;
	}
	
	function saveProductPeekAction() {
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
		
		// Product Attributes
		$product_attributes = DevblocksPlatform::importGPC($_REQUEST['product_attributes'], 'array', array());
		
		// Dates
		$created_date = time();
				
		// Worker
		$active_worker = CerberusApplication::getActiveWorker();
	
		// Name must be set
		if(empty($name))
			return;
		
		// Save
		if(!empty($id) && !empty($do_delete)) {
			if(null != ($product = DAO_Product::get($id))) {
				DAO_Product::delete($id);
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
					foreach($product_attributes as $key => $value) {
						$product->setAttribute($key, $value);
					}
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
				if(null !== ($product = DAO_Product::get($id)))
					foreach($product_attributes as $key => $value) {
						$product->setAttribute($key, $value);
					}
				
				// View marquee
				if(!empty($id) && !empty($view_id)) {
					C4_AbstractView::setMarqueeContextCreated($view_id, 'osellot.contexts.product', $id);
				}
			}
		}
		exit;
	}
};