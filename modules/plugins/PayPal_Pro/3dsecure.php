<?php
require '../../../ini.inc.php';
require CC_ROOT_DIR.'/includes/global.inc.php';
require CC_ROOT_DIR.'/includes/functions.inc.php';
require CC_ROOT_DIR.'/classes/session.class.php';
$config	= $glob;

// Initialize Cache
$GLOBALS['cache'] = Cache::getInstance();
// Initalise Database class, and fetch default configuration
$GLOBALS['db'] = Database::getInstance($glob);
// Initalise Config class
$GLOBALS['config'] = Config::getInstance($glob);
//We will not need this anymore
unset($glob);
$GLOBALS['config']->merge('config', '', $config_default);
// Initialize debug
$GLOBALS['debug'] = Debug::getInstance();
//Initialize sessions
$GLOBALS['session'] = Session::getInstance();
//Initialize Smarty
$GLOBALS['smarty'] = new Smarty();
$GLOBALS['smarty']->compile_dir  = CC_SKIN_CACHE_DIR;
$GLOBALS['smarty']->config_dir   = CC_SKIN_CACHE_DIR;
$GLOBALS['smarty']->cache_dir    = CC_SKIN_CACHE_DIR;
$GLOBALS['smarty']->error_reporting = E_ALL & ~E_NOTICE;
//Initialize SSL
$GLOBALS['ssl'] = SSL::getInstance();
//Initialize language
$GLOBALS['language'] = Language::getInstance();
//Initialize hooks
$GLOBALS['hooks'] = HookLoader::getInstance();
//Initialize GUI
$GLOBALS['gui'] = GUI::getInstance();
//Initialize SEO
$GLOBALS['seo'] = SEO::getInstance();
//Initialize Taxes
$GLOBALS['tax'] = Tax::getInstance();
//Initialize catalogue
$GLOBALS['catalogue'] = Catalogue::getInstance();
//Initialize cubecart
$GLOBALS['cubecart'] = Cubecart::getInstance();
//Initialize user
$GLOBALS['user'] = User::getInstance();
//Initialize cart
$GLOBALS['cart'] = Cart::getInstance();
$GLOBALS['debug']->supress();
?>
<!DOCTYPE html>
<html>
<head>
<title>Launch Payer Authentication Page</title>
<script language="javascript">
function onLoadHandler(){
	document.processform.submit();
}
</script>
</head>
<body onload="onLoadHandler();">
  <form name="processform" method="post" action="<?php echo $GLOBALS['session']->get('ACSUrl', 'centinel') ?>" />
	<input type="hidden" name="PaReq" value="<?php echo $GLOBALS['session']->get('Payload', 'centinel') ?>" />
	<input type="hidden" name="TermUrl" value="<?php echo $GLOBALS['session']->get('TermUrl', 'centinel') ?>" />
	<input type="hidden" name="MD" value="" />
	<noscript>
	  <h2>Processing your Payer Authentication Transaction</h2> 
	  <h3>JavaScript is currently disabled or is not supported by your browser.</h3> 
	  <h4>Please click Submit to continue the processing of your transaction.</h4> 
	  <input type="submit" value="Submit"> 
	</noscript> 
  </form>
</body>
</html>