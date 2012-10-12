<?php
class PostbackPortal_OsellotController extends Extension_Portal_Osellot_Controller {
	public function writeResponse(DevblocksHttpResponse $response) {
		
		// [TODO] handle this in the gateway!
// 		$plugin = DevblocksPlatform::importGPC($_REQUEST['plugin'], 'string', '');
		// process the postback
		$req = 'cmd=_notify-validate';
		
		foreach ($_POST as $key => $value) {
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
		}
		
		// post back to PayPal system to validate
		$header .= "POST /cgi-bin/webscr HTTP/1.1\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
		$header .= "Host: www.paypal.com\r\n";
		$header .= "Connection: close";
		
		$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);
		
		// assign posted variables to local variables
		$invoice_id = DevblocksPlatform::importGPC($_POST['invoice'], 'integer', 0);
		$item_name = DevblocksPlatform::importGPC($_POST['item_name'], 'string', '');
		$item_number = DevblocksPlatform::importGPC($_POST['item_number'], 'string', '');
		$payment_status = DevblocksPlatform::importGPC($_POST['payment_status'], 'string', '');
		$payment_amount = DevblocksPlatform::importGPC($_POST['mc_gross'], 'string', '');
		$payment_currency = DevblocksPlatform::importGPC($_POST['mc_currency'], 'string', '');
		$payment_date = DevblocksPlatform::importGPC($_POST['payment_date'], 'string', '');
		$txn_id = DevblocksPlatform::importGPC($_POST['txn_id'], 'string', '');
		$receiver_email = DevblocksPlatform::importGPC($_POST['receiver_email'], 'string', '');
		$payer_email = DevblocksPlatform::importGPC($_POST['payer_email'], 'string', '');
		
		if (!$fp) {
			// HTTP ERROR
		} else {
			fputs ($fp, $header . $req);
			while (!feof($fp)) {
				$res = fgets ($fp, 1024);
				if (strcmp ($res, "VERIFIED") == 0) {
					// check the payment_status is Completed
					// check that txn_id has not been previously processed
					// check that receiver_email is your Primary PayPal email
					// check that payment_amount/payment_currency are correct
					// process payment
					$status = 0;
					if(null !== ($invoice = DAO_Invoice::get($invoice_id))) {
						$invoice_txn_id = $invoice->getAttribute('txn_id');
						if($invoice_txn_id == $txn_id || empty($invoice_txn_id)) {
							if(empty($invoice_txn_id))
								$invoice->setAttribute('txn_id', $txn_id);
							
							if($payment_amount >= $invoice->amount)
								$status = 2;
							
							$fields = array(
								DAO_Invoice::AMOUNT_PAID => $payment_amount,
								DAO_Invoice::STATUS => $status,
								DAO_Invoice::PAID_DATE => strtotime($payment_date)
							);
							DAO_Invoice::update($invoice->id, $fields);
							
							if(null !== ($account = DAO_ContactPerson::get($invoice->account_id))) {
								// Send email notification
								$msg = sprintf("Hello, %s %s!\r\n\r\nThis email has been sent to confirm order #%d placed on %s at %s. The order contents are as follows:\r\n",
									$account->getPrimaryAddress()->first_name,
									$account->getPrimaryAddress()->last_name,
									$invoice->id,
									date('F d, Y'),
									date('g:i A')
								);
								$items = $invoice->getItems();
								foreach($items as $item) {
									if(null !== ($product = DAO_Product::get($item->product_id))) {
										$msg .= sprintf("%dx %s ($%d ea) - $%d\r\n", $item->quantity, $product->name, $product->price, $item->quantity * $product->price);
									}
								}
									
								if($invoice->getAttribute('delivery')) {
									$msg .= sprintf("Total: $%d (+$%d for delivery)\r\n\r\n", $invoice_total - $invoice->getAttribute('delivery_cost'), $invoice->getAttribute('delivery_cost'));
								} else {
									$msg .= sprintf("Total: $%d\r\n\r\n", $invoice_total);
								}
								
								$msg .= "You do not need to reply to this email unless you have questions regarding your order.";
								
								CerberusMail::quickSend($account->getPrimaryAddress()->email, "Order Confirmation", $msg);
							}
						}
					}
				}
				else if (strcmp ($res, "INVALID") == 0) {
					// log for manual investigation
				}
			}
			fclose ($fp);
		}
	}
};