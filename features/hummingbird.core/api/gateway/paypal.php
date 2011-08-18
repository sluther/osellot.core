<?php

class PayPal_Gateway extends Extension_Gateway_Hummingbird {
	
	public function checkoutOption() {
		$tpl = DevblocksPlatform::getTemplateService();
		
		$tpl->display('devblocks:hummingbird.core::gateway/paypal/checkout_option.tpl');
	}
	
	public function checkout() {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->display('devblocks:hummingbird.core::gateway/paypal/checkout.tpl');
	}
		
	public function processTransaction($order) {
		$tpl = DevblocksPlatform::getTemplateService();
		$url = DevblocksPlatform::getUrlService();
		$return_url = $url->write('c=account&a=history', true);
		$cancel_return_url = $url->write('c=account&a=history', true);
		$notify_url = $url->write(sprintf('c=order&a=postback&id=%s', $order['attributes']['checkout_plugin']), true);
		
		$tpl->assign('return_url', $return_url);
		$tpl->assign('cancel_return_url', $cancel_return_url);
		$tpl->assign('notify_url', $notify_url);
		$tpl->assign('settings', $this->getSettings());
		$tpl->assign('order', $order);
		$tpl->display('devblocks:hummingbird.core::gateway/paypal/process_transaction.tpl');
	}
	
	public function postback() {
		
		
	}
	
	public function configure() {
		$tpl = DevblocksPlatform::getTemplateService();
		
		$tpl->display('devblocks:hummingbird.core::gateway/paypal/configure.tpl');
	}
}