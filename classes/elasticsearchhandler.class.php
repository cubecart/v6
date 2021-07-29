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

    public function deleteAll($index = 'product') {
        $params = [
            'index' => $index,
            'routing' => $this->_routing_id,
            'body' => [
                'query' => [
                    'match_all' => (object)[] // defined empty object is a required workaround 
                ]
            ]
        ];
        return $this->_client->deleteByQuery($params);
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

    public function indexBody($product_id, $es_data) {
        $seo = SEO::getInstance();
        return array(
            'name'          => $es_data['name'],
            'description'   => $es_data['description'],
            'price_to_pay'  => (float)$es_data['price_to_pay'],
            'category'      => $seo->getDirectory((int)$_POST['primary_cat'], false, ' ', false, false),
            'manufacturer'  => $GLOBALS['catalogue']->getManufacturer($es_data['manufacturer']),
            'featured'      => (int)$es_data['featured'],
            'stock_level'  => $GLOBALS['catalogue']->getProductStock($product_id),
            'product_codes' => array(
                'sku'   => $es_data['product_code'],
                'upc'   => $es_data['upc'],
                'ean'   => $es_data['ean'],
                'jan'   => $es_data['jan'],
                'isbn'  => $es_data['isbn'],
                'gtin'  => $es_data['gtin'],
                'mpn'   => $es_data['mpn']
            )
        );
    }

    public function rebuild($cycle) {
        
        ini_set('ignore_user_abort', true);
        if($cycle == 1) {
            $this->deleteAll();
        }

        $limit = 20;
        $where = array('status' => 1);
        $total = (int)$GLOBALS['db']->count('CubeCart_inventory', 'status', $where);
        if($total==0 && $cycle==1) {
            $GLOBALS['gui']->setError('No produts to index.');
        }
        if (($products = $GLOBALS['db']->select('CubeCart_inventory', false, $where, false, $limit, $cycle)) !== false) {
            foreach ($products as $product) {
                $es_data = $GLOBALS['catalogue']->getProductPrice($product);
                $es_body = $this->indexBody($product['product_id'], $es_data);
                $this->addIndex($product['product_id'], $es_body);
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
    public function search($body, $from, $size, $index = 'product') {
        $from = ($from-1)*$size;
        $body = json_encode(array_merge(array('from' => $from, 'size' => $size),$body));
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