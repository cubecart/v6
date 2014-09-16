<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@devellion.com
 * License:  GPL-2.0 http://opensource.org/licenses/GPL-2.0
 */
class Pinterest {

	private $_module 	= array();
	private $_section 	= '';
	
	public function __construct($module = false, $section = null) {
		$this->_module	= $module;	
		$this->_section	= $section;	
	}

	public function getButtonHTML() {
		if($this->_module['status'] && ($this->_module['location']==$this->_section || $this->_module['location']=='all')) {
		
			if(empty($this->_module['username'])) return false;
			$username 	= $this->_module['username'];
			$button_img = empty($this->_module['button_img']) ? 'pinterest-button' : $this->_module['button_img'];
			$button_img = str_replace('_','-',$button_img);
			return <<<END
<div class="pinterest_form social_wrapper"><a href="http://pinterest.com/$username/"><img src="http://passets-cdn.pinterest.com/images/$button_img.png"  alt="Follow Me on Pinterest" /></a></div>
END;
		} else {
			return false;
		}
	}
}