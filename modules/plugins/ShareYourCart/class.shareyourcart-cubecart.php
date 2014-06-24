<?php
/**
 *	CLASS: Share Your Cart CubeCart Plugin
 *	AUTHOR: Barandi Solutions
 *	COUNTRY: Romania
 *	EMAIL: vlad.barliba@barandisolutions.ro
 *	VERSION : 1.0
 *	DESCRIPTION: This class is used as a base class for every wordpress shopping cart plugin we create.
 * *    Copyright (C) 2011 Barandi Solutions
 */

require_once("sdk/class.shareyourcart-base.php");

if(!class_exists('ShareYourCartCubeCartPlugin',false)){

class ShareYourCartCubeCartPlugin extends ShareYourCartBase {

	protected static $_VERSION = 6;
	protected $_PLUGIN_PATH;
	public $adminFix = true;
	
	/**
	*
	* Get the plugin version.
	* @return an integer
	*
	*/
	protected function getPluginVersion(){
			
		return self::$_VERSION;
	}

	/**
	 *
	 * Return the project's URL
	 *
	 */
	protected function getDomain(){
		return $GLOBALS['storeURL'];
	}

	/**
	 *
	 * Return the admin's email
	 *
	 */
	protected function getAdminEmail(){
		return $GLOBALS['config']->get('config','email_address');
	}

	/**
	 *
	 * Set the field value
	 *
	 */
	protected function setConfigValue($field,$value){
		$GLOBALS['config']->set('ShareYourCartConfig',$field,$value);
	}
	
	
	/**
	 *
	 * Get the table name based on the key
	 *
	 */
	protected function getTableName($key){
		return $GLOBALS['config']->get('config','dbprefix').$key;
	}

	/**
	 *
	 * Get the field value
	 *
	 */
	protected function getConfigValue($field){
		return $GLOBALS['config']->get('ShareYourCartConfig',$field);
	}

	/**
	 *
	 * Execute the SQL statement
	 *
	 */
	protected function executeNonQuery($sql){
		$GLOBALS['db']->query($sql);
	}

	/**
	 *
	 * Get the row returned from the SQL
	 *
	 * @return an associative array containing the data of the row OR NULL
	 *         if there is none
	 */
	protected function getRow($sql){
		$result = $GLOBALS['db']->query($sql);
		return (count($result)>0 ? $result[0]: NULL);
	}


	/**
	 *
	 * Insert the row into the specified table
	 *
	 */
	protected function insertRow($tableName,$data){
		$prefix = $GLOBALS['config']->get('config','dbprefix');
		
		$length = strlen($prefix);
		
		if(substr($tableName, 0, $length) === $prefix):
			$tableName = substr($tableName, $length);
		endif;
		
		$GLOBALS['db']->insert($tableName,$data);
	}

	/**
	 *
	 * Create url for the specified file. The file must be specified in relative path
	 * to the base of the plugin
	 */
	protected function createUrl($file){
		//get the real file path
		$file = realpath($file);

		//calculate the relative path from this file
		$file = SyC::relativepath(dirname(__FILE__),$file);
		$subject = $GLOBALS['storeURL'].'/modules/plugins/'.basename(dirname(__FILE__)).'/'.$file;
		return preg_replace('/\\\\/', '/', $subject);
	}

	/**
	 *
	 * This is the activate hook called by Cubecart. It is used for both install and activate
	 *
	 */
	public function activateHook() {
		$message = '';

		//the db version is old, so install it again
		if(version_compare($this->getConfigValue('db_version'),self::$_DB_VERSION)!=0){
			$this->install($message);
		} else { //if the version are equal, simply activate the plugin

			$this->activate($message);
		}

		//TODO: perhaps we can show the message somehow to the user. For now, just log it
		error_log($message);
	}

	/**
	 *
	 * This is the deactivate hook called by WordPress.
	 *
	 */
	public function deactivateHook() {
		$message = '';

		$this->deactivate($message);

		//TODO: perhaps we can show the message somehow to the user. For now, just log it
		error_log($message);
	}

	public function getButtonCallbackURL()
	{
		$callback_url = $GLOBALS['storeURL'].'?_g=remote&mod_type=gateway&module=shareyourcart&action=buttonCallback';

		if($this->isSingleProduct())
		{
			$callback_url .= '&product_id='. (int)$_GET['product_id'];
		}

		return $callback_url;
	}

	public function loadSessionData()
	{
		$GLOBALS['cart']->load();
	}

	public function getSecretKey()	{
		return '5d3923a2-6ae2-495d-b848-d5da7bd70e00';
	}

	/**
	 *
	 * 	 Insert coupon in database
	 *
	 */
	protected function saveCoupon($token, $coupon_code, $coupon_value, $coupon_type, $product_unique_ids = array()) {

		$record		= array(
		'status'		=> 1, //active
		#	'archived'		=> (isset($_POST['coupon']['archived'])) ? 1 : 0,
		'code'			=> $coupon_code,
        /*
        ovidiu.buksa: 05.03.2013: When inserting a new coupon in the db and be able to use it in storefront, the record of the coupon MUST have
                                  a product id. It has been observed that the product id is the same for all the coupons: the serialisation of
                                  the array containing the key-word "include". The version of CubeCart in which this was introduced is 5.1.5, 
                                  but kept compatibility with older versions of CubeCart in which this worked with the product id ''.
        */
		'product_id'	=> (version_compare(CC_VERSION,'5.1.5','>=')) ? serialize(array('include')) : '',
		'expires'		=> date('Y-m-d', strtotime("now +1 day")),
		'allowed_uses'	=> 1,
		'min_subtotal'	=> '',
		''		=> 0,
		'description'	=> $coupon_value,		
		);

		switch($coupon_type)
		{
			case 'amount':
				$record['discount_price'] = $coupon_value;
				$record['shipping'] = 0;
				break;
			case 'percent':
				$record['discount_percent'] = $coupon_value;
				$record['shipping'] = 0;
				break;
			case 'free_shipping' :
				$record['discount_percent'] = 100;
				$record['shipping'] = 1;
				break;
			default :
				$record['discount_price'] = $coupon_value;
				$record['shipping'] = 0;
				break;
		}

		if($GLOBALS['db']->insert('CubeCart_coupons', $record) === false)
		{
			//the save failed
			throw new Exception('Failed to save the coupon');
		}

		//call the base class method
		parent::saveCoupon($token, $coupon_code, $coupon_value, $coupon_type);
	}

	public function applyCoupon($coupon_code){
		unset($GLOBALS['cart']->basket['coupons']); //clear any previously applied coupon
		$GLOBALS['cart']->discountAdd($coupon_code);
		$GLOBALS['cart']->save();
	}

	public function buttonCallback(){

		$callback_url = $GLOBALS['storeURL'].'?_g=remote&mod_type=gateway&module=shareyourcart&action=couponCallback';
		$shopping_cart_url = $GLOBALS['storeURL'].'/index.php?_a=basket';

		//specify the parameters
		$params = array(
			'callback_url' => $callback_url,
			'success_url' => $shopping_cart_url,
			'cancel_url' => $shopping_cart_url,
		);

		//there is no product set, thus send the products from the shopping cart
		if(!isset($_REQUEST['product_id']))
		{
			//add the cart items to the arguments
			foreach($GLOBALS['cart']->get() as $product)
			{
				$productPrice = $product['ctrl_sale']? $product['sale_price']:$product['line_price'];
				
				$params['cart'][] = array(
				"item_name" => $product['name'],
				"item_url" => $GLOBALS['storeURL'].'/index.php?_a=product&product_id='.$product['product_id'],
				"item_price" => $productPrice,
				"item_description" => substr($product['description'],0,255), 
				"item_picture_url" => $GLOBALS['gui']->getProductImage($product['product_id']),
				);			
			} //cart loop
			
			
		}
		else
		{
			$product_id = (int)$_REQUEST['product_id'];
			$product = $GLOBALS['catalogue']->getProductData($product_id);
			
			$productPrice = $product['ctrl_sale']? $product['sale_price']:$product['price'];
						
			$params['cart'][] = array(
				"item_name" => $product['name'],
				"item_url" => $GLOBALS['storeURL'].'/index.php?_a=product&product_id='.$product_id,
				"item_price" => $productPrice,
				"item_description" => substr($product['description'],0,255), 
				"item_picture_url" => $GLOBALS['gui']->getProductImage($product['product_id']),
			);
		}

		try
		{
			$this->startSession($params);
		}
		catch(Exception $e)
		{
			//display the error to the user
			echo $e->getMessage();
		}
		exit;

	}
	
	public function isSingleProduct()
	{
		return isset($_GET['product_id']) && is_numeric($_GET['product_id']);
	}
	
	public function isOutOfStock()
	{		
		return ($GLOBALS['smarty']->getTemplateVars('CTRL_OUT_OF_STOCK')==1 ? true:false);			
	}
	
	/**
	 *
	 * Return the jQuery sibling selector for the product button
	 *
	 */
	protected function getProductButtonPosition(){
		$selector = parent::getProductButtonPosition();
		return (!empty($selector) ? $selector : "#product_detail h1");
	}
	
	/**
	 *
	 * Return the jQuery sibling selector for the cart button
	 *
	 */
	protected function getCartButtonPosition(){
		$selector = parent::getCartButtonPosition();
		return (!empty($selector) ? $selector : ".subtotals");
	}
}
} //END IF
?>