<?php
class Facebook {

	private $_module 	= array();
	private $_section 	= '';
	private $_url		= '//connect.facebook.net';
	private $_path		= '/en_US/all.js';
	
	public function __construct($module = false, $section = null) {
		$this->_module	= $module;	
		$this->_section	= $section;
	}

	public function getButtonHTML() {
		if($this->_module['status'] && $this->_module['like_status'] && ($this->_module['like_location']==$this->_section || $this->_module['like_location']=='all')) {
			$attributes[] 	= ($this->_module['button_text']=='recommend') 		? 'action="recommend"' : '';
			$attributes[]	= ($this->_module['button_showfaces']) 				? 'show_faces="true"' : 'show_faces="false"';
			$attributes[]	= ($this->_module['button_color']=='dark') 			? 'colorscheme="dark"' : '';
			$attributes[]	= ($this->_module['button_layout']!=='standard') 	? 'layout="'.$this->_module['button_layout'].'"' : '';
			$attributes[]	= 'href="'.currentPage().'"';
			//$attributes[] 	= 'width="'.$this->_module['button_width'].'"';
			
			if ($this->_section=='product') $GLOBALS['smarty']->assign('FBOG', 1);
			
			return '<script src="'.$this->_url.$this->_path.'#xfbml=1"></script><fb:like '.implode(' ', $attributes).'></fb:like>';
		} else {
			return false;
		}
	}
	
	public function getCommunityHTML() {
		if($this->_module['status'] && $this->_module['comments_status'] && ($this->_module['comments_location']==$this->_section || $this->_module['comments_location']=='all')) {
			
			return '<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "'.$this->_url.$this->_path.'#xfbml=1&appId='.$this->_module['appid'].'";
  fjs.parentNode.insertBefore(js, fjs);
}(document, \'script\', \'facebook-jssdk\'));</script>
<div class="fb-comments" data-href="'.currentPage().'" data-num-posts="'.$this->_module['comments_numposts'].'" data-width="'.$this->_module['comments_width'].'"></div>';
		
		} else {
			return false;
		}
	}
}