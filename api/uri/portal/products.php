<?php
class ProductsPortal_OsellotController extends Extension_Portal_Osellot_Controller {
	public function writeResponse(DevblocksHttpResponse $response) {
		$tpl = DevblocksPlatform::getTemplateService();
		$products = DAO_Product::getAll();
		
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
		$tpl->display('devblocks:osellot.core::portal/products.tpl');
	}
};