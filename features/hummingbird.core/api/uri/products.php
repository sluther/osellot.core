<?php
class ProductsTab_Billing_Hummingbird extends Extension_Tab_Billing_Hummingbird {

	public function showTab() {
		$tpl = DevblocksPlatform::getTemplateService();
		
		$fields = array(
			DAO_Product::NAME => 'Product 1',
			DAO_Product::DESCRIPTION => 'This is a description of a product',
			DAO_Product::PRICE => '9.99',
			DAO_Product::PRICE_SETUP => '0',
			DAO_Product::RECURRING => '0',
			DAO_Product::SKU => 'prod1',
			DAO_Product::TAXABLE => 0,
		);
		
// 		$product_id = DAO_Product::create($fields);
		$product = DAO_Product::get(1);

		
// 			DAO_ProductSetting::PRODUCT_ID => 1,
// 			DAO_ProductSetting::NAME => 'test_setting',
// 			DAO_ProductSetting::VALUE => 'test_value',		
// 		);
		
		DAO_ProductSetting::setProductSetting(1, 'test_setting', 'test_value');
		
		$settings = DAO_ProductSetting::getProductSettings(1);
		$product->settings = array();
		foreach($settings as $setting) {
			$product->settings[$setting->name] = $setting->value;
		}
		var_dump($product);

		$tpl->display('devblocks:hummingbird.core::billing/tabs/products.tpl');
	}
	
	public function addProductAction() {


	}
}

