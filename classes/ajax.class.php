<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2015. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */

/**
 * AJAX controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class Ajax {

	//=====[ Public ]=======================================

	/**
	 * Load the proper AJAX function/method
	 */
	public static function load() {
		global $glob;

		$json = '';
		//Kill debug
		$GLOBALS['debug']->supress();


		//Try a hook first
		foreach ($GLOBALS['hooks']->load('class.ajax.load') as $hook) include $hook;
		if (!empty($json)) {
			return $json;
		}

		//Get the correct function/method
		$type = (isset($_GET['type'])) ? $_GET['type'] : '';
		$string = ($_GET['q']) ? $_GET['q'] : '';
		
		switch ($_GET['function']) {
			case 'viewEmail':
				$return_data = self::viewEmail((int)$_GET['id'], (string)$_GET['mode']);
			break;
			case 'SMTPTest':
				$return_data = self::SMTPTest();
			break;
			case 'template':
				$return_data = self::template($type, $string);
			break;
			case 'search':
			default:
				$return_data = self::search($type, $string);
			break;
		}
		return $return_data;
	}

	/**
	 * Admin search function
	 *
	 * @param string $type
	 * @param string $search_string
	 * @return data/false
	 */
	public static function search($type, $search_string) {
		$data = false;
		
		foreach ($GLOBALS['hooks']->load('class.ajax.search') as $hook) include $hook;

		if (!empty($type) && !empty($search_string)) {
			switch (strtolower($type)) {
			case 'user':
				if (($results = $GLOBALS['db']->select('CubeCart_customer', false, array('~'.$search_string => array('last_name', 'first_name', 'email')), false, false, false, false)) !== false) {
					foreach ($results as $result) {
						$data[] = array(
							'value'  => $result['customer_id'],
							'display' => $result['first_name'].' '.$result['last_name'],
							'info'  => $result['email'],
							'data'  => $result,
						);
					}
				}
				break;
			case 'address':
				if (($results = $GLOBALS['db']->select('CubeCart_addressbook', false, array('customer_id' => (int)$search_string), false, false, false, false)) !== false) {
					foreach ($results as $result) {
						$result['state'] = getStateFormat($result['state']);
						$result['country'] = getCountryFormat($result['country']);
						$result['description'] = empty($result['description']) ? $result['line1'].', '.$result['postcode'] : $result['description'];
						$data[]    = $result;
					}
				}
				break;
			case 'product':
				// Limited to a maximum of 15 results, in order to prevent it going mental
				if (($results = $GLOBALS['db']->select('CubeCart_inventory', false, array('~'.$search_string => array('name', 'product_code')), false, 15, false, false)) !== false) {
					foreach ($results as $result) {
						$lower_price = Tax::getInstance()->salePrice($result['price'], $result['sale_price'], false);
						if ($lower_price && ($lower_price < $result['price'])) {
							$result['price'] = $lower_price;
						}
						$data[] = array(
							'value'  => $result['product_id'],
							'display' => $result['name'],
							'info'  => Tax::getInstance()->priceFormat($result['price']),
							'data'  => $result,
						);
					}
				}
				break;
			case 'newsletter':
				$newsletter = Newsletter::getInstance();
				$status  = $newsletter->sendNewsletter($_GET['q'], $_GET['page']);
				if (is_array($status)) {
					$data = $status;
				} else {
					$data = ($status) ? array('complete' => 'true', 'percent' => 100) : array('error' => 'true');
				}
				break;
			case 'files':

				if ($_GET['dir'] == '/') {
					$dir = false;
				} elseif ($_GET['dir'] == '/') {
					$dir = false;
				} else {
					$dir = $_GET['dir'];
				}

				$filemanager = new FileManager($_GET['group'], $dir);

				// Directories
				if (is_array($dirs = $filemanager->findDirectories())) {
					foreach ($dirs[$filemanager->formatPath($dir)] as $parent => $folder) {
						$path = (!empty($dir)) ? '/' : '';
						$json[] = array(
							'type' => 'directory',
							'path' => urldecode($dir.basename($folder).'/'),
							'name' => basename($folder),
						);
					}
				}

				if (($files = $filemanager->listFiles()) !== false) {
					$catalogue = new Catalogue();
					foreach ($files as $result) {
						if ($filemanager->getMode() == FileManager::FM_FILETYPE_IMG) {
							$fetch = $catalogue->imagePath($result['file_id'], 'medium');
							$path = $name = $fetch;
						} else {
							$path = $result['filepath'];
							$name = $result['filename'];
						}
						$json[] = array(
							'type'   => 'file',
							'path'   => dirname($path).'/',
							'file'   => basename($result['filename']),
							'name'   => basename($name),
							'id'   => $result['file_id'],
							'description' => $result['description'],
							'mime'   => $result['mimetype'],
						);
					}
				}

				$data = (isset($json) && is_array($json)) ? $json : false;
				break;
			default:
				return false;
				break;
			}
			if (!$data) {
				$data = array();
			}
			return json_encode($data);
		}
		return false;
	}

	/**
	 * Test SMPT 
	 *
	 * @return data/false
	 */
	public static function SMTPTest() {
		if (CC_IN_ADMIN) {
		    @ob_start();
		    $test_mailer = new Mailer();
		    $test_mailer->SMTPDebug = 2;
		    $test_mailer->Debugoutput = "html";
		    $test_mailer->ClearAddresses();
		    $test_mailer->Password = $GLOBALS['config']->get('config', 'email_smtp_password');
		    $test_mailer->AddAddress($GLOBALS['config']->get('config', 'email_address'));
		    $test_mailer->Subject = "Testing CubeCart";
		    $test_mailer->Body = "Testing from CubeCart v".CC_VERSION." at ".CC_STORE_URL;
		    $test_mailer->AltBody = "Testing from CubeCart v".CC_VERSION." at ".CC_STORE_URL;
		    // Send email
		    $email_test_send_result = $test_mailer->Send();
		    $email_test_results = @ob_get_contents();@ob_end_clean();

		    if(!empty($email_test_results)) {
		      $email_test_results_data = array (
		            'request_url' => 'mailto:'.$GLOBALS['config']->get('config', 'email_address'),
		            'request' => 'Subject: Testing CubeCart',
		            'result' => $email_test_results,
		            'error' => ($email_test_send_result) ? null : "Mailer Failed" ,
		        );
		      $GLOBALS['db']->insert('CubeCart_request_log', $email_test_results_data);
		      $json = $email_test_results;
		    } else {
		      $json = "Test failed to execute. ".$test_mailer->ErrorInfo;
		    }
		    return $json;
		}
		return false;
	}

	/**
	 * Dynamic template load
	 *
	 * @param string $type
	 * @param string $search_string
	 * @return data/false
	 */
	public static function template($type, $search_string) {
		switch (strtolower($type)) {
			case 'prod_options':
				$options['options'] = Catalogue::getInstance()->displayProductOptions($search_string);				
				$GLOBALS['smarty']->assign('product',$options);
				die($GLOBALS['smarty']->fetch('templates/element.product_options.php'));
			break;
		}
		return false;	
	}

	/**
	 * View Email 
	 *
	 * @param int $id
	 * @return data/false
	 */
	public static function viewEmail($id, $mode) {
		$column = ($mode == 'content_text') ? 'content_text' : 'content_html';

		if (CC_IN_ADMIN) {
		    $email = $GLOBALS['db']->select('CubeCart_email_log', array($column), array('id' => $id));
		    if($mode == 'content_text') {
		    	return '<div style="font-family: \'Courier New\', Courier">'.nl2br($email[0][$column]).'</div>';
		    } else {
		    	return $email[0][$column];
			}
		}
		return false;
	}
}