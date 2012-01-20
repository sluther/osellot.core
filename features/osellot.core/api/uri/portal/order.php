<?php
class OrderPortal_OsellotController extends Extension_Portal_Osellot_Controller {
	public function writeResponse(DevblocksHttpResponse $response) {
		$tpl = DevblocksPlatform::getTemplateService();
		$umsession = ChPortalHelper::getSession();
		
		$stack = $response->path;
		
		array_shift($stack); // order
		$section = array_shift($stack);
		
		switch($section) {
			case 'checkout':
				$cart = $umsession->getProperty('hb_cart', null);
				
				if($cart === null)
					DevblocksPlatform::redirect(new DevblocksHttpResponse(array('order')));
				
				$enabled_plugins = DAO_Gateway::getEnabled();
				$checkout_plugins = array();
				foreach($enabled_plugins as $plugin) {
					$plugins[] = DevblocksPlatform::getExtension($plugin->extension_id, true);				
				}
				$default_plugin = array_shift($plugins);
				array_unshift($plugins, $default_plugin);
				
				$tpl->assign('checkout_plugins', $plugins);
				$tpl->assign('default_plugin', $default_plugin);
				$tpl->assign('cart', $cart);
				$tpl->display('devblocks:osellot.core::portal/order/checkout.tpl');
				break;
			case 'confirm':
				$order = $umsession->getProperty('hb_order', null);
				if($order === null)
					DevblocksPlatform::redirect(new DevblocksHttpResponse(array('order')));
				
				$plugin_id = $order['attributes']['checkout_plugin'];
				$plugin = DevblocksPlatform::getExtension($plugin_id, true);
				
				$tpl->assign('order', $order);
				$tpl->assign('plugin', $plugin);
				$tpl->display('devblocks:osellot.core::portal/order/confirm.tpl');
				break;
			case 'process':
				$order = $umsession->getProperty('hb_order', null);
				if($order === null)
					DevblocksPlatform::redirect(new DevblocksHttpResponse(array('order')));
				
				$plugin_id = $order['attributes']['checkout_plugin'];
				$plugin = DevblocksPlatform::getExtension($plugin_id, true);
				
				$plugin->processTransaction($order);
				break;
			case 'postback':
				
				break;
			default:
				$cart = $umsession->getProperty('hb_cart', null);
				
				// Sort cart items alphabetically by name
				asort($cart['items']);
				
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
				$tpl->assign('cart', $cart);
				$tpl->display('devblocks:osellot.core::portal/order/index.tpl');
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
		array_shift($stack); // order
		array_shift($stack); // cart
		
		$section = array_shift($stack);
		switch($section) { 
			case 'add':
				$item = array_shift($stack);
				$product = DAO_Product::get($item);
				
				if(isset($cart['items'][$product->id])) {
					$cart['items'][$product->id]['quantity'] += 1;
					$cart['items'][$product->id]['total'] = $cart['items'][$product->id]['price'] * $cart['items'][$product->id]['quantity'];
				} else {
					$cart['items'][$product->id] = array(
						'name' => $product->name,
						'sku' => $product->sku,
						'price' => $product->price,
						'total' => $product->price,
 						// 'price_setup' => $product->price_setup,
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
				$product = DAO_Product::get($item);
				
				if(isset($cart['items'][$product->id])) {
					if($cart['items'][$product->id]['quantity'] > 1) {
						$cart['items'][$product->id]['quantity'] -= 1;
						$cart['items'][$product->id]['total'] = $cart['items'][$product->id]['price'] * $cart['items'][$product->id]['quantity'];
					} else {
						unset($cart['items'][$product->id]);
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
		
		DevblocksPlatform::redirect(new DevblocksHttpResponse(array('order')));
	}
	
	public function doCheckoutAction() {
		$tpl = DevblocksPlatform::getTemplateService();
		$umsession = ChPortalHelper::getSession();
		$account = $umsession->getProperty('hb_login');
		$cart = $umsession->getProperty('hb_cart', null);
		
		$checkout_plugin = DevblocksPlatform::importGPC($_REQUEST['plugin'], 'string', '');
		if($cart === null)
			DevblocksPlatform::redirect(new DevblocksHttpResponse(array('order')));
		
		$line1 = DevblocksPlatform::importGPC($_REQUEST['bline1'], 'string', '');
		$line2 = DevblocksPlatform::importGPC($_REQUEST['bline2'], 'string', '');
		$city = DevblocksPlatform::importGPC($_REQUEST['bcity'], 'string', '');
		$state = DevblocksPlatform::importGPC($_REQUEST['bstate'], 'string', '');
		$postal = DevblocksPlatform::importGPC($_REQUEST['bpostal'], 'string', '');
		
		// Create the order array
		$order = array(
			'items' => $cart['items'],
			'attributes' => array(
				'checkout_plugin' => $checkout_plugin,
				'billing_address' => array(
					'line1' => $line1,
					'line2' => $line2,
					'city' => $city,
					'state' => $state,
					'postal' => $postal
				)
			),
			'amount' => $cart['total'],
			'account_id' => $account->id
		);
		
		// Save the order in the session
		$umsession->setProperty('hb_order', $order);
		DevblocksPlatform::redirect(new DevblocksHttpResponse(array('order', 'confirm')));
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
		
		$plugin_id = $order['attributes']['checkout_plugin'];
		$plugin = DevblocksPlatform::getExtension($plugin_id, true);
		
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
		
		$invoice_total = 0;
		
		// [TODO] Refactor this to lookup product attrs and get the price for them
		// Add the attributes
		foreach($order['attributes'] as $name => $value) {
			if(is_array($value)) {
				foreach($value as $key => $val) {
					DAO_Invoice::setAttribute($invoice_id, $name.'.'.$key, $val);
				}
			} else {
				DAO_Invoice::setAttribute($invoice_id, $name, $value);
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
		array_shift($stack); // order
		$section = array_shift($stack);
		switch($section) {
			case 'checkout':
				$header = 'Place an online order (part 2 of 3)';
				break;
			case 'confirm':
				$header = 'Place an online order (part 3 of 3)';
				break;
			default:
				$header = 'Place an online order (part 1 of 3)';
				break;
		}
	
		return $header;
	}
};