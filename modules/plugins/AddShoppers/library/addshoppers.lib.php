<?php
/**
 * Copyright 2014
 * AddShoppers
 * All rights reserved.
 */
 
if (!function_exists('addshoppers_verify_data')){
	function addshoppers_verify_data($data,$api_secret) {
		$params = json_decode(stripslashes(urldecode($data)), true);
		$signature = null;
		$p = array();
		
		if ($params['google_picture']) $params['google_picture'] = str_replace('h--p', 'http', $params['google_picture']);
  
		foreach($params as $key => $value)
		{
    		if($key == "signature")
        		$signature = $value;
   		 	else
		    	$p[] = $key . "=" . $value;
		}
		asort($p);
		$query = $api_secret . implode($p);
		$hashed = hash("md5", $query);
		if($signature !== $hashed) return array('error' => 1); 
		else return get_profile_info($params); 
	}
}

if (!function_exists('get_profile_info')){
	function get_profile_info($data) {
		$networks = array('facebook','google','paypal','linkedin','twitter');
		$profile_data = array();
				
		foreach ($networks as $network) {
			if ($data[$network . '_email'] && !$profile_data['email'])
				$profile_data['email'] = $data[$network . '_email'];
			if ($data[$network . '_firstname'] && !$profile_data['firstname'])
				$profile_data['firstname'] = $data[$network . '_firstname'];
			if ($data[$network . '_lastname'] && !$profile_data['lastname'])
				$profile_data['lastname'] = $data[$network . '_lastname'];
		}
	return $profile_data;
	}
}
	
?>
