<?php
class AuthNet extends Extension_Gateway_Hummingbird {
	
	const LIVE_URL = 'https://secure.authorize.net/gateway/transact.dll';
    const SANDBOX_URL = 'https://test.authorize.net/gateway/transact.dll';
    const XML_LIVE_URL = "https://api.authorize.net/xml/v1/request.api";
    const XML_SANDBOX_URL = "https://apitest.authorize.net/xml/v1/request.api";
	
    
    public function checkoutOption() {
    	$tpl = DevblocksPlatform::getTemplateService();
    
    	$tpl->display('devblocks:hummingbird.core::gateway/paypal/checkout_option.tpl');
    }
    
    public function checkout() {
    	$tpl = DevblocksPlatform::getTemplateService();
    	$tpl->display('devblocks:hummingbird.core::gateway/authnet/checkout.tpl');
    }
    
    public function processTransaction($transaction) {
    
    	//		$this->decrypt();
    
    	// DPM
    	//		$fp_sequence = 1;
    	//		$fp_timestamp = time();
    	//
    	//		if (function_exists('hash_hmac')) {
    	//            $fp = hash_hmac("md5", $this->username . "^" . $fp_sequence . "^" . $fp_timestamp . "^" . $transaction->amount . "^", $this->password);
    	//        }
    	//        $fp = bin2hex(mhash(MHASH_MD5, $this->username . "^" . $fp_sequence . "^" . $fp_timestamp . "^" . $transaction->amount . "^", $this->password));
    	//
    	//		// set postfields
    	//		$postfields = array (
    	//            'x_amount'			=> $transaction->amount,
    	//			'x_card_num'		=> $transaction->cc->number,
    	//			'x_exp_date'		=> $transaction->cc->exp_date,
    	//			'x_first_name'		=> $transaction->first_name,
    	//			'x_last_name'		=> $transaction->last_name,
    	//			'x_card_code'		=> $transaction->cc->card_code,
    	//            'x_fp_sequence'		=> $fp_sequence,
    	//            'x_fp_hash'			=> $fp,
    	//            'x_fp_timestamp'	=> $fp_timestamp,
    	//            'x_login'			=> $this->username,
    	//		);
    
    	//		$rs = $this->connect(($this->test_mode ? self::LIVE_URL : self::SANDBOX_URL), $postfields);
    
    	$cim = $this->_createXML('createCustomerProfileTransactionRequest');
    	$trans = $cim->addChild('transaction');
    	$auth = $trans->addChild('profileTransAuthCapture');
    	$auth->addChild('amount', $transaction->amount);
    	$auth->addChild('customerProfileId', $transaction->customerProfileId);
    	$auth->addChild('customerPaymentProfileId', $transaction->paymentProfileId);
    	$rs = $this->connect(($this->test_mode ? self::XML_LIVE_URL : self::XML_SANDBOX_URL), $cim->asXML());
    
    	@$response = new SimpleXMLElement($rs);
    	var_dump($response);
    }
    
    public function postback() {
    
    
    }
    
    public function configure() {
    	$tpl = DevblocksPlatform::getTemplateService();
    
    	$tpl->display('devblocks:hummingbird.core::gateway/authnet/configure.tpl');
    }
    
    public function createCustomerProfile($customerProfile) {
    	$db = DevblocksPlatform::getDatabaseService();
    	$this->decrypt();
		
    	// CIM
		    	
		$cim = $this->_createXML('createCustomerProfileRequest');
		$profile = $cim->addChild('profile');
		$this->_addObject($profile, $customerProfile);
		$rs = $this->connect(($this->test_mode ? self::XML_LIVE_URL : self::XML_SANDBOX_URL), $cim->asXML());
		@$response = new SimpleXMLElement($rs);
				
		if($response->messages->resultCode != 'Error') {
			$address = DAO_Address::getByEmail($customerProfile->email);	
			$profileId = (integer) $response->customerProfileId;
			
			$ccs = array();
			foreach($customerProfile->paymentProfiles as $paymentProfile) {
				$ccNum = substr($paymentProfile->payment->creditCard->cardNumber, -4, 4);
				$ccs[$ccNum] = $paymentProfile->payment->creditCard->expirationDate;
			}
			if(null == ($profile = DAO_AuthnetCustomerProfile::getWhere(sprintf("address_id = %s", $address->id))))
				DAO_AuthnetCustomerProfile::create(array('address_id' => $address->id, 'profile_id' => $profileId));
			$amount = 9.95;
			foreach($response->customerPaymentProfileIdList as $paymentProfile) {
				$paymentProfileId = (integer) $paymentProfile->numericString;
				$result = $this->getPaymentProfileTest($profileId, $paymentProfileId);
				$cc = substr($result->payment->creditCard->cardNumber, -4, 4);
				if(array_key_exists($cc, $ccs)) {
					if(null == ($profile = DAO_AuthnetPaymentProfile::getWhere(sprintf("profile_id = %s", $paymentProfileId))))
						DAO_AuthnetPaymentProfile::create(array('address_id' => $address->id, 'card_num' => $cc, 'card_exp' => $ccs[$cc], 'profile_id' => $paymentProfileId));
				}
				
			}
			$trans = $this->processAuthOnly($amount, $profileId, $paymentProfileId);
//			var_dump($trans);
			if($trans[0] != '3') {
				if(null == ($result = DAO_Transaction::getWhere(sprintf("trans_id = %s", $trans[6]))))
					$id = DAO_Transaction::create(
									array(
										DAO_Transaction::ADDRESS_ID => $address->id,
										DAO_Transaction::GATEWAY => 'wgm.hummingbird.gateway.cc.authnet',
										DAO_Transaction::TRANS_ID => $trans[6],
										DAO_Transaction::PROFILE_ID => $paymentProfileId,
										DAO_Transaction::AMOUNT => $amount,
									)
					);
				$trans = DAO_Transaction::get($id);
				
				$result = $this->processCapturePriorAuth($amount, $customerProfileId, $paymentProfileId, $trans->trans_id);
				var_dump($result);
				if($result[0] == '1') {
					$fields = array(
						DAO_Transaction::PROCESSED => 1
					);
					DAO_Transaction::update($trans->id,$fields);
				}
			}
	
			$this->deleteCustomerProfileTest($profileId);
		}		
    }
    
    public function getCustomerProfile($profileId) {
    	$this->decrypt();
    	
    	$cim = $this->_createXML('getCustomerProfileRequest');
		$cim->addChild('customerProfileId', $profileId);
		$rs = $this->connect(($this->test_mode ? self::XML_LIVE_URL : self::XML_SANDBOX_URL), $cim->asXML());
		
		@$response = new SimpleXMLElement($rs);
		var_dump($response->profile->paymentProfiles[0]);
    }
    
    public function deleteCustomerProfileTest($profileId) {
    	
    	$cim = $this->_createXML('deleteCustomerProfileRequest');
		$cim->addChild('customerProfileId', $profileId);
		$rs = $this->connect(($this->test_mode ? self::XML_LIVE_URL : self::XML_SANDBOX_URL), $cim->asXML());
		
		@$response = new SimpleXMLElement($rs);
//		var_dump($response);
    }
    
    public function deleteCustomerProfile($profileId) {
    	$this->decrypt();
    	
    	$cim = $this->_createXML('deleteCustomerProfileRequest');
		$cim->addChild('customerProfileId', $profileId);
		$rs = $this->connect(($this->test_mode ? self::XML_LIVE_URL : self::XML_SANDBOX_URL), $cim->asXML());
		
		@$response = new SimpleXMLElement($rs);
//		var_dump($response);
    }
    
    public function getPaymentProfileTest($profileId, $paymentProfileId) {
    	
    	$cim = $this->_createXML('getCustomerPaymentProfileRequest');
    	$cim->addChild('customerProfileId', $profileId);
		$cim->addChild('customerPaymentProfileId', $paymentProfileId);
		$rs = $this->connect(($this->test_mode ? self::XML_LIVE_URL : self::XML_SANDBOX_URL), $cim->asXML());
		@$response = new SimpleXMLElement($rs);
		return $response->paymentProfile;
    }
    
    public function getPaymentProfile($profileId, $paymentProfileId) {
		$this->decrypt();
    	
    	$cim = $this->_createXML('getCustomerPaymentProfileRequest');
    	$cim->addChild('customerProfileId', $profileId);
		$cim->addChild('customerPaymentProfileId', $paymentProfileId);
		$rs = $this->connect(($this->test_mode ? self::XML_LIVE_URL : self::XML_SANDBOX_URL), $cim->asXML());
		@$response = new SimpleXMLElement($rs);
		return $response->paymentProfile;
    }
    
    /*
	 * 
	 * processAuthOnly
	 * 
	 * Performs an authorization ONLY against the paymentProfileId
	 * 
	 * @access public
	 * @var amount The amount of the transaction
	 * @var customerProfileId the ID of the customer associated with this transaction
	 * @var paymentProfileId the ID of the paymentProfile to authorize this transaction to
	 * 
	 */
    
    public function processAuthOnly($amount, $customerProfileId, $paymentProfileId) {
    	$cim = $this->_createXML('createCustomerProfileTransactionRequest');
		$trans = $cim->addChild('transaction');
		$auth = $trans->addChild('profileTransAuthOnly');
		$auth->addChild('amount', $amount);
		$auth->addChild('customerProfileId', $customerProfileId);
		$auth->addChild('customerPaymentProfileId', $paymentProfileId);
		$rs = $this->connect(($this->test_mode ? self::XML_LIVE_URL : self::XML_SANDBOX_URL), $cim->asXML());
		
		@$response = new SimpleXMLElement($rs);
		var_dump($response);
		$response = explode(',', $response->directResponse);
		return $response;
    }
    
	/*
	 * 
	 * processAuthAndCapture
	 * 
	 * Performs an authorization and capture against the supplied paymentProfileId
	 * 
	 * @access public
	 * @var amount The amount of the transaction
	 * @var customerProfileId the ID of the customer associated with this transaction
	 * @var paymentProfileId the ID of the paymentProfile to bill this transaction to
	 * 
	 */
    
    public function processAuthAndCapture($amount, $customerProfileId, $paymentProfileId) {
    	$cim = $this->_createXML('createCustomerProfileTransactionRequest');
		$trans = $cim->addChild('transaction');
		$auth = $trans->addChild('profileTransAuthCapture');
		$auth->addChild('amount', $amount);
		$auth->addChild('customerProfileId', $customerProfileId);
		$auth->addChild('customerPaymentProfileId', $paymentProfileId);
		$rs = $this->connect(($this->test_mode ? self::XML_LIVE_URL : self::XML_SANDBOX_URL), $cim->asXML());
		
		@$response = new SimpleXMLElement($rs);
		$response = explode(',', $response->directResponse);
		return $response;
    }
    
    /*
	 * 
	 * processCapturePriorAuth
	 * 
	 * Performs a capture for the given transId against the paymentProfileId
	 * 
	 * @access public
	 * @var amount The amount of the transaction
	 * @var customerProfileId the ID of the customer associated with this transaction
	 * @var paymentProfileId the ID of the paymentProfile to bill this transaction to
	 * @var $transId the ID of the transaction returned by processAuthOnly
	 * 
	 */
    
    public function processCapturePriorAuth($amount, $customerProfileId, $paymentProfileId, $transId) {
    	$cim = $this->_createXML('createCustomerProfileTransactionRequest');
		$trans = $cim->addChild('transaction');
		$auth = $trans->addChild('profileTransPriorAuthCapture');
		$auth->addChild('amount', $amount);
		$auth->addChild('customerProfileId', $customerProfileId);
		$auth->addChild('customerPaymentProfileId', $paymentProfileId);
		$auth->addChild('transId', $transId);
		$rs = $this->connect(($this->test_mode ? self::XML_LIVE_URL : self::XML_SANDBOX_URL), $cim->asXML());
		
		@$response = new SimpleXMLElement($rs);
		$response = explode(',', $response->directResponse);
		return $response;
    }
	
	public function generateKey($pass, $salt, $counter, $keylen, $algorithm = 'sha256') {
		
		$hashlen = strlen(hash($algorithm, null, true)); // Hash length
		$keyblocks = ceil($keylen / $hashlen);  // Number of key blocks to compute
        $key = '';
		
        // Create key
        for ( $block = 1; $block <= $keyblocks; $block ++ ) {
 
            // Initial hash for this block
            $ib = $b = hash_hmac($algorithm, $salt . pack('N', $block), $pass, true);
 
            // loop over each block
            for ( $i = 1; $i < $counter; $i ++ )
 
                // XOR each iterate
                $ib ^= ($b = hash_hmac($algorithm, $b, $pass, true));
 			// append iterated block
            $key .= $ib;
        }

        // Return derived key of correct length
        return substr($key, 0, $keylen);
		
	}
	
	public function encrypt($data) {
		$key = $this->generateKey(sha1($this->username), sha1('hummingbird'), 100000, 32);	
		$td = mcrypt_module_open('rijndael-256', '', 'ctr', '');
		$iv = mcrypt_create_iv(32, MCRYPT_RAND);
		mcrypt_generic_init($td, $key, $iv);
		
		$encrypted_data = mcrypt_generic($td, $data);
		$encrypted_data = $iv . $encrypted_data;
		$encrypted_data .= $this->generateKey($encryptyed_data, $key, 100000, 32);	
		
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		
		return base64_encode($encrypted_data);	
	}
	
	public function decrypt() {
		$key = $this->generateKey(sha1($this->username), sha1('hummingbird'), 100000, 32);
		$this->password = base64_decode($this->password);
		
		$td = mcrypt_module_open('rijndael-256', '', 'ctr', '');
		
		$iv = substr($this->password, 0, 32);
		$mo = strlen($this->password) - 32;
		$em = substr($this->password, $mo);
		

		$encrypted_data = substr($this->password, 32, strlen($this->password)-64);
		
		$mac = $this->generateKey($iv . $encrypted_data, $key, 100000, 32);
		if($em !== $mac)
			print 'failed!';
			
		mcrypt_generic_init($td, $key, $iv);
		$this->password = mdecrypt_generic($td, $encrypted_data);
	}
	
	public function connect($url, $payload) {
		
		$header = array();
		$ch = curl_init();
 
		$verb = strtoupper($verb);
		$http_date = gmdate(DATE_RFC822);
 
		$header[] = 'Date: '.$http_date;
	    if (preg_match('/xml/',$url)) {
            $header[] = "Content-Type: text/xml";
        } else {
        	$header[] = 'Content-Type: application/x-www-form-urlencoded; charset=utf-8';
        }
        
		$postfields = '';
 
		if(!is_null($payload)) {
			if(is_array($payload)) {
				foreach($payload as $field => $value) {
					$postfields .= $field.'='.rawurlencode($value) . '&';
				}
				rtrim($postfields,'&');
 
			} elseif (is_string($payload)) {
				$postfields = $payload;
			}
		}
 
			
		$header[] = 'Content-Length: ' .  strlen($postfields);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);

		
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
 
		$output = curl_exec($ch);
 
		$info = curl_getinfo($ch);
		$this->_content_type = $info['content_type'];
 
		curl_close($ch);
 
		return $output;
		
	}
	
	private function _createXML($request_type) {
		$string = '<?xml version="1.0" encoding="utf-8"?><'.$request_type.' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd"></'.$request_type.'>';
		@$xml = new SimpleXMLElement($string);
		
		$merchant = $xml->addChild('merchantAuthentication');
		$merchant->addChild('name', $this->username);
		$merchant->addChild('transactionKey', $this->password);
		
		return $xml;
	}

	
    private function _addObject($destination, $object) {
        $array = (array)$object;
        foreach ($array as $key => $value) {
            if ($value && !is_object($value)) {
                if (is_array($value) && count($value)) {
                    foreach ($value as $index => $item) {
                        $items = $destination->addChild($key);
                        $this->_addObject($items, $item);
                    }
                } else {
                    $destination->addChild($key,$value);
                }
            } elseif (is_object($value) && self::_notEmpty($value)) {
                $dest = $destination->addChild($key);
                $this->_addObject($dest, $value);
            }
        }
    }
	
    private static function _notEmpty($object) {
        $array = (array)$object;
        foreach ($array as $key => $value) {
            if ($value && !is_object($value)) {
                return true;
            } elseif (is_object($value)) {
                if (self::_notEmpty($value)) {
                    return true;
                }
            }
        }
        return false;
    }
}