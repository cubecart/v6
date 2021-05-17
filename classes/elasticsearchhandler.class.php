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
if (!defined('CC_INI_SET')) {
    die('Access Denied');
}

/**
 * Elasticsearch Handler
 *
 * @author Al Brookbanks
 * @since 6.5.0
 */

use Elasticsearch\ClientBuilder;
require 'elasticsearch/autoload.php';

class ElasticsearchHandler
{
    private $_client = '';

    public function __construct()
    {
        $status = $GLOBALS['config']->get('config', 'elasticsearch');
        if($status == '1') {
            $this->_client = ClientBuilder::create()->build();
        }
    }
}