<?php
class ProductsPortal_HummingbirdController extends Extension_Portal_Hummingbird_Controller {
	public function writeResponse(DevblocksHttpResponse $response) {
		$tpl = DevblocksPlatform::getTemplateService();
		$products = DAO_Product::getWhere();
		
		foreach($products as $id => $product) {
			$settings = DAO_Product::getProductSettings($id);
			if(!empty($settings)) {
				$products[$id]->settings = array();
				foreach($settings as $setting) {
					$products[$id]->settings[$setting->name] = $setting->value;
				}
			}
		}
		
		$tpl->assign('products', $products);
		$tpl->display('devblocks:hummingbird.core::portal/products.tpl');
	}
};