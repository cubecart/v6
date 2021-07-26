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
    public function rebuild($cycle) {
        
        ini_set('ignore_user_abort', true);
        
        $limit = 20;
        $where = array('status' => 1);
        $total = (int)$GLOBALS['db']->count('CubeCart_inventory', 'status', $where);
        if($total==0 && $cycle==1) {
            $GLOBALS['gui']->setError('No produts to index.');
        }
        if (($products = $GLOBALS['db']->select('CubeCart_inventory', false, $where, false, $limit, $cycle)) !== false) {
            foreach ($products as $product) {
                // INDEX HERE
            }
            $sent_to = $limit * $cycle;
            if ($total > $sent_to) {
                $data = array(
                    'count'  => $sent_to,
                    'total'  => $total,
                    'percent' => ($sent_to/$total)*100,
                );
                return $data;
            } else {
                return true;
            }
        } else {
            return false;
        } 
    }
    public function search($body, $index = 'product') {
        
        echo json_encode($body);
        echo "<hr>";
        $params = [
            'index' => $index,
            'body'  => json_encode($body)
        ];
        
        return $this->_client->search($params);
    }
    private function _generateRoutingId() {
        return CC_ROOT_DIR;
    }
}