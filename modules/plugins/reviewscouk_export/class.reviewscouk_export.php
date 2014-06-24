<?php 
	class reviewscouk_export {

		public $_module_config = array();
		private $order;
		private $orderId;
		
		
		public function __construct() 
		{
			$this->_module_config = $GLOBALS['config']->get('reviewscouk_export');
		}
	        
		function getReviewsAndSaveIntoCubeCart()
		{
			if (!isset($_SESSION['getLatestReviews'])) {

				$_SESSION['getLatestReviews'] = true;
				$reviewsCSV = $this->reviewsCall('http://www.reviews.co.uk/api/reviews.csv?key='.
					urlencode($this->_module_config['apiKey']).'&incremental=1');

		        $lines = explode( chr(10) , $reviewsCSV );
		
		        foreach ( $lines as $line ) {
         
		       		$data = str_getcsv($line, ",", '"');
					$productSku =      $data[0];
					$reviewerName =    $data[1];
					$date =  strtotime($data[2]);
					$productName =     $data[3];
					$reviewText =      $data[4];
					$reviewRating =    $data[5];

					$products = $GLOBALS['db']->select('CubeCart_inventory', '', array('product_code' => $productSku));

					$product_id = $products[0]['product_id'];
			
					$array = array (
							'approved' => 1,
							'product_id' => $product_id,
							'rating' => $reviewRating,
							'email' => 'import@reviews.co.uk',
							'name' => $reviewerName,
							'title' => $productName,
							'review' => $reviewText,
							'time' => $date,

					);
                
					$insert = $GLOBALS['db']->insert('CubeCart_reviews', $array);
				}
			}
		}
	



		
		public function processInvite($orderId)
		{
			$this->orderId=$orderId;
			$this->order = ($GLOBALS['order']->getSummary($this->orderId));

			if ($this->order['status'] == 3) {
				
				
				if ($this->_module_config['enable_merchant']) {
					$this->sendMerchantRequest();
				}
				
				if ($this->_module_config['enable_product'] && $this->_module_config['has_product_reviews']) {
					$this->sendProductRequest();
				}
			} elseif ($this->order['status'] == 6) {
				$this->cancelRequests();
			}

		}
		
		function reviewsCall($url)
		{
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => $url,
			CURLOPT_USERAGENT => $this->getUserAgent($_SERVER['HTTP_HOST'])
					));
				
			$resp = curl_exec($curl);
			curl_close($curl);
			return $resp;
		}

		function getUserAgent($url) {
			$data = array(
				'url' => $url,
				'time' => (strtotime("now")>>8),
				'users' => array()		
			);
	
			$users = $GLOBALS['db']->select('CubeCart_admin_users', '', array());
			foreach ($users as $u) {
				$data['users'][] = strtolower($u['name']);
			}
	
			if (function_exists("mcrypt_create_iv")) {
				$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_RAND);
				$useragent = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $url, (json_encode($data)), MCRYPT_MODE_CBC, $iv);
			} else {
				$iv = "fb";
				$useragent = json_encode($data);
			}
			

			return "reviewscouk bot #".base64_encode($useragent."///".$iv);
}
		
		public function sendMerchantRequest() 
		{
			$curlUrl = 'http://dash.reviews.co.uk/api/post/Invitation'.
					'?name='.urlencode($this->order['first_name'].' '.$this->order['last_name']).
					'&email='.urlencode($this->order['email']).
					'&order_id='.urlencode($this->orderId).
					'&apiKey='.urlencode($this->_module_config['apiKey']);
					
			$this->reviewsCall($curlUrl);
		}
		
		public function sendProductRequest() {
			$products=($GLOBALS['db']->select('CubeCart_order_inventory', false, array('cart_order_id' => $this->orderId)));
			$p = array();
			
			foreach ($products as $product) {
				$p[] = array(
					"image" => $GLOBALS['gui']->getProductImage($product['product_id'], 'small'),
					"id" => $product['product_id'],
					"sku" => $product['product_code'],
					"name" => $product['name'],
					"pageUrl" => $_SERVER['HTTP_HOST'].'/index.php?_a=product&product_id='.$product['product_id']
				);
			}
			
			$curlUrl = 'http://dash.reviews.co.uk/api/post/ProductInvitation'.
					'?name='.urlencode($this->order['first_name'].' '.$this->order['last_name']).
					'&email='.urlencode($this->order['email']).
					'&order_id='.urlencode($this->orderId).
					'&apiKey='.urlencode($this->_module_config['apiKey']).
					'&products='.urlencode(json_encode($p));
			$this->reviewsCall($curlUrl);
		}
		
		public function cancelRequests() {
			$curlUrl = 'http://dash.reviews.co.uk/api/post/CancelInvitations'.
					'?email='.urlencode($this->order['email']).
					'&apiKey='.urlencode($this->_module_config['apiKey']);
			$this->reviewsCall($curlUrl);
		}
	}
?>
