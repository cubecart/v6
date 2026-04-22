<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2026. All rights reserved.
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
 */

use Elastic\Elasticsearch\ClientBuilder;
require 'elasticsearch/vendor/autoload.php';

class ElasticsearchHandler
{
    public $last_error = '';

    private $_client;
    private $_search_body = array();
    private $_index_body = array();
    private $_index = '';
    private $_config = array();
    private $_config_file = './includes/extra/es.json';
    private $_index_exists_cache = null;

    public function __construct($config = array(), $write = false)
    {
        if($write) {
            $this->_writeConfig($config);
        }
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
        $out_of_stock_excluded = ($this->_config['es_is']=='1' && isset($this->_index_body['stock_level']) && $this->_index_body['stock_level']<=0);
        try {
            if(!$this->indexExists()) {
                if($out_of_stock_excluded) {
                    return false;
                } else {
                    $this->createIndex();
                }
            } else if ($out_of_stock_excluded) {
                return $this->delete($id);
            }
            $response = $this->_client->index($params);
            return $response->getStatusCode() == 200 ? true : false;
        } catch (Exception $e) {
            $this->_logError($e->getMessage());
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

        // Detect if using a hosted Elasticsearch service
        $isElasticCloud = false;
        $isSearchly = false;
        foreach ($hosts as $host) {
            if (strpos($host, '.elastic-cloud.com') !== false || strpos($host, '.es.io') !== false) {
                $isElasticCloud = true;
                break;
            }
            if (strpos($host, '.searchly.com') !== false) {
                $isElasticCloud = true;
                $isSearchly = true;
                break;
            }
        }
        \Elastic\Elasticsearch\Client::$isSearchly = $isSearchly;

        $clientBuilder = ClientBuilder::create()
            ->setHosts($hosts);

        // Only set SSL verification and CA bundle for self-hosted Elasticsearch
        if (!$isElasticCloud) {
            $clientBuilder
                ->setSSLVerification($validate_ssl)
                ->setCABundle($this->_config['es_c']);
        }

        // Authentication (works for both Cloud and self-hosted)
        $auth_type = isset($this->_config['es_t']) ? $this->_config['es_t'] : '0';
        switch ($auth_type) {
            case '2': // No authentication
                break;
            case '1': // API key
                $clientBuilder->setApiKey($this->_config['es_a']);
                break;
            default: // Basic authentication
                $clientBuilder->setBasicAuthentication($this->_config['es_u'], $this->_config['es_p']);
                break;
        }

        $this->_client = $clientBuilder->build();
         
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
                                'min_gram' => 2,
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
                            'analyzer' => 'autocomplete',
                            'fields' => [
                                'keyword' => ['type' => 'keyword']
                            ]
                        ],
                        'date_added' => [
                            'type' => 'keyword'
                        ],
                        'product_code' => ['type' => 'keyword'],
                        'upc'          => ['type' => 'keyword'],
                        'ean'          => ['type' => 'keyword'],
                        'jan'          => ['type' => 'keyword'],
                        'isbn'         => ['type' => 'keyword'],
                        'gtin'         => ['type' => 'keyword'],
                        'mpn'          => ['type' => 'keyword']
                    ]
                ]
            ]
        ];
        try {
            $response = $this->_client->indices()->create($params);
            $ok = ($response->getStatusCode() == 200);
            if ($ok) $this->_index_exists_cache = true;
            return $ok;
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
            $ok = ($response->getStatusCode() == 200);
            if ($ok) $this->_index_exists_cache = false;
            return $ok;
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
            $params = ['index' => $this->_index];
            $params['metric'] = '_all';
            $response = $this->_client->indices()->stats($params);
            $response = json_decode($response, true);
            return array('size' => formatBytes($response['_all']['primaries']['store']['size_in_bytes'], true), 'count' => $response['_all']['primaries']['docs']['count']);
        } catch (Exception $e) {
            $error = $e->getMessage();
            $error = json_decode($error,true);

            if(isset($error['error']['type']) && $error['error']['type'] == 'index_not_found_exception') {
                $GLOBALS['gui']->setError($GLOBALS['language']->maintain['es_no_indices']);
            } elseif(isset($error['error']['reason'])) {
                $GLOBALS['gui']->setError($error['error']['reason']);
            } else {
                $GLOBALS['gui']->setError($GLOBALS['language']->maintain['es_error'].': '.$e->getMessage());
            }
            return array('size' => '0b', 'count' => '0');
        } 
        
    }

    /**
     * Check index exists
     */
    public function indexExists() {
        if ($this->_index_exists_cache !== null) {
            return $this->_index_exists_cache;
        }
        try {
            $response = $this->_client->indices()->exists(['index' => $this->_index]);
            $this->_index_exists_cache = ($response->getStatusCode() == 200);
            return $this->_index_exists_cache;
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
    public function query($search, $sort = array()) {
        if(!isset($search['keywords'])) return false;
        $q = $search['keywords'];
        $must = [];
        $should =
        [
            ['match' =>
                ['name' =>
                    ['query' => $q,
                    'analyzer' => 'standard',
                    'boost' => 3
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

        if(count($search)>1) { // Form submitted search
            $should = array_merge($should, [
                ['match' => ['description' => ['query' => $q, 'boost' => 0.5]]],
                ['match' => ['category' => ['query' => $q, 'boost' => 1]]]
            ]);
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
        $inner_bool = ['should' => $should];
        if (!empty($q)) {
            $inner_bool['minimum_should_match'] = 1;
        }
        $this->_search_body =
        [
            'query' =>
            [
                'bool' =>
                [
                    'must'      => array_merge($must,[['bool' => $inner_bool]])
                ]
            ]
        ];
        if(!empty($sort)) {
            // Map: stock_level, price_to_pay, date_added, name.keyword
            $sort_fields = [];
            foreach ($sort as $field => $direction) {
                switch(strtolower($field)) {
                    case 'name':
                        $f = 'name.keyword';
                    break;
                    case 'date_added':
                        $f = 'date_added';
                    break;
                    case 'stock_level':
                        $f = 'stock_level';
                    break;
                    case 'price':
                        $f = 'price_to_pay';
                    break;
                    default:
                        $f = '';
                }
                switch(strtolower($direction)) {
                    case 'asc':
                        $d = 'asc';
                    break;
                    case 'desc':
                        $d = 'desc';
                    break;
                    default:
                        $d = '';
                }
                if(!empty($f) && !empty($d)) {
                    $sort_fields[] = [$f => $d];
                }
            }
            if(!empty($sort_fields)) {
                $this->_search_body['sort'] = $sort_fields;
            }
        }
    }
   


    /**
     * Execute search query
     */
    public function search($from, $size) {
        $from = ($from-1)*$size;
        $params = [
            'index' => $this->_index,
            'body'  => array_merge(['from' => $from, 'size' => $size], $this->_search_body)
        ];
        try {
            $response = $this->_client->search($params);
            return $response->getStatusCode() == 200 ? $response : false;
        } catch (Exception $e) {
            $this->_logError($e->getMessage());
            return false;
        }
    }

    /**
     * Rebuild index
     */
    public function rebuild($cycle, $limit = 500) {
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
            $GLOBALS['gui']->setError($GLOBALS['language']->maintain['es_no_products']);
        }
        if (($products = $GLOBALS['db']->select('CubeCart_inventory', array('product_id'), $where, false, $limit, $cycle)) !== false) {
            // Build a single _bulk payload for the whole batch instead of
            // one index request per product.
            $bulk_body = array();
            $exclude_stock_outs = ($this->_config['es_is']=='1');
            foreach ($products as $product) {
                $this->_indexBody($product['product_id']);
                if($exclude_stock_outs && isset($this->_index_body['stock_level']) && $this->_index_body['stock_level']<=0) {
                    continue;
                }
                $bulk_body[] = array('index' => array('_index' => $this->_index, '_id' => $product['product_id']));
                $bulk_body[] = $this->_index_body;
            }
            if (!empty($bulk_body)) {
                try {
                    $this->_client->bulk(array('body' => $bulk_body));
                } catch (Exception $e) {
                    $this->_logError($e->getMessage());
                }
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
        try {
            if(!$this->indexExists()) {
                $this->createIndex();
                return $this->add($id);
            }
            if($field === 'stock_level' && $this->_config['es_is']=='1' && isset($this->_index_body['stock_level']) && $this->_index_body['stock_level']<=0) {
                return $this->delete($id);
            }
            return $this->_client->update($params);
        } catch (Exception $e) {
            $this->_logError($e->getMessage());
            return false;
        }
    }

    /**
     * Write config to json file
     */
    private function _writeConfig($config) {
        $es_config = array(
            'es_h' => $config['es_h'],
            'es_u' => $config['es_u'],
            'es_p' => $config['es_p'],
        	'es_a' => $config['es_a'],
        	'es_t' => $config['es_t'],
            'es_i' => $config['es_i'],
            'es_v' => $config['es_v'],
            'es_c' => $config['es_c'],
            'es_is' => $config['es_is']
        );
        $fh = fopen($this->_config_file,"w");
        fwrite($fh,json_encode($es_config));
        fclose($fh);
    }

    /**
     * Get config
     */
    private function _getConfig($config) {
        global $glob;
        /* 
        #############################
        # Config Variable Reference
        #############################
        es_h = Hostname
        es_u = Username
        es_p = Password
        es_a = API Key
        es_t = Authentication Type (0 = Basic, 1 = API)
        es_i = Index name
        es_v = Validate SSL (bool)
        es_c = Certificate path
        es_is = Include out of stock
        */

        // Get config from master config which also merges global.inc.php file
        if(isset($GLOBALS['config']) && $GLOBALS['config']->has('config', 'es_h')) { 
            $this->_config = array(
                'es_h' => $GLOBALS['config']->get('config', 'es_h'),
                'es_u' => $GLOBALS['config']->get('config', 'es_u'),
                'es_p' => $GLOBALS['config']->get('config', 'es_p'),
            	'es_a' => $GLOBALS['config']->get('config', 'es_a'),
            	'es_t' => $GLOBALS['config']->get('config', 'es_t'),
                'es_i' => $GLOBALS['config']->get('config', 'es_i'),
                'es_v' => $GLOBALS['config']->get('config', 'es_v'),
                'es_c' => $GLOBALS['config']->get('config', 'es_c'),
                'es_is' => $GLOBALS['config']->get('config', 'es_is')
            );
        } else {
            if(!empty($config)) { // Get config from $_POST of admin settings page
                $this->_config = $config;
            } elseif(!empty($glob['es_h'])) { // Get config from globals.inc.php file if set
                $es_config = array(
                    'es_h' => $glob['es_h'],
                    'es_u' => $glob['es_u'],
                    'es_p' => $glob['es_p'],
                	'es_a' => $glob['es_a'],
                	'es_t' => isset($glob['es_t']) ? $glob['es_t'] : 1,
                    'es_i' => $glob['es_i'],
                    'es_v' => $glob['es_v'],
                    'es_c' => $glob['es_c'],
                    'es_is' => isset($glob['es_is']) ? $glob['es_is'] : 1
                );
                $this->_config = $es_config;
            } else { // Get config from cached settings in json file
                $this->_config = json_decode(file_get_contents($this->_config_file),true);
            }
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
        if (empty($product)) return;
        $cats = $GLOBALS['db']->select('CubeCart_category_index', array('cat_id'), array('product_id' => $product['product_id']));
        $seo = SEO::getInstance();
        $category_paths = array();
        if (!empty($cats)) {
            foreach ($cats as $c) {
                $path = $seo->getDirectory((int)$c['cat_id'], false, ' ', false, false);
                if (!empty($path)) $category_paths[] = (string)$path;
            }
        }
        // Sorters / numeric / always-present fields
        $this->_index_body = array(
            'name'          => (string)$product['name'], ## Searchable (autocomplete) and sortable via name.keyword multi-field
            'date_added'    => (string)$product['date_added'], ## Sorter
            'stock_level'   => (int)$GLOBALS['catalogue']->getProductStock($product['product_id']), ## Sorter
            'price_to_pay'  => (float)round($product['price_to_pay'],2), ## Sorter
            'manufacturer_id' => (int)$product['manufacturer'],
            'featured'      => (int)$product['featured'],
            'digital'       => (int)$product['digital']
        );

        // Optional fields — only include when populated so empty strings
        // don't pollute keyword mappings or `exists` queries
        $optional = array(
            'product_code'  => (string)$product['product_code'],
            'upc'           => (string)$product['upc'],
            'ean'           => (string)$product['ean'],
            'jan'           => (string)$product['jan'],
            'isbn'          => (string)$product['isbn'],
            'gtin'          => (string)$product['gtin'],
            'mpn'           => (string)$product['mpn'],
            'thumbnail'     => (string)$GLOBALS['gui']->getProductImage($product['product_id'], 'thumbnail', 'relative'),
            'description'   => (string)$this->_indexToPlainText($product['description']),
            'category'      => implode(' ', $category_paths),
            'manufacturer'  => (string)$GLOBALS['catalogue']->getManufacturer($product['manufacturer'])
        );
        foreach ($optional as $k => $v) {
            if ($v !== '' && $v !== null) {
                $this->_index_body[$k] = $v;
            }
        }
    }
}
