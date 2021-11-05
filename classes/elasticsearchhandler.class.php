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
    // The host(s) can be set in the includes/global.inc.php file as for example 
    // $glob['elasticsearch_hosts'] = array('https://user:pass@127.0.0.1:9200');
    private $_hosts = array('127.0.0.1:9200'); 
    private $_search_body = array();
    private $_index_body = array();
    private $_index = '';
    private $_marker = array();

    public function __construct()
    {
        global $glob;
        if (isset($glob['elasticsearch_hosts']) && is_array($glob['elasticsearch_hosts'])) {
            $this->_hosts = $glob['elasticsearch_hosts'];
        }
        $this->connect();
        $this->_index = $glob['dbdatabase'];
    }

    public function addIndex($id, $body = array()) {
        if(!empty($body)) {
            $this->_index_body = $body;
        } else {
            $this->_indexBody($id);
        }
        $params = [
            'index'     => $this->_index,
            'id'        => $id,
            'body'      => $this->_index_body
        ];
        try {
            if(!$this->indexExists()) {
                $this->createWholeIndex();
            }
            return $this->_client->index($params);
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_NOTICE);
            return false;
        }
    }

    public function connect($test = false) {
        $this->_client = ClientBuilder::create()->setHosts($this->_hosts)->build();

        if($test) {
            try {
                $id = bin2hex(openssl_random_pseudo_bytes(10));
                $result = $this->addIndex($id, array('test' => 1));
                if(isset($result['result']) == 'created') {
                    $this->deleteIndex($id);
                    return true;
                } else {
                    return false;
                }
            } catch (Exception $e) {
                return false;
            }
            
        }
    }

    public function createWholeIndex() {
        $params = [
            'index' => $this->_index,
            'body' => [
                'settings' => [
                    'analysis' => [
                        'analyzer' => [
                            'autocomplete' => [
                                'tokenizer' => 'autocomplete',
                                'filter' => ['lowercase']
                            ],
                            'autocomplete_search' => ['tokenizer' => 'lowercase'],
                            'default' => [
                                'type' => 'custom',
                                'tokenizer' => 'default_tokenizer',
                                'filter' => ['lowercase','keyword_repeat','default_stemmer','unique_stem'],'char_filter' => ['default_char_filter']
                            ]
                        ],
                        'tokenizer' => [
                            'autocomplete' => [
                                'type' => 'edge_ngram',
                                'min_gram' => 2,
                                'max_gram' => 15,
                                'token_chars' => ['letter','digit','custom'],
                                'custom_token_chars' => '-_.'
                            ],
                            'default_tokenizer' => [
                                'type' => 'standard'
                            ]
                        ],
                        'filter' => [
                            'default_stemmer' => [
                                'type' => 'stemmer',
                                'language' => 'english'
                            ],
                            'unique_stem' => [
                                'type' => 'unique',
                                'only_on_same_position' => true
                            ]
                        ],
                        'char_filter' => [
                            'default_char_filter' => [
                                'type' => 'html_strip'
                            ]
                        ]
                    ]
                ],
                'mappings' => [
                    'properties' => [
                        'name' => [
                            'type' => 'text',
                            'analyzer' => 'autocomplete',
                            'search_analyzer' => 'autocomplete_search'
                        ],
                        'product_codes.sku' => [
                            'type' => 'text',
                            'analyzer' => 'autocomplete',
                            'search_analyzer' => 'autocomplete_search'
                        ]
                    ]
                ]
            ]
        ];
        return $this->_client->indices()->create($params);    
    }

    public function deleteWholeIndex() {
        $params = ['index' => $this->_index];
        return $this->_client->indices()->delete($params);
    }

    public function deleteIndex($id) {
        $params = [
            'index'     => $this->_index,
            'id'        => $id,
        ];
        try {
            return $this->_client->delete($params);
        } catch (Exception $e) {
            return false;
        }
    }

    public function exists($id = '') {
        $params = ['index' => $this->_index, 'id' => $id];
        try {
            return $this->_client->exists($params);
        } catch (Exception $e) {
            return false;
        } 
    }

    public function getStats() {
        try {
            $stats = $this->_client->cat()->indices(array('index' => $this->_index));
            return array('size' => $stats[0]['store.size'], 'count' => $stats[0]['docs.count']);
        } catch (Exception $e) {
            $error = $e->getMessage();
            $error = json_decode($error,true);
            if($error['error']['type'] == 'index_not_found_exception') {
                $GLOBALS['gui']->setError('Elasticsearch has no indicies. Please rebuild.');    
            } else {
                $GLOBALS['gui']->setError($error['error']['reason']); 
            }
            return array('size' => '0b', 'count' => '0');
        } 
        
    }

    public function indexExists() {
        $params = ['index' => $this->_index];
        return $this->_client->indices()->exists($params);
    }

    public function query($search_data) {
        
        if(!isset($search_data['keywords'])) return false;
        $es_keywords = $search_data['keywords'];
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
                                            'name' => array('query' => $es_keywords, 'boost' => 6)
                                        )
                                    ), 
                                    array (
                                        'match' => 
                                        array (
                                            'description' => array('query' => $es_keywords, 'boost' => 11)
                                        )
                                    ),
                                    array (
                                        'match' => 
                                        array (
                                            'product_codes.sku' => array('query' => $es_keywords, 'boost' => 2)
                                        )
                                    ),
                                    array (
                                        'match' => 
                                        array (
                                            'product_codes.upc' => array('query' => $es_keywords, 'boost' => 2)
                                        )
                                    ),
                                    array (
                                        'match' => 
                                        array (
                                            'product_codes.ean' => array('query' => $es_keywords, 'boost' => 2)
                                        )
                                    ),
                                    array (
                                        'match' => 
                                        array (
                                            'product_codes.jan' => array('query' => $es_keywords, 'boost' => 2)
                                        )
                                    ),
                                    array (
                                        'match' => 
                                        array (
                                            'product_codes.isbn' => array('query' => $es_keywords, 'boost' => 2)
                                        )
                                    ),
                                    array (
                                        'match' => 
                                        array (
                                            'product_codes.gtin' => array('query' => $es_keywords, 'boost' => 2)
                                        )
                                    ),
                                    array (
                                        'match' => 
                                        array (
                                            'product_codes.mpn' => array('query' => $es_keywords, 'boost' => 2)
                                        )
                                    ),
                                    array (
                                        'match' => 
                                        array (
                                            'manufacturer' => array('query' => $es_keywords, 'boost' => 6)
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

    public function rebuild($cycle) {
        
        ini_set('ignore_user_abort', true);
        if($cycle == 1) {
            if($this->indexExists()) {
                $this->deleteWholeIndex();
            }
            $this->createWholeIndex();
        }

        $limit = 25;
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
                
                if($percent % 10 == 0 && !isset($this->marker[$percent])) {
                    $this->marker[$percent] = true;
                    $stats = $this->getStats();
                } else {
                    $stats = array('count' => false, 'size' => false);
                }
                
                $data = array(
                    'count'  => $sent_to,
                    'total'  => $total,
                    'percent' => $percent,
                    'es_count' => number_format($stats['count']),
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
    
    public function search($from, $size) {
        $from = ($from-1)*$size;
        $params = [
            'index' => $this->_index,
            'body'  => json_encode(array_merge(array('from' => $from, 'size' => $size),$this->_search_body))
        ];
        
        $response = $this->_client->search($params);
        return $response;
    }

    public function updateIndex($id, $field = '') {
        switch($field) {
            case 'stock_level':
                $this->_index_body = array('stock_level'   => (int)$GLOBALS['catalogue']->getProductStock($id));
            break;
            default:
                $this->_indexBody($id);
        }
        $params = array(
            'index' => $this->_index,
            'id'    => $id,
            'body'  => array('doc' => $this->_index_body)
        );
        return $this->_client->update($params);   
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
                'sku'   => (string)$es_data['product_code'],
                'upc'   => (string)$es_data['upc'],
                'ean'   => (string)$es_data['ean'],
                'jan'   => (string)$es_data['jan'],
                'isbn'  => (string)$es_data['isbn'],
                'gtin'  => (string)$es_data['gtin'],
                'mpn'   => (string)$es_data['mpn']
            )
        );
    }
}