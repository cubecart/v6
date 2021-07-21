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
    private $_routing_id = '';
    private $_hosts = array('localhost:9200');

    public function __construct()
    {
        global $glob;
        $status = $GLOBALS['config']->get('config', 'elasticsearch');
        if($status == '1') {
            if (isset($glob['elasticsearch_hosts']) && is_array($glob['elasticsearch_hosts'])) {
                $this->_hosts = $glob['elasticsearch_hosts'];
            }
            $this->connect();
            $this->_routing_id = $this->_generateRoutingId();
        }
    }
    public function addIndex($id, $body, $index = 'product') {
        $params = [
            'index'     => $index,
            'id'        => $id,
            'routing'   => $this->_routing_id,
            'body'      => $body
        ];
        try {
            return $this->_client->index($params);
        } catch (Exception $e) {
            return false;
        }
    }
    public function connect($test = false) {
        $this->_client = ClientBuilder::create()->setHosts($this->_hosts)->build();
        if($test) {
            try {
                $index = bin2hex(openssl_random_pseudo_bytes(10));
                $result = $this->addIndex($index, array('test'=>1));
                if(isset($result['result']) == 'created') {
                    $this->deleteIndex($index);
                    return true;
                } else {
                    return false;
                }
            } catch (Exception $e) {
                return false;
            }
            
        }
    }
    public function deleteIndex($id, $index = 'product') {
        $params = [
            'index'     => $index,
            'id'        => $id,
            'routing'   => $this->_routing_id
        ];
        try {
            return $this->_client->delete($params);
        } catch (Exception $e) {
            return false;
        }
    }
    public function search($body, $index = 'product') {
        $params = [
            'index' => $index,
            'body'  => $body
        ];
        
        return $this->_client->search($params);
    }
    private function _generateRoutingId() {
        return CC_ROOT_DIR;
    }
}