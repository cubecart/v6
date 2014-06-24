<?php
class Disqus {

	private $_module 	= array();
	private $_section 	= '';
	
	public function __construct($module = false, $section = null) {
		$this->_module	= $module;	
		$this->_section	= $section;	
	}

	public function getCommunityHTML() {
		if($this->_module['status'] && ($this->_module['location']==$this->_section || $this->_module['location']=='all')) {
			/* FULL LIST http://docs.disqus.com/help/2/ */
			$shortname 	= $this->_module['shortname'];
			$developer 	= $this->_module['developer'];
			$identifier = md5(currentPage());
			$permalink	= currentPage();
			return <<<END
<div id="disqus_thread"></div>
<script type="text/javascript">
    /* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
    var disqus_shortname = '$shortname'; 	// required: replace example with your forum shortname
    var disqus_developer = $developer; 		// developer mode is on
    var disqus_identifier = '$identifier';	// unique identifier
    var disqus_url = '$permalink';			// permalink

    /* * * DON'T EDIT BELOW THIS LINE * * */
    (function() {
        var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
        dsq.src = 'http://' + disqus_shortname + '.disqus.com/embed.js';
        (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
    })();
</script>
<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
<a href="http://disqus.com" class="dsq-brlink">blog comments powered by <span class="logo-disqus">Disqus</span></a>
END;
		} else {
			return false;
		}
	}
}