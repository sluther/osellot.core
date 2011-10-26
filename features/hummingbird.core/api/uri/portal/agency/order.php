<?php
class OrderAgencyPortal_HummingbirdController extends Extension_Agency_Portal_Hummingbird_Controller {
	public function writeResponse(DevblocksHttpResponse $response) {
		$tpl = DevblocksPlatform::getTemplateService();
		$umsession = ChPortalHelper::getSession();
		$cart = $umsession->getProperty('hb_cart', null);
		
		// Sort cart items alphabetically by name
		asort($cart['items']);
		$tpl->assign('cart', $cart);
		
		$stack = $response->path;
		
		array_shift($stack); // agency
		array_shift($stack); // order
		$section = array_shift($stack);
		
		switch($section) {
			case 'checkout':
				$tpl->display('devblocks:hummingbird.core::portal/agency/order/checkout.tpl');
				break;
			case 'confirm':
				$order = $umsession->getProperty('hb_order', null);
				if($order === null)
					DevblocksPlatform::redirect(new DevblocksHttpResponse(array('agency', 'order')));
				
				$tpl->assign('order', $order);
				$tpl->display('devblocks:hummingbird.core::portal/agency/order/confirm.tpl');
				break;
			case 'process':
				$order = $umsession->getProperty('hb_order', null);
				if($order === null)
					DevblocksPlatform::redirect(new DevblocksHttpResponse(array('agency', 'order')));
				break;
			default:
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
				$tpl->display('devblocks:hummingbird.core::portal/agency/order/index.tpl');
				break;
		}
	}
	
	public function cartAction() {
		$request = DevblocksPlatform::getHttpRequest();
		$stack = $request->path;
		
		$umsession = ChPortalHelper::getSession();
		$cart = $umsession->getProperty('hb_cart', null);
		$cart['items'] = null == $cart['items'] ? array() : $cart['items'];
		
		array_shift($stack); // portal
		array_shift($stack); // portal id
		array_shift($stack); // agency
		array_shift($stack); // order
		array_shift($stack); // cart
		
		$section = array_shift($stack);
		switch($section) { 
			case 'add':
				$item = array_shift($stack);
				$item = DAO_Product::get($item);
				
				if(isset($cart['items'][$item->id])) {
					$cart['items'][$item->id]['quantity'] += 1;
					$cart['items'][$item->id]['total'] = $cart['items'][$item->id]['price'] * $cart['items'][$item->id]['quantity'];
				} else {
					$cart['items'][$item->id] = array(
						'name' => $item->name,
						'sku' => $item->sku,
						'price' => $item->price,
						'total' => $item->price,
 						// 'price_setup' => $item->price_setup,
						'quantity' => 1
					);
				}
				
				$cart['total'] = 0;
				foreach($cart['items'] as $item) {
					$cart['total'] += $item['price'] * $item['quantity'];
				}
				
				$umsession->setProperty('hb_cart', $cart);
				break;
			case 'remove':
				$item = array_shift($stack);
				$item = DAO_Product::get($item);
				
				if(isset($cart['items'][$item->id])) {
					if($cart['items'][$item->id]['quantity'] > 1) {
						$cart['items'][$item->id]['quantity'] -= 1;
						$cart['items'][$item->id]['total'] = $cart['items'][$item->id]['price'] * $cart['items'][$item->id]['quantity'];
					} else {
						unset($cart['items'][$item->id]);
					}
				}
				
				$cart['total'] = 0;
				foreach($cart['items'] as $item) {
					$cart['total'] += $item['price'] * $item['quantity'];
				}
				
				$umsession->setProperty('hb_cart', $cart);
				break;
			default:
				break;
		};
		
		DevblocksPlatform::redirect(new DevblocksHttpResponse(array('agency', 'order')));
	}
	
	public function doCheckoutAction() {
		$tpl = DevblocksPlatform::getTemplateService();
		$umsession = ChPortalHelper::getSession();
		$account = $umsession->getProperty('hb_login');
		$cart = $umsession->getProperty('hb_cart', null);
		
		$delivery = DevblocksPlatform::importGPC($_REQUEST['delivery'], 'integer', 1);

		if($cart === null)
			DevblocksPlatform::redirect(new DevblocksHttpResponse(array('agency', 'order')));
				
		// Create the order array
		$order = array(
			'items' => $cart['items'],
			'attributes' => array(
				'delivery' => $delivery,
				'pickup' => $delivery == false ? true : false,
			),
			'amount' => $cart['total'],
			'account_id' => $account->id
		);
		
		if($delivery) {
			$name = DevblocksPlatform::importGPC($_REQUEST['name'], 'string', '');
			$dline1 = DevblocksPlatform::importGPC($_REQUEST['dline1'], 'string', '');
			$dline2 = DevblocksPlatform::importGPC($_REQUEST['dline2'], 'string', '');
			$municipality = DevblocksPlatform::importGPC($_REQUEST['dmunicipality'], 'string', '');
			$postal = DevblocksPlatform::importGPC($_REQUEST['dpostal'], 'string', '');
			$order['attributes']['delivery_address'] = array(
				'name' => $name,
				'line1' => $dline1,
				'line2' => $dline2,
				'municipality' => $municipality,
				'postal' => $postal
			);
			$order['amount'] += 3;
		} else {
			$order['attributes']['pickup_location'] = array(
				'line1' => $account->address_line1,
				'line2' => $account->address_line2,
				'city' => $account->address_city,
				'province' => $account->address_province,
				'postal' => $account->address_postal 
			);
		}
		
		// Save the order in the session
		$umsession->setProperty('hb_order', $order);
		DevblocksPlatform::redirect(new DevblocksHttpResponse(array('agency', 'order', 'confirm')));
	}
	

	public function doConfirmAction() {
		$tpl = DevblocksPlatform::getTemplateService();
		$umsession = ChPortalHelper::getSession();
		$account = $umsession->getProperty('hb_login');
		$order = $umsession->getProperty('hb_order', null);
		$umsession->setProperty('hb_cart', null);
		
		if($order === null) {
			DevblocksPlatform::redirect(new DevblocksHttpResponse(array('order')));
		}
		
		// Generate an order number
		
		// [TODO] Refactor this so it can be changed in settings
		$number = mt_rand();
		
		// [TODO] Refactor this so we can create a new invoice without committing it to DB instantly
		
		// Create the invoice record
		$fields = array(
			DAO_Invoice::ACCOUNT_ID => $order['account_id'],
			DAO_Invoice::CREATED_DATE => time(),
			DAO_Invoice::STATUS => 0,
			DAO_Invoice::NUMBER => $number
		);
		
		$invoice_id = DAO_Invoice::create($fields);
		
		if($order['attributes']['delivery']) {
			$invoice_total = 3;
		} else {
			$invoice_total = 0;
		}
		
		// [TODO] Refactor this to lookup product attrs and get the price for them
		// Add the attributes
		foreach($order['attributes'] as $name => $value) {
			if(is_array($value)) {
				foreach($value as $key => $val) {
					DAO_Invoice::addAttribute($invoice_id, $name.'.'.$key, $val);
				}
			} else {
				DAO_Invoice::addAttribute($invoice_id, $name, $value);
			}
		}
		
		// Add the invoice items
		foreach($order['items'] as $item_id => $item) {
			DAO_Invoice::addItem($invoice_id, $item_id, $invoice_total, $item['quantity']);
		}
		
		$order['amount'] = $invoice_total;
		$order['number'] = $number;
		$umsession->setProperty('hb_order', $order);
		DAO_Invoice::update($invoice_id, array(DAO_Invoice::AMOUNT => $invoice_total));

		DevblocksPlatform::redirect(new DevblocksHttpResponse(array('order', 'process')));
	}
	
	public function getTitle(DevblocksHttpResponse $response) {
		$stack = $response->path;
		$title = '';
		array_shift($stack); // agency
		array_shift($stack); // order
		$section = array_shift($stack);
		switch($section) {
			case 'checkout':
				$title = 'Place an order (2 of 3)';
				break;
			case 'confirm':
				$title = 'Place an order (3 of 3)';
				break;
			default:
				$title = 'Place an order (1 of 3)';
				break;
		}
	
		return $title;
	}
	
	public function getHeader(DevblocksHttpResponse $response) {
		$stack = $response->path;
		$header = '';
		array_shift($stack); // agency
		array_shift($stack); // order
		$section = array_shift($stack);
		switch($section) {
			case 'checkout':
				$header = 'Place an order on behalf of a client (part 2 of 3)';
				break;
			case 'confirm':
				$header = 'Place an order on behalf of a client (part 3 of 3)';
				break;
			default:
				$header = 'Place an order on behalf of a client (part 1 of 3)';
				break;
		}
	
		return $header;
	}
};