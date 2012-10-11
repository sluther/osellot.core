<?php
class ProductsTab_Billing_Osellot extends Extension_Tab_Billing_Osellot {
	const EXTENSION_ID = 'products.tab.billing.osellot';
	const VIEW_ACTIVITY_PRODUCTS = 'products';
	
	public function showTab() {
		$tpl = DevblocksPlatform::getTemplateService();
		$visit = CerberusApplication::getVisit();
		$translate = DevblocksPlatform::getTranslationService();
		$active_worker = CerberusApplication::getActiveWorker();
		
		// Index
		$defaults = new C4_AbstractViewModel();
		$defaults->class_name = 'View_Product';
		$defaults->id = self::VIEW_ACTIVITY_PRODUCTS;
		$defaults->name = $translate->_('products.tab.billing');
		$defaults->renderSortBy = SearchFields_Product::NAME;
		$defaults->renderSortAsc = 0;
		$defaults->renderLimit = 100;
		
		$view = C4_AbstractViewLoader::getView(self::VIEW_ACTIVITY_PRODUCTS, $defaults);
		
		C4_AbstractViewLoader::setView($view->id, $view);
		
		// 		$quick_search_type = $visit->get('crm.opps.quick_search_type');
		// 		$tpl->assign('quick_search_type', $quick_search_type);
		
		$tpl->assign('view', $view);
		
// 		$fields = array(
// 			DAO_Product::NAME => 'Product 1',
// 			DAO_Product::DESCRIPTION => 'This is a description of a product',
// 			DAO_Product::PRICE => '9.99',
// 			DAO_Product::PRICE_SETUP => '0',
// 			DAO_Product::RECURRING => '0',
// 			DAO_Product::SKU => 'prod1',
// 			DAO_Product::TAXABLE => 0,
// 		);
		
// 		$product = DAO_Product::get(1);

		
// 		DAO_ProductSetting::setProductSetting(1, 'test_setting', 'test_value');
		
// 		$settings = DAO_ProductSetting::getProductSettings(1);
// 		$product->settings = array();
// 		foreach($settings as $setting) {
// 			$product->settings[$setting->name] = $setting->value;
// 		}
// 		var_dump($product);

		$tpl->display('devblocks:osellot.core::billing/tabs/products.tpl');
	}
	
	public function showProductPanelAction() {
		$tpl = DevblocksPlatform::getTemplateService();
		
		@$product_id = DevblocksPlatform::importGPC($_REQUEST['id'],'integer',0);
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id'],'string','');
		
		$tpl->assign('view_id', $view_id);
		
		// Handle context links ([TODO] as an optional array)
		@$context = DevblocksPlatform::importGPC($_REQUEST['context'],'string','');
		@$context_id = DevblocksPlatform::importGPC($_REQUEST['context_id'],'integer','');
		$tpl->assign('context', $context);
		$tpl->assign('context_id', $context_id);
		
		if(!empty($product_id) && null != ($product = DAO_Product::get($product_id))) {
			$tpl->assign('product', $product);
		}
				
		$tpl->display('devblocks:osellot.core::billing/tabs/products/panel.tpl');
	}
	
	function saveProductPanelAction() {
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id'],'string','');
	
		@$product_id = DevblocksPlatform::importGPC($_REQUEST['product_id'],'integer',0);
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
	
		// Save
		if($do_delete) {
			if(null != ($product = DAO_Product::get($product_id))) {
				DAO_Product::delete($product_id);
				$product_id = null;
			}
				
		} elseif(empty($product_id)) {
			// Check privs
			
			// SKU is unique
			if(null !== ($product = DAO_Product::getBySKU($sku)))
				return;
			// Name must be set
			if(empty($name))
				return;
			
			$fields = array(
				DAO_Product::PRICE => $price,
				DAO_Product::PRICE_SETUP => $price_setup,
				DAO_Product::RECURRING => $recurring,
				DAO_Product::SKU => $sku,
				DAO_Product::NAME => $name,
				DAO_Product::DESCRIPTION => $description
			);
			$product_id = DAO_Product::create($fields);			
		} else {
			if(empty($product_id))
				return;
			
			// SKU is unique
			if(null !== ($product = DAO_Product::getBySKU($sku))) {
				if($product->id != $product_id) {
					return;
				}
			}
				
			// Name must be set
			if(empty($name))
				return;

			$fields = array(
				DAO_Product::PRICE => $price,
				DAO_Product::PRICE_SETUP => $price_setup,
				DAO_Product::RECURRING => $recurring,
				DAO_Product::SKU => $sku,
				DAO_Product::NAME => $name,
				DAO_Product::DESCRIPTION => $description
			);

			// Valid product?
			if(null !== ($product = DAO_Product::get($product_id))) {
				DAO_Product::update($product_id, $fields);
			}
		}
	
		// Reload view (if linked)
		if(!empty($view_id) && null != ($view = C4_AbstractViewLoader::getView($view_id))) {
			$view->render();
		}
	
		exit;
	}
}