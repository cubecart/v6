<?php
class WishPot {

	private $_module 	= array();
	private $_section 	= '';
	
	public function __construct($module = false, $section = null) {
		$this->_module	= $module;	
		$this->_section	= $section;	
	}

	public function getButtonHTML() {
		if($this->_module['status'] && ($this->_module['location']==$this->_section || $this->_module['location']=='all')) {
		
			if(empty($this->_module['user_id'])) return false;
			$username 	= $this->_module['user_id'];
			$button_img = empty($this->_module['button_img']) ? 'addtowishpot139px26px' : $this->_module['button_img'];
			return <<<END
<div class="wishpot_form social_wrapper"><noscript>Visit <a href="http://www.wishpot.com/?pkey=$username" title="Wish list, wedding registry, baby registry" target="_blank">Wishpot add this item to your <b>wish list</b></a>.</noscript><input type="hidden" name="pkey" value="$username" /><input type="hidden" name="rc" value="button" /><a href="http://www.wishpot.com/?pkey=$username" title="Add to your wishlist at Wishpot.com" onClick="window.WISHPOT_FORM=this.parentNode;if(document.getElementById){var x=document.getElementsByTagName('head').item(0);var o=document.createElement('script');if(typeof(o)!='object') o=document.standardCreateElement('script');o.setAttribute('src', (('https:' == document.location.protocol) ? 'https://' : 'http://')+'www.wishpot.com/scripts/bm.js?v=102');o.setAttribute('type','text/javascript');x.appendChild(o);} return false;"><img src="http://www.wishpot.com/img/buttons/$button_img.png" alt="add to my wishpot" title="Add to your wishlist at Wishpot.com" border="0"/></a></div>
END;
		} else {
			return false;
		}
	}
}