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
    private $_search_body = array();
    private $_index_body = array();

    public function __construct()
    {
        global $glob;
        if (isset($glob['elasticsearch_hosts']) && is_array($glob['elasticsearch_hosts'])) {
            $this->_hosts = $glob['elasticsearch_hosts'];
        }
        $this->connect();
        $this->_routing_id = $this->_generateRoutingId();
    }
    public function addIndex($id, $body = array(), $index = 'product') {
        if(!empty($body)) {
            $this->_index_body = $body;
        } else {
            $this->_indexBody($id);
        }
        $params = [
            'index'     => $index,
            'id'        => $id,
            'routing'   => $this->_routing_id,
            'body'      => $this->_index_body
        ];
        try {
            return $this->_client->index($params);
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_NOTICE);
            return false;
        }
    }


    public function body($search_data) {
        $this->_search_body = array (
            'query' => 
            array (
                'bool' => 
                array (
                'must' => 
                    array ( 
                        array (
                        'bool' => 
                            array (
                                'should' => 
                                array ( 
                                    array (
                                        'match' => 
                                        array (
                                            'name' => $search_data['keywords']
                                        )
                                    ), 
                                    array (
                                        'match' => 
                                        array (
                                            'description' => $search_data['keywords']
                                        )
                                    ),
                                    array (
                                        'match' => 
                                        array (
                                            'product_codes.sku' => $search_data['keywords']
                                        )
                                    ),
                                    array (
                                        'match' => 
                                        array (
                                            'product_codes.upc' => $search_data['keywords']
                                        )
                                    ),
                                    array (
                                        'match' => 
                                        array (
                                            'product_codes.ean' => $search_data['keywords']
                                        )
                                    ),
                                    array (
                                        'match' => 
                                        array (
                                            'product_codes.jan' => $search_data['keywords']
                                        )
                                    ),
                                    array (
                                        'match' => 
                                        array (
                                            'product_codes.isbn' => $search_data['keywords']
                                        )
                                    ),
                                    array (
                                        'match' => 
                                        array (
                                            'product_codes.gtin' => $search_data['keywords']
                                        )
                                    ),
                                    array (
                                        'match' => 
                                        array (
                                            'product_codes.mpn' => $search_data['keywords']
                                        )
                                    ),
                                    array (
                                        'match' => 
                                        array (
                                            'manufacturer' => $search_data['keywords']
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );
        
        $price_range = array(); 
        if(isset($search_data['priceMin']) && is_numeric($search_data['priceMin'])) {
            $price_range[] =   array (
                    'range' => 
                    array (
                        'price_to_pay' => 
                        array (
                            'gte' => ((!empty($search_data['priceVary'])) ? round($GLOBALS['tax']->priceConvertFX($search_data['priceMin'])/1.05, 3) : (float)$search_data['priceMin'])
                        )
                    )
            );
        }

        if(isset($search_data['priceMax']) && is_numeric($search_data['priceMax'])) {
            $price_range[] =   array (
                'range' => 
                array (
                    'price_to_pay' => 
                    array (
                        'lte' => ((!empty($search_data['priceVary'])) ? round($GLOBALS['tax']->priceConvertFX($search_data['priceMax'])*1.05, 3) : (float)$search_data['priceMax'])
                    )
                )
            );
        }
        
        if(!empty($price_range)) {
            $this->_search_body['query']['bool']['must'][]['bool']['must'] = $price_range;
        }

        if(isset($search_data['manufacturer']) && is_array($search_data['manufacturer'])) {
            $manufacturers = array();
            foreach($search_data['manufacturer'] as $mid) {
                array_push($manufacturers, $GLOBALS['catalogue']->getManufacturer($mid));
            }
            if(is_array($manufacturers)) {
                $m = array();
                foreach($manufacturers as $manufacturer) {
                    $m[] = array (
                        'match' => 
                        array (
                            'manufacturer' => $manufacturer
                        )
                    );
                }
                $this->_search_body['query']['bool']['must'][]['bool']['must'] = $m;
            }
        }

        if(isset($search_data['featured']) && !empty($search_data['featured'])) {
            $this->_search_body['query']['bool']['must'][]['bool']['must'][] = array (
                    'term' => 
                    array (
                        'featured' => array(
                            'value' => 1
                        )
                    )
                );
        }

        if(isset($search_data['inStock']) && !empty($search_data['inStock'])) {
            $this->_search_body['query']['bool']['must'][]['bool']['must'][] = array (
                'range' => 
                    array (
                        'stock_level' => 
                        array (
                            'gt' => 0
                        )
                    )
                ); 
        }
    }

    public function connect($test = false) {
        $this->_client = ClientBuilder::create()->setHosts($this->_hosts)->build();
        if($test) {
            try {
                $index = bin2hex(openssl_random_pseudo_bytes(10));
                $result = $this->addIndex($index, array('test' => 1));
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

    public function getStats($index = 'product') {
        $stats = $this->_client->cat()->indices(array('index'=>$index));
        return array('size' => $stats[0]['store.size'], 'count' => $stats[0]['docs.count']);
    }

    public function _indexBody($product_id) {
        $es_data = $GLOBALS['catalogue']->getProductPrice($product_id);
        $cat = $GLOBALS['db']->select('CubeCart_category_index', array('cat_id'), array('product_id' => $es_data['product_id'], 'primary' => 1));
        $seo = SEO::getInstance();
        $this->_index_body = array(
            'name'          => (string)$es_data['name'],
            'description'   => (string)$es_data['description'],
            'price_to_pay'  => (float)$es_data['price_to_pay'],
            'category'      => (string)$seo->getDirectory((int)$cat[0]['cat_id'], false, ' ', false, false),
            'manufacturer'  => (string)$GLOBALS['catalogue']->getManufacturer($es_data['manufacturer']),
            'featured'      => (int)$es_data['featured'],
            'stock_level'   => (int)$GLOBALS['catalogue']->getProductStock($es_data['product_id']),
            'thumbnail'     => (string)$GLOBALS['gui']->getProductImage($es_data['product_id'], 'thumbnail'),
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
        if (($products = $GLOBALS['db']->select('CubeCart_inventory', array('product_id'), $where, false, $limit, $cycle)) !== false) {
            foreach ($products as $product) {
                $this->addIndex($product['product_id']);
            }
            $sent_to = $limit * $cycle;
            if ($total > $sent_to) {
                $percent = ($sent_to/$total)*100;
                //if ($percent % 10 == 0) {
                    $stats = $this->getStats();
                //}
                $data = array(
                    'count'  => $sent_to,
                    'total'  => $total,
                    'percent' => $percent,
                    'es_count' => $stats['count'],
                    'es_size' => $stats['size']
                );
                return $data;
            } else {
                return true;
            }
        } else {
            return false;
        } 
    }
    public function search($from, $size, $index = 'product') {
        $from = ($from-1)*$size;
        $params = [
            'index' => $index,
            'body'  => json_encode(array_merge(array('from' => $from, 'size' => $size),$this->_search_body))
        ];
        return $this->_client->search($params);
    }
    private function _generateRoutingId() {
        return CC_ROOT_DIR;
    }
}