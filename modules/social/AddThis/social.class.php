<?php
class AddThis {

	private $_module 	= array();
	private $_html		= array();
	private $_protocol	= '';
	private $_url		= '';
	private $_path		= '';
	private $_section 	= '';
	
	public function __construct($module = false, $section = null) {
		$this->_module				= $module;
		$this->_section				= $section;
		$this->_module['username'] 	= empty($module['username']) ? 'cubecart' : $module['username'];
		$this->_protocol 			= CC_SSL ? 'https://' : 'http://';
		$this->_url					= 's7.addthis.com';
		$this->_path				= '/js/250/addthis_widget.js';
						
	}

	public function getButtonHTML() {
		if(!$GLOBALS['session']->cookiesBlocked()) {		
			if($this->_module['status'] && ($this->_module['location']==$this->_section || $this->_module['location']=='all')) {
				$wrapper_style = array (
					'addthis_toolbox', 'addthis_default_style'
				);
				if($this->_module['large_icons']) {
					$wrapper_style = array_merge($wrapper_style,array('addthis_32x32_style'));
					$height = '34';
				} else {
					$height = '18';
				}
				
				$this->_html[] = '<div class="'.implode(' ', $wrapper_style).' social_wrapper" style="height: '.$height.'px">';
					if(!empty($this->_module['specific_buttons'])) {
						$services = explode(',',$this->_module['specific_buttons']);
						foreach($services as $service) {
							$this->_html[] = '<a class="addthis_button_'.$service.'"></a>';
						}
					} else {			
						for ($i = 1; $i <= $this->_module['preferred_count']; $i++) {
							$this->_html[] = '<a class="addthis_button_preferred_'.$i.'"></a>';
						}
					}
					if($this->_module['large_icons']){
						$this->_html[] = '<a class="addthis_button_compact"></a>';
					} else {
						$this->_html[] = '<a href="http://www.addthis.com/bookmark.php?v=250&username='.$this->_module['username'].'" class="addthis_button_compact">'.$GLOBALS['language']->account['share'].'</a>';
					}
				$this->_html[] = '</div>';
				if($this->_module['analytics']) {
					$this->_html[] = '<script type="text/javascript">var addthis_config = {"data_track_clickback":true};</script>';
				}
				$this->_html[] = '<script type="text/javascript" src="'.$this->_protocol.$this->_url.$this->_path.'#username='.$this->_module['username'].'"></script>';
				
                if ($this->_section=='product') $GLOBALS['smarty']->assign('FBOG', 1);
				
				return implode("\r\n", $this->_html);
			} else {
				return false;
			}
		} else {
			define('THIRD_PARTY_COOKIES',true);
		}
	}
}