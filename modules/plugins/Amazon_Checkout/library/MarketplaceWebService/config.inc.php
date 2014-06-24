<?php
  
   define ('DATE_FORMAT', 'Y-m-d\TH:i:s\Z');
   
   // Set include path for library
   $amazon_lib = 'modules/plugins/Amazon_Checkout/library';
	set_include_path(get_include_path().PATH_SEPARATOR.$amazon_lib);
	
	$amazon_classes = array(
		'MarketplaceWebService_MWSFeedsClient',
		'MarketplaceWebService_MWSProperties',
		'MarketplaceWebService_Client',
		'MarketplaceWebService_Model_FeedType',
		'MarketplaceWebService_Model_SubmitFeedRequest'
	);
	
	foreach($amazon_classes as $class) {
		$file_name = str_replace('_','/',$class);
		$file_path = CC_ROOT_DIR.'/modules/plugins/Amazon_Checkout/library/'.$file_name.'.php';
		if(file_exists($file_path)) {
			require_once $file_path;
		} else {
			continue;
		}	
	}
  
?>