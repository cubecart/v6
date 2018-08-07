<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2017. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */
header('HTTP/1.1 404 Not Found');
header('HTTP/1.0 404 Not Found');
header('Status: 404 Not Found');
?>
<html>
<head>
<title>404 Not Found</title>
</head><body>
<h1>Not Found</h1>
<p>The requested URL <?php echo htmlentities($_SERVER['REQUEST_URI'], ENT_QUOTES, "UTF-8"); ?> was not found on this server.</p>
</body>
</html>