<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2023. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
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

use Elastic\Elasticsearch\ClientBuilder;
require 'elasticsearch/vendor/autoload.php';

class ElasticsearchHandler
{
    private $_client = '';
    private $_search_body = array();
    private $_index_body = array();
    private $_index = '';
    private $_config = array();
    private $_config_file = './includes/extra/es.json';

    public function __construct($config = array())
    {
        $this->_getConfig($config);
        $this->connect();
        $this->_index = trim($this->_config['es_i']);
    }
    /**
     * Add product to index
     */
    public function add($id, $body = array()) {
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
                $this->createIndex();
            }
            $response = $this->_client->index($params);
            return $response->getStatusCode() == 200 ? true : false;
        } catch (Exception $e) {
            $this->last_error = $e->getMessage();
            return false;
        }
    }
    /**
     * Establish connection to ES
     */
    public function connect($test = false) {
        if(empty($this->_config['es_i'])) {
            $this->_logError("A unique index name is required.");
            return false;
        }
        $hosts = empty($this->_config['es_h']) ? array('https://localhost:9200') : explode(',', $this->_config['es_h']);
        $validate_ssl = ($this->_config['es_v']=='1') ? true : false;

        $this->_client = ClientBuilder::create()
            ->setHosts($hosts)
            ->setSSLVerification($validate_ssl)
            ->setCABundle($this->_config['es_c'])
            ->setBasicAuthentication($this->_config['es_u'], $this->_config['es_p'])
            ->build();
         
        if($test) {
            if(!$this->indexExists()) {
                try {
                    return $this->createIndex();
                } catch (Exception $e) {
                    $this->_logError($e->getMessage());
                    return false;
                }
            }
            return true;
        } else {
            return false;
        }
    }
    /**
     * Create index
     */
    public function createIndex() {
        $params = [
            'index' => $this->_index,
            'body' => [
                'settings' => [ 
                    'analysis' => [ 
                        'filter' => [
                            'autocomplete_filter' => [
                                'type' => 'edge_ngram',
                                'min_gram' => 1,
                                'max_gram' => 20
                            ]
                        ],
                        'analyzer' => [
                            'autocomplete' => [
                                'type' => 'custom',
                                'tokenizer' => 'standard',
                                'filter' => ['lowercase','autocomplete_filter']
                            ]
                        ]
                    ]
                ],
                'mappings' => [ 
                    'properties' => [
                        'name' => [
                            'type' => 'text',
                            'analyzer' => 'autocomplete'
                        ]
                    ]
                ]
            ]
        ];
        try {
            $response = $this->_client->indices()->create($params);
            return $response->getStatusCode() == 200 ? true : false;
        } catch (Exception $e) {
            $this->_logError($e->getMessage());
            return false;
        } 
    }
    
    /**
     * Delete index
     */
    public function deleteIndex() {
        try {
            $response =  $this->_client->indices()->delete(['index' => $this->_index]);
            return $response->getStatusCode() == 200 ? true : false;
        } catch (Exception $e) {
            $this->_logError($e->getMessage());
            return false;
        } 
    }

    /**
     * Delete product from index
     */
    public function delete($id) {
        try {
            $response =  $this->_client->delete(['index' => $this->_index, 'id' => $id]);
            return $response->getStatusCode() == 200 ? true : false;
        } catch (Exception $e) {
            $this->_logError($e->getMessage());
            return false;
        }
    }
    
    /**
     * Check product exists in index
     */
    public function exists($id = '') {
        try {
            $response = $this->_client->exists(['index' => $this->_index, 'id' => $id]);
            return $response->getStatusCode() == 200 ? true : false;
        } catch (Exception $e) {
            $this->_logError($e->getMessage());
            return false;
        } 
    }

    /**
     * Get stats about index
     */
    public function getStats() {
        try {
            $params = ['index',$this->_index];
            $params['metric'] = '_all';
            $response = $this->_client->indices()->stats($params);
            $response = json_decode($response, true);
            return array('size' => formatBytes($response['_all']['primaries']['store']['size_in_bytes'], true), 'count' => $response['_all']['primaries']['docs']['count']);
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

    /**
     * Check index exists
     */
    public function indexExists() {
        try {
            $response = $this->_client->indices()->exists(['index' => $this->_index]);
            return $response->getStatusCode() == 200 ? true : false;
        } catch (Exception $e) {
            $this->_logError($e->getMessage());
            return false;
        }
    }

    /**
     * Log error
     */
    private function _logError($message = '') {
        if(!empty($message)) {
            trigger_error('Elasticsearch: '.$message, E_USER_NOTICE);
            $this->last_error = $message;
        }
    }

    /**
     * Create search query to execute later
     */
    public function query($search, $sayt = true) {
        if(!isset($search['keywords'])) return false;
        $q = $search['keywords'];
        $must = [];
        $should = 
        [
            ['match' => 
                ['name' => 
                    ['query' => $q, 
                    'analyzer' => 'standard'
                    ]
                ]
            ],
            ['match' => ['product_code' => $q]],
            ['match' => ['upc' => $q]],
            ['match' => ['ean' => $q]],
            ['match' => ['jan' => $q]],
            ['match' => ['isbn' => $q]],
            ['match' => ['gtin' => $q]],
            ['match' => ['mpn' => $q]]
        ];
        
        if(!$sayt) { // Form submitted search 
            $should = array_merge($should, [['match' => ['description' => $q]]]);
            if(isset($search['featured']) && $search['featured']=='1') {
                $featured =
                [
                    'match' =>
                    [
                        'featured' => 1
                    ]

                ];
                array_push($must, $featured);
            }
            if(isset($search['manufacturer']) && is_array($search['manufacturer']) && !empty($search['manufacturer'])) {
                $manufacturer =
                [
                    'terms' =>
                    [
                        'manufacturer_id' => array_map('intval', $search['manufacturer'])
                    ]

                ];
                array_push($must, $manufacturer);
            }
            $price_range = [];
            if(isset($search['priceMin']) && $search['priceMin'] > 0) {
                $price = empty($search['priceVary']) ? $search['priceMin'] : round($GLOBALS['tax']->priceConvertFX($search['priceMin'])/1.05, 2); // Legacy for old skins
                $price_range['gte'] = (float)$price;
            }
            if(isset($search['priceMax']) && $search['priceMax'] > 0) {
                $price = empty($search['priceVary']) ? $search['priceMax'] : round($GLOBALS['tax']->priceConvertFX($search['priceMax'])*1.05, 2); // Legacy for old skins
                $price_range['lte'] = (float)$price;
            }
            if(!empty($price_range)) {
                $price_range =
                [
                    'range' =>
                    [
                        'price_to_pay' => $price_range
                    ]

                ];
                array_push($must, $price_range);
            }
            if(isset($search['inStock']) && $search['inStock']=='1') {
                // (digital = 1 OR stock_level > 1)
                $inStock = 
                [
                    'bool' =>
                    [
                        'should' =>
                        [
                            [
                                    
                                'range' =>
                                [
                                    'stock_level' => 
                                    [
                                        'gte' => 1
                                    ]
                                ]
                            ],
                            [
                                'match' => 
                                [
                                    'digital' => 1
                                ]
                            ]
                        ]    
                    ]
                ];
                array_push($must, $inStock);
            }
        }
        $this->_search_body = 
        [
            'query' =>
            [
                'bool' =>
                [
                    'must'      => array_merge($must,[['bool' => ['should' => $should]]])   
                ]
            ]
        ];  
    }
   


    /**
     * Execute search query
     */
    public function search($from, $size) {
        $from = ($from-1)*$size;
        $body = json_encode(array_merge(['from' => $from, 'size' => $size],$this->_search_body));
        $params = [
            'index' => $this->_index,
            'body'  => $body
        ];
        try {
            $response = $this->_client->search($params);
            return $response->getStatusCode() == 200 ? $response : false;
        } catch (Exception $e) {
            $this->_logError($e->getMessage());
            die($e->getMessage());
            return false;
        }
    }

    /**
     * Rebuild index
     */
    public function rebuild($cycle, $limit = 25) {
        ini_set('ignore_user_abort', true);
        if($cycle == 1) {
            if($this->indexExists()) {
                $this->deleteIndex();
            }
            $this->createIndex();
        }
    
        $where = array('status' => 1);
        $total = (int)$GLOBALS['db']->count('CubeCart_inventory', 'status', $where);
        if($total==0 && $cycle==1) {
            $GLOBALS['gui']->setError('No produts to index.');
        }
        if (($products = $GLOBALS['db']->select('CubeCart_inventory', array('product_id'), $where, false, $limit, $cycle)) !== false) {
            foreach ($products as $product) {
                $this->add($product['product_id']);
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

    /**
     * Update product in index
     */
    public function update($id, $field = '') {
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

    /**
     * Get config
     */
    private function _getConfig($config) {
        if(!empty($config)) {
            $es_config = array(
                'es_h' => $config['es_h'],  // Hostname
                'es_u' => $config['es_u'],  // Username
                'es_p' => $config['es_p'],  // Password
                'es_i' => $config['es_i'],  // Index name
                'es_v' => $config['es_v'],  // Validate SSL (bool)
                'es_c' => $config['es_c']   // Certificate path
            );
            $fh = fopen($this->_config_file,"wa+");
            fwrite($fh,json_encode($es_config));
            fclose($fh);
            $this->_config = $es_config;
        } else {
            $this->_config = json_decode(file_get_contents($this->_config_file),true);
        }
    }

    private function _indexToPlainText($string) {
        $string = strip_tags($string);
        $string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
        $string = preg_replace("/\s+/", " ", $string);
        return $string;
    }

    /**
     * Create body for product to be indexed
     */
    private function _indexBody($product_id) {
        $product = $GLOBALS['catalogue']->getProductData($product_id);
        $cat = $GLOBALS['db']->select('CubeCart_category_index', array('cat_id'), array('product_id' => $product['product_id'], 'primary' => 1));
        $seo = SEO::getInstance();
        $this->_index_body = array(
            'name'          => (string)$product['name'],
            'product_code'  => (string)$product['product_code'],
            'upc'           => (string)$product['upc'],
            'ean'           => (string)$product['ean'],
            'jan'           => (string)$product['jan'],
            'isbn'          => (string)$product['isbn'],
            'gtin'          => (string)$product['gtin'],
            'mpn'           => (string)$product['mpn'],
            'thumbnail'     => (string)$GLOBALS['gui']->getProductImage($product['product_id'], 'thumbnail', 'relative'),
            'description'   => (string)$this->_indexToPlainText($product['description']),
            'price_to_pay'  => (float)round($product['price_to_pay'],2),
            'category'      => (string)$seo->getDirectory((int)$cat[0]['cat_id'], false, ' ', false, false),
            'manufacturer'  => (string)$GLOBALS['catalogue']->getManufacturer($product['manufacturer']),
            'manufacturer_id'  => (int)$product['manufacturer'],
            'featured'      => (int)$product['featured'],
            'stock_level'   => (int)$GLOBALS['catalogue']->getProductStock($product['product_id']),
            'digital'       => (int)$product['digital']
        );
    }
}