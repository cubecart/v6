{literal}
<!-- AddShoppers Social Login -->
<script type="text/javascript">
function init() {
   AddShoppersWidget.API.Event.bind("sign_in", createAccount);		
};
function createAccount(params) {	
	if (params.source == "social_login") {	
		var data = AddShoppersWidget.API.User.signed_data();
		if ("google_picture" in data) {
			data.google_picture = data.google_picture.replace('http', 'h--p');
		}
		window.location.href = window.location.href + (window.location.search ? '&' : '?') + 'as_signature=' + JSON.stringify(data);
	}
}
if (window.addEventListener) {			
   window.addEventListener("load", init, false); 
} else {
   document.onreadystatechange = function() { 
    if(document.readyState in {loaded: 1, complete: 1}) {
	document.onreadystatechange = null; 
       init();			
    } 
  }					
} 
</script>
{/literal}