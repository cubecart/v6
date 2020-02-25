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

/**
 * Catalogue controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class Catalogue
{
    private $_category_count  = 0;
    private $_category_products  = 0;
    private $_category_status_prod_id = array();
    private $_categoryData;
    private $_productData;
    private $_productHash = array();
    private $_pathElements;
    private $_category_translations = false;
    private $_option_required = false;
    private $_options_line_price = 0;
    private $_sort_by_relevance = false;
    private $_where_live_from = '';

    const OPTION_SELECT     = 0;
    const OPTION_TEXTBOX    = 1;
    const OPTION_TEXTAREA   = 2;
    const OPTION_PASSWORD = 3;
    const OPTION_RADIO  = 4;
    const OPTION_CHECKBOX = 5;
    const OPTION_DATEPICKER = 6;
    const OPTION_HIDDEN  = 7;
    const OPTION_FILE  = 8;

    private $_options_selectable = array( // fixed values
        self::OPTION_SELECT,
        self::OPTION_RADIO,
        self::OPTION_CHECKBOX,
        self::OPTION_HIDDEN,
    );
    
    private $_options_textual = array(
        self::OPTION_TEXTBOX,
        self::OPTION_TEXTAREA,
        self::OPTION_PASSWORD,
        self::OPTION_DATEPICKER,
        self::OPTION_FILE,
    );

    /**
     * Class instance
     *
     * @var instance
     */
    protected static $_instance;

    final protected function __construct()
    {
        if($pre_release = $GLOBALS['db']->select('CubeCart_inventory', 'MIN(`live_from`) AS `next_time`', '`live_from` > UNIX_TIMESTAMP()', false, 1, false, false)) {
            $this->_where_live_from = ' AND `live_from` < UNIX_TIMESTAMP() ';
        }
    }

    /**
     * Setup the instance (singleton)
     *
     * @return Catalogue
     */
    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    //=====[ Public ]=======================================

    /**
     * Build HTML select of categories
     *
     * @param int $parent_id
     * @param string $breakout
     * @param int $spaces
     * @return array
     */
    public function buildCategoriesDropDown($parent_id = 0, $breakout = '|', $spaces = 0)
    {
        $out = array();
        if (($categories = $GLOBALS['db']->select('CubeCart_category', array('cat_parent_id', 'cat_id', 'cat_name'), array('cat_parent_id' => $parent_id), 'priority, cat_name ASC')) !== false) {
            foreach ($categories as $category) {
                $out[] = array(
                    'cat_id' => $category['cat_id'],
                    'name'  => ($spaces > 0) ? str_repeat('&nbsp;', $spaces).$breakout.' '.$category['cat_name'] : $category['cat_name'],
                );
                if (($children = $GLOBALS['db']->count('CubeCart_category', 'cat_id', array('cat_parent_id' => $category['cat_id']))) !== false) {
                    $out = array_merge($out, $this->buildCategoriesDropDown($category['cat_id'], $breakout, $spaces + 2));
                }
            }
        }

        return $out;
    }

    /**
     * Paginate categories
     *
     * @param int $page
     * @return nothing
     */
    public function categoryPagination($page)
    {
        if ($this->_category_count) {
            //Pagination
            $catalogue_products_per_page = $GLOBALS['gui']->itemsPerPage('products', 'perpage');
            if(ctype_digit($page)) {
                if (($page * $catalogue_products_per_page) > $this->_category_count) {
                    $new_page = (int)ceil($this->_category_count / $catalogue_products_per_page);
                    if ($new_page < $page) {
                        httpredir(currentPage(null, array('page'=>$new_page)));
                    }
                }
            }
            if (($pages = $GLOBALS['db']->pagination($this->_category_count, $catalogue_products_per_page, $page)) !== false) {
                // Display pagination
                $GLOBALS['smarty']->assign('PAGINATION', $pages);
            }
        }
    }

    /**
     * Build category breadcrumb path
     *
     * @param int $category_id
     * @param string $glue
     * @param bool $link
     * @param bool $reverse_sort
     * @param bool $top
     * @return string
     */
    public function categoryPath($category_id, $glue = '/', $link = true, $reverse_sort = true, $top = true)
    {
        if ($top) {
            $this->_pathElements = null;
        }
        if (is_numeric($category_id) && $category_id > 0) {
            $this->getCategoryData($category_id);
            $this->_categoryTranslation();

            $result = $this->_categoryData;
            $this->_pathElements[] = ($link) ? sprintf('<a href="'.$GLOBALS['storeURL'].'/index.php?_a=viewCat&cat_id=%d">%s</a>', $result['cat_id'], $result['cat_name']) : $result['cat_name'];
            if ($result['cat_parent_id'] != 0) {
                $this->categoryPath($result['cat_parent_id'], $glue, $link, $reverse_sort, false);
            }
        }
        if (is_array($this->_pathElements)) {
            ($reverse_sort) ? krsort($this->_pathElements) : ksort($this->_pathElements);
            return implode($glue, $this->_pathElements);
        }
    }

    /**
     * Create unique option combination identifier
     *
     * @param array $optionsArray
     * @return string
     */
    public function defineOptionsIdentifier($optionsArray)
    {
        if (is_array($optionsArray)) {
            foreach ($optionsArray as $value) {
                if (is_numeric($value)) {
                    $assign_ids[] = $value;
                }
            }

            if (is_array($assign_ids)) {
                $query = 'SELECT `option_id`, `value_id` FROM `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_option_assign` WHERE `matrix_include` = 1 AND `assign_id` IN ('.implode(',', $assign_ids).') ORDER BY `option_id`, `value_id` ASC';

                $option_identifiers = $GLOBALS['db']->query($query);
                // Update product code & stock based on options matrix

                $options_identifier_string = '';
                if (is_array($option_identifiers)) {
                    foreach ($option_identifiers as $option_identifier) {
                        $options_identifier_string .= $option_identifier['option_id'].$option_identifier['value_id'];
                    }
                    return md5($options_identifier_string);
                }
            }
        }
        return '';
    }

    /**
     * Work our short description based on config length
     *
     * @param array $product
     * @return string
     */
    public function descriptionShort($product)
    {
        ## Short Description
        $product_precis = $GLOBALS['config']->get('config', 'product_precis');
        $product_precis = (is_numeric($product_precis) && $product_precis > 0) ? $product_precis : 0;
        
        if (empty($product['description_short'])) {
            $short_description = strip_tags($product['description']);
            $substr = true;
        } else {
            // Allow HTML if length without HTML is under the limit
            $short_description = strip_tags($product['description_short']);
            if ($product_precis>0 && strlen($short_description)<=$product_precis) {
                $short_description = $product['description_short'];
                $substr = false;
            } else {
                $substr = true;
            }
        }
        
        if ($substr && $product_precis>0 && strlen($short_description)>$product_precis) {
            return htmlentities(substr(html_entity_decode($short_description, ENT_QUOTES, 'UTF-8'), 0, $product_precis), ENT_QUOTES, 'UTF-8').'&hellip;';
        } else {
            return $short_description;
        }
    }

    /**
     * Display category list page
     *
     * @return bool
     */
    public function displayCategory()
    {

        // Allow hooks to see/change what will be displayed
        $catData = $this->_categoryData;
        $products = $this->_category_products;

        foreach ($GLOBALS['hooks']->load('class.cubecart.pre_display_category') as $hook) {
            include $hook;
        }

        if (isset($catData) && is_array($catData)) {
            $vars['category'] = $catData;

            if (!empty($catData['cat_image'])) {
                $vars['category']['image'] = $this->imagePath($catData['cat_image'], 'category', 'url');
            }
            $GLOBALS['smarty']->assign('category', $vars['category']);
            $meta_data = array(
                'name'   => (isset($catData['cat_name'])) ? $catData['cat_name'] : '',
                'path'   => null,
                'description' => (isset($catData['seo_meta_description'])) ? $catData['seo_meta_description'] : '',
                'title'   => (isset($catData['seo_meta_title'])) ? $catData['seo_meta_title'] : '',
            );
            $GLOBALS['seo']->set_meta_data($meta_data);
        } elseif ($_GET['_a'] !== 'saleitems') {
            $GLOBALS['gui']->setError($GLOBALS['language']->catalogue['error_category_error']);
            return false;
        }

        if (!empty($products)) {
            foreach ($products as $product) {
                $product = $this->getProductPrice($product);
                // ctrl_stock True when a product is considered 'in stock' for purposes of allowing a purchase, either by actually being in stock or via certain settings
                $product['ctrl_stock'] = (!$product['use_stock_level'] || $GLOBALS['config']->get('config', 'basket_out_of_stock_purchase') || ($product['use_stock_level'] && $GLOBALS['catalogue']->getProductStock($product['product_id'], null, true) > 0)) ? true : false;
                $this->productAssign($product, false);
                $product['url'] = $GLOBALS['seo']->buildURL('prod', $product['product_id'], '&');
                $product['options'] = $GLOBALS['catalogue']->getProductOptions($product['product_id']);
                $vars['products'][] = $product;
            }

            $GLOBALS['smarty']->assign('PRODUCTS', $vars['products']);
        }

        if (!empty($catData)) {
            $GLOBALS['smarty']->assign('SUBCATS', $this->displaySubCategory(isset($_GET['cat_id']) ? $_GET['cat_id'] : ''));
            // Generate Breadcrumbs
            $string = $GLOBALS['seo']->getDirectory((isset($catData['cat_id'])) ? $catData['cat_id'] : '', true, '|');
            $cats = explode('|', $string);
            if (is_array($cats)) {
                foreach ($cats as $cat) {
                    if (preg_match('#^<a href="(.*)">(.*)</a>$#', $cat, $match)) {
                        $GLOBALS['gui']->addBreadcrumb($match[2], $match[1]);
                    }
                }
            }
        }

        // Sorting
        $GLOBALS['smarty']->assign('SORTING', $this->displaySort());
                
        $GLOBALS['smarty']->assign('PAGE_SPLITS', $GLOBALS['gui']->perPageSplits());
        
        foreach ($GLOBALS['hooks']->load('class.cubecart.display_category') as $hook) {
            include $hook;
        }
        $content = $GLOBALS['smarty']->fetch('templates/content.category.php');
        $GLOBALS['smarty']->assign('PAGE_CONTENT', $content);

        return true;
    }

    /**
     * Display product detail page
     *
     * @param int $product
     * @param bool $popularity
     * @return bool
     */
    public function displayProduct($product = false, $popularity = false)
    {
        if (isset($product) && is_numeric($product)) {
            if (($product = $this->getProductData($product)) !== false) {
                $product['condition'] = $GLOBALS['language']->common[$product['condition']];
                $meta_data = array(
                    'name'   => $product['name'],
                    'path'   => null,
                    'description' => $product['seo_meta_description'],
                    'title'   => $product['seo_meta_title'],
                );
                $GLOBALS['seo']->set_meta_data($meta_data);

                // Update popularity
                if ($popularity) {
                    $this->_productPopularity($product['product_id']);
                }

                if (isset($_GET['error']) && !empty($_GET['error'])) {
                    switch (strtolower($_GET['error'])) {
                    case 'option':
                        $GLOBALS['gui']->setError($GLOBALS['language']->catalogue['error_option_required']);
                        break;
                    default:
                        // No error defined
                        break;
                    }
                }
                $this->productAssign($product);

                // Show manfacturer
                if (($manufacturer = $this->getManufacturer($product['manufacturer'])) !== false) {
                    $GLOBALS['smarty']->assign('MANUFACTURER', $manufacturer);
                }

                // Display gallery
                $GLOBALS['smarty']->assign('GALLERY', $this->_productGallery($product['product_id']));
                $product_options = $this->displayProductOptions($product['product_id']);
                $GLOBALS['smarty']->assign('OPTIONS', $product_options);

                $allow_purchase = true;
                $out = $hide = false;

                if ((bool)$product['use_stock_level']) {
                    // Get Stock Level
                    $stock_level = ($product_options) ? $this->getProductStock($product['product_id'], null, true) : $product['stock_level'];

                    $product['stock_level'] = ($stock_level>0) ? $stock_level : 0;
                    if ((int)$stock_level <= 0) {
                        // Out of Stock
                        if (!$GLOBALS['config']->get('config', 'basket_out_of_stock_purchase')) {
                            // Not Allowed
                            $allow_purchase = false;
                            $out = true;
                        }
                    }
                }

                if ($GLOBALS['session']->get('hide_prices')) {
                    $allow_purchase = false;
                    $hide = true;
                }

                $GLOBALS['smarty']->assign('CTRL_ALLOW_PURCHASE', $allow_purchase);
                $GLOBALS['smarty']->assign('CTRL_HIDE_PRICES', $hide);
                $GLOBALS['smarty']->assign('CTRL_OUT_OF_STOCK', $out);

                $GLOBALS['smarty']->assign('REVIEW_SCORE_MAX', 5);
                //Are we displaying reviews, or the "tell-a-friend" form?

                $GLOBALS['smarty']->assign('CTRL_REVIEW', (bool)$GLOBALS['config']->get('config', 'enable_reviews'));
                // Display Reviews
                $page  = (isset($_GET['page']) && !empty($_GET['page'])) ? $_GET['page'] : 1;
                $per_page = 5;
                if (($reviews = $GLOBALS['db']->select('CubeCart_reviews', false, array('approved' => 1, 'product_id' => $product['product_id']), 'time DESC', $per_page, $page)) !== false) {
                    if (($paginate = $GLOBALS['db']->select('CubeCart_reviews', 'SUM(`rating`) AS Score, COUNT(`id`) as Count', array('approved' => 1, 'product_id' => $product['product_id']))) !== false) {
                        $review_count = (int)$paginate[0]['Count'];
                        $review_score = $paginate[0]['Score'];
                        $GLOBALS['smarty']->assign('PAGINATION', $GLOBALS['db']->pagination($review_count, $per_page, $page));
                    }
                    foreach ($reviews as $review) {
                        if ($review['anon']) {
                            $review['name'] = $GLOBALS['language']->catalogue['review_anon'];
                        }
                        $review['date']  = formatTime($review['time']);
                        $review['date_schema'] = formatTime($review['time'], '%G-%m-%d', true);
                        if($GLOBALS['config']->get('config', 'enable_reviews')==='1') {
                            $review['gravatar'] = md5(strtolower(trim($review['email'])));
                            $review['gravatar_src'] = 'https://www.gravatar.com/avatar/'.$review['gravatar'].'?d=404&r=g';
                            $headers = get_headers($review['gravatar_src']);
                            $review['gravatar_exists'] = strstr($headers[0], '200') ? true : false;
                        } else {
                            $review['gravatar_exists'] = false;
                        }
                        $vars[] = $review;
                    }
                    $GLOBALS['smarty']->assign('REVIEWS', $vars);
                    $GLOBALS['smarty']->assign('REVIEW_COUNT', (int)$review_count);
                    $GLOBALS['smarty']->assign('REVIEW_AVERAGE', round($review_score/$review_count, 1));
                }
                for ($i = 1; $i <= 5; ++$i) {
                    $star = array(
                        'value'  => $i,
                        'checked' => (isset($_POST['rating']['rating']) && $_POST['rating']['rating'] == $i) ? 'checked="checked"' : '',
                    );
                    $vars['rating_stars'][] = $star;
                    $GLOBALS['smarty']->assign('RATING_STARS', $vars['rating_stars']);
                }

                $product['url'] = $GLOBALS['seo']->buildURL('prod', $product['product_id'], '&');
                $product['options'] = $GLOBALS['catalogue']->getProductOptions($product['product_id']);

                // Get stock level variations for options
                if ($product_options && $stock_variations = $GLOBALS['db']->select('CubeCart_option_matrix', 'MAX(stock_level) AS max_stock, MIN(stock_level) AS min_stock', array('product_id' => $product['product_id'], 'use_stock' => 1, 'status' => 1), false, 1, false, false)) {
                    if (is_numeric($stock_variations[0]['min_stock']) && is_numeric($stock_variations[0]['max_stock'])) {
                        $product['stock_level'] =  ($stock_variations[0]['min_stock'] == $stock_variations[0]['max_stock']) ? $stock_variations[0]['max_stock'] : $stock_variations[0]['min_stock'].' - '.$stock_variations[0]['max_stock'];
                    }
                }
                $product['stock_level'] = ($GLOBALS['config']->get('config', 'stock_level')=='1') ? $product['stock_level'] : false;
                $product['unsuppressed_stock_level'] = $product['stock_level'];
                $GLOBALS['smarty']->assign('PRODUCT', $product);
            }
            if (($category = $GLOBALS['db']->select('CubeCart_category_index', false, array('product_id' => (int)$product['product_id'], 'primary' => 1), array('priority' => 'DESC'), 1)) !== false) {
                $string = $GLOBALS['seo']->getDirectory($category[0]['cat_id'], true, '|');
                $cats = explode('|', $string);
                if (is_array($cats)) {
                    foreach ($cats as $cat) {
                        if (preg_match('#^<a href="(.*)">(.*)</a>$#', $cat, $match)) {
                            $GLOBALS['gui']->addBreadcrumb($match[2], $match[1]);
                        }
                    }
                }
                $GLOBALS['gui']->addBreadcrumb($product['name'], currentPage());
            }

            // Output to main GUI
            foreach ($GLOBALS['hooks']->load('class.cubecart.display_product') as $hook) {
                include $hook;
            }
            if (isset($contentDefined) && $contentDefined === true) {
                return true;
            }
            $content = $GLOBALS['smarty']->fetch('templates/content.product.php');
            $GLOBALS['smarty']->assign('SECTION_NAME', 'product');
            $GLOBALS['smarty']->assign('PAGE_CONTENT', $content);

            return true;
        }

        return false;
    }

    /**
     * Display product option
     *
     * @param int $product_id
     * @param array $selected_options_array
     * @return array/false
     */
    public function displayProductOptions($product_id = null, $selected_options_array = null)
    {
        if (isset($product_id) && is_numeric($product_id)) {
            if (is_array($selected_options_array)) {
                foreach ($selected_options_array as $selected_assign_id => $value) {
                    if (is_array($value)) {
                        foreach ($value as $selected_assign_id => $value) {
                            $selected[$selected_assign_id] = $value;
                        }
                    } else {
                        $selected[$value] = $value;
                    }
                }
            }

            $optionArray = $this->getProductOptions($product_id);
            $this->_options_line_price = 0; // Reset option line price
            if (is_array($optionArray)) {
                ksort($optionArray);
                foreach ($optionArray as $type => $group) {
                    switch ($type) {
                    case self::OPTION_SELECT:  ## Dropdown options
                    case self::OPTION_RADIO:  ## Radio options
                        foreach ($group as $key => $option) {
                            $group_priority = $option['priority'];
                            unset($option['priority']);
                            foreach ($option as $value) {
                                if (!isset($option_list[$value['option_id']])) {
                                    $option_list[$value['option_id']] = array(
                                        'type'   => $value['option_type'],
                                        'option_id'  => $value['option_id'],
                                        'option_name' => $value['option_name'],
                                        'option_description' => $value['option_description'],
                                        'option_default' => (bool)$value['option_default'],
                                        'required'  => (bool)$value['option_required'],
                                        'selected' => isset($selected[$value['assign_id']]) ? true : false
                                    );
                                }
                    
                                $decimal_price_sign = $value['option_negative'] ? '-' : '';
                                $symbol = (isset($value['option_price']) && $value['option_price']!=0 && $value['option_negative'] == 0) ? '+' : '-';

                                $option_list[$value['option_id']]['values'][] = array(
                                    'assign_id'  => $value['assign_id'],
                                    'decimal_price'   => (string)$decimal_price_sign.$value['option_price'],
                                    'price'   => (isset($value['option_price']) && $value['option_price']!=0) ? Tax::getInstance()->priceFormat($value['option_price'], true) : false,
                                    'symbol'  => ($value['absolute_price']=='1' && $symbol=='+') ? '' : $symbol,
                                    'value_id'  => $value['value_id'],
                                    'value_name' => $value['value_name'],
                                    'option_default' => (bool)$value['option_default'],
                                    'selected' => isset($selected[$value['assign_id']]) ? true : false,
                                    'absolute_price' => $value['absolute_price']
                                );
                                
                                if (isset($selected[$value['assign_id']]) && $selected[$value['assign_id']] > 0) {
                                    if ($value['absolute_price']=='1') {
                                        $this->_options_line_price =  $value['option_price'];
                                    } else {
                                        if ($value['option_price']>0 && $value['option_negative'] == 0) {
                                            $this->_options_line_price +=  $value['option_price'];
                                        } elseif ($value['option_price']>0) {
                                            $this->_options_line_price -=  $value['option_price'];
                                        }
                                    }
                                }
                            }
                            $option_list[$value['option_id']]['priority'] = $group_priority;
                        }
                        
                        break;
                    case self::OPTION_TEXTBOX:  ## Textbox options
                    case self::OPTION_TEXTAREA:  ## Textarea option
                        
                        foreach ($group as $key => $option) {
                            $price = (isset($option[0]['option_price']) && $option[0]['option_price']>0) ? Tax::getInstance()->priceFormat($option[0]['option_price']) : false;
                            $symbol = (isset($option[0]['option_price']) && $option[0]['option_negative'] == 0) ? '+' : '-';
                            $description = trim(str_replace(array($option[0]['option_name'].':','('.$symbol.$price.')'), '', $selected[$option[0]['assign_id']]));
                            
                            $decimal_price_sign = $option[0]['option_negative'] ? '-' : '';
                            
                            $option_list[$option[0]['option_id']] = array(
                                'type'   => $option[0]['option_type'],
                                'option_id'  => $option[0]['option_id'],
                                'assign_id'  => $option[0]['assign_id'],
                                'option_name' => $option[0]['option_name'],
                                'option_description' => $option[0]['option_description'],
                                'required'  => (bool)$option[0]['option_required'],
                                'price'   => $price,
                                'decimal_price'   => (string)$decimal_price_sign.$option[0]['option_price'],
                                'symbol'  => ($option[0]['absolute_price']=='1' && $symbol=='+') ? '' : $symbol,
                                'priority'      => $option['priority'],
                                'value'	=> $description,
                                'absolute_price' => $option[0]['absolute_price']
                            );
                            
                            if ($option[0]['absolute_price']=='1') {
                                $this->_options_line_price =  $option[0]['option_price'];
                            } else {
                                if ($option[0]['option_price']>0 && $option[0]['option_negative'] == 0) {
                                    $this->_options_line_price +=  $option[0]['option_price'];
                                } elseif ($option[0]['option_price']>0) {
                                    $this->_options_line_price -=  $option[0]['option_price'];
                                }
                            }
                        }
                        break;
                    }
                }
                uasort($option_list, 'cmpmc'); // sort groups
                foreach ($GLOBALS['hooks']->load('class.catalogue.display_product_options') as $hook) {
                    include $hook;
                }
                return $option_list;
            }
        }
        return false;
    }

    /**
     * Display sort on category list
     *
     * @param string $search
     * @return array
     */
    public function displaySort($search = false)
    {
        // Default sort order
        $default = array('field'=>$GLOBALS['config']->get('config', 'product_sort_column'), 'sort'=>$GLOBALS['config']->get('config', 'product_sort_direction'));
        // Sort
        if ($search || $this->_sort_by_relevance) {
            $sorters['Relevance'] = $GLOBALS['language']->common['relevance'];
            $default['field'] = 'Relevance'; // default search order is always 'Relevance'
        }
        $sorters['name']  = $GLOBALS['language']->common['name'];
        $sorters['date_added'] = $GLOBALS['language']->category['sort_date'];

        if (!$GLOBALS['session']->get('hide_prices')) {
            $sorters['price'] = $GLOBALS['language']->common['price'];
        }
        if ($GLOBALS['config']->get('config', 'stock_level')) {
            $sorters['stock_level'] = $GLOBALS['language']->category['sort_stock'];
        }
        
        foreach ($GLOBALS['hooks']->load('class.catalogue.product_sort') as $hook) {
            include $hook;
        }

        $directions = array(
            'DESC' => $GLOBALS['language']->category['sort_high_low'],
            'ASC' => $GLOBALS['language']->category['sort_low_high'],
        );
        foreach ($sorters as $field => $name) {
            foreach ($directions as $order => $direction) {
                $direction = (isset($GLOBALS['language']->category[strtolower('sort_'.$field.'_'.$order)])) ? $GLOBALS['language']->category[strtolower('sort_'.$field.'_'.$order)] : $direction;
                $assign = array('name' => $name, 'field' => $field, 'order' => $order, 'direction' => $direction);

                if ((isset($_GET['sort'][$field]) && strtoupper($_GET['sort'][$field]) == $order) || (!isset($_GET['sort']) && $field == $default['field'] && $order == $default['sort'])) {
                    $assign['selected'] = 'selected="selected"';
                } else {
                    $assign['selected'] = '';
                }

                $data[] = $assign;
            }
        }
        return $data;
    }

    /**
     * Display subcategories on category list
     *
     * @param int $category_id
     * @return array/false
     */
    public function displaySubCategory($category_id)
    {
        if (!empty($category_id) && is_numeric($category_id)) {
            if (($subcats = $GLOBALS['db']->select('CubeCart_category', false, array('cat_parent_id' => $category_id, 'status' => '1', 'hide' => '0'), array('priority'=>'ASC'))) !== false) {
                foreach ($subcats as $cat) {
                    // Translate
                    $GLOBALS['language']->translateCategory($cat);
                    $products = $this->productCount($cat['cat_id']);
                    
                    if ($products || $GLOBALS['config']->get('config', 'catalogue_show_empty') == '1') {
                        $cat['cat_image'] = $this->imagePath($cat['cat_image'], 'subcategory', 'url');
                        $cat['url'] = $GLOBALS['seo']->buildURL('cat', $cat['cat_id'], '&');
                        $cat['products_number'] = $products;
                        $return[] = $cat;
                    }
                }
                return $return;
            }
        }
        return false;
    }

    /**
     * Convert parameter to int
     *
     * @param undefined $input
     * @return int
     */
    public function get_int($input)
    {
        return (int)$input;
    }

    /**
     * Convert array values to int
     *
     * @param array $input
     * @return array
     */
    public function get_int_array($inputArray)
    {
        return array_map(array(&$this, 'get_int'), $inputArray);
    }

    /**
     * Get specific category data
     *
     * @param int $category_id
     * @return array/false
     */
    public function getCategoryData($category_id)
    {
        if (($result = $GLOBALS['db']->select('CubeCart_category', false, array('cat_id' => $category_id, 'status' => 1))) !== false) {
            $GLOBALS['language']->translateCategory($result[0]);
            $this->_categoryData = $result[0];
            return $this->_categoryData;
        }

        return false;
    }

    /**
     * Get products of specific category
     *
     * @param int $category_id
     * @param int $page
     * @param int $per_page
     * @param bool $hidden
     * @return array/false
     */
    public function getCategoryProducts($category_id, $page = 1, $per_page = 10, $hidden = false)
    {
        if (strtolower($page) == 'all') {
            $per_page = false;
            $page  = false;
        }

        $where2 = $this->outOfStockWhere(false, 'INV', true);

        if (($result = $GLOBALS['db']->query('SELECT I.product_id FROM `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_category_index` as I,  `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_inventory` as INV WHERE I.cat_id = '.$category_id.' AND I.product_id = INV.product_id AND INV.status = 1'.$where2)) !== false) {
            $this->_category_count = $GLOBALS['db']->numrows();
            if (isset($_GET['sort']) && is_array($_GET['sort'])) {
                foreach ($_GET['sort'] as $field => $direction) {
                    $order[$field] = (strtolower($direction) == 'asc') ? 'ASC' : 'DESC';
                    break;
                }
            } else {
                $order_column = $GLOBALS['config']->get('config', 'product_sort_column');
                $order_direction = $GLOBALS['config']->get('config', 'product_sort_direction');
                $order[$order_column] = $order_direction;
            }
            foreach ($result as $product) {
                $list[] = $product['product_id'];
            }
            foreach ($GLOBALS['hooks']->load('class.catalogue.category_product_list') as $hook) {
                include $hook;
            }
            $productList = $this->getProductData($list, 1, $order, $per_page, $page, true);
        }
        foreach ($GLOBALS['hooks']->load('class.catalogue.category_product_list_return') as $hook) {
            include $hook;
        }
        return (isset($productList) && is_array($productList)) ? $productList : false;
    }


    /**
     * Get status of category from product ID
     *
     * @param int $product_id
     * @return array
     */
    public function getCategoryStatusByProductID($product_id)
    {
        if (is_numeric($product_id) && $product_id>0) {
            if (empty($this->_category_status_prod_id[$product_id])) {
                $query = sprintf("SELECT CI.* , C.status FROM `%1\$sCubeCart_category_index` AS CI, `%1\$sCubeCart_category` AS C WHERE CI.product_id = '$product_id' AND CI.cat_id = C.cat_id ORDER BY CI.primary DESC", $GLOBALS['config']->get('config', 'dbprefix'));
                if (($data = $GLOBALS['db']->query($query)) !== false) {
                    foreach ($data as $cat_data) {
                        $this->_category_status_prod_id[$cat_data['product_id']][] = $cat_data;
                    }
                }
            }
            if (isset($this->_category_status_prod_id[$product_id])) {
                return $this->_category_status_prod_id[$product_id];
            }
            return array();
        }
        return array();
    }

    /**
     * Get tree of categories & subcategories for navigation
     *
     * @param int $parent_id
     * @return array/false
     */
    public function getCategoryTree($parent_id = 0, $level = 0)
    {
        $level++;
        if (($categories = $GLOBALS['db']->select('CubeCart_category', array('cat_parent_id', 'cat_id', 'cat_name'), array('cat_parent_id' => $parent_id, 'status' => 1, 'hide' => 0), 'priority, cat_name ASC')) !== false) {

            // Write over with translations
            if (!$this->_category_translations && ($translations = $GLOBALS['db']->select('CubeCart_category_language', array('cat_id', 'cat_name'), array('language' => $GLOBALS['language']->current()))) !== false) {
                foreach ($translations as $translation) {
                    $this->_category_translations[$translation['cat_id']] = $translation['cat_name'];
                }
            }

            foreach ($categories as $category) {
                $sql = 'SELECT C.`product_id`, I.`use_stock_level` FROM `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_category_index` AS C INNER JOIN `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_inventory` AS I ON I.`product_id` = C.`product_id` WHERE C.cat_id = '.$category['cat_id'].' AND I.status = 1';
                $available_products = $GLOBALS['db']->misc($sql);

                if ($available_products && $GLOBALS['config']->get('config', 'hide_out_of_stock')) {
                    
                    // Hide products out of stock
                    $in_stock = array();
                    foreach ($available_products as $key => $product) {
                        if ($product['use_stock_level']=='1') {
                            if ($options = $GLOBALS['db']->select('CubeCart_option_matrix', array('stock_level', 'use_stock'), array('product_id' => $product['product_id'], 'status' => 1), false, false, false, false)) {
                                $oos_combos = array();
                                foreach ($options as $option) {
                                    if ($option['use_stock']==1 && $option['stock_level']<=0) {
                                        $oos_combos[] = true;
                                    }
                                }
                                // If ALL matrix options are out of stock and all use stock levels
                                if (count($options)==count($oos_combos)) {
                                    unset($available_products[$key]);
                                } else {
                                    $in_stock[] = $product['product_id'];
                                }
                            }
                        }
                    }
                    
                    // Check stock at main level
                    $product_dataset = $GLOBALS['db']->misc($sql.' AND I.use_stock_level = 1 AND I.stock_level <= 0');

                    if ($product_dataset) {
                        foreach ($product_dataset as $key => $product) {
                            if (!in_array($product['product_id'], $in_stock)) {
                                foreach ($available_products as $master_key => $master_product) {
                                    if ($master_product['product_id']==$product['product_id']) {
                                        unset($available_products[$master_key]);
                                    }
                                }
                            }
                        }
                    }
                }

                $products = $available_products ? count($available_products) : 0;

                $children = $GLOBALS['db']->count('CubeCart_category', 'cat_id', array('cat_parent_id' => $category['cat_id'], 'status' => '1'));
                if (($products> 0 || $GLOBALS['config']->get('config', 'catalogue_show_empty')) || $children) {
                    $result = array(
                        'name'  => (isset($this->_category_translations[$category['cat_id']]) && !empty($this->_category_translations[$category['cat_id']])) ? $this->_category_translations[$category['cat_id']] : $category['cat_name'],
                        'cat_id' => $category['cat_id'],
                        'cat_level' => $level
                    );
                    if ($GLOBALS['config']->get('config', 'catalogue_expand_tree') && $children = $this->getCategoryTree($category['cat_id'], $level)) {
                        $result['children'] = $children;
                    }
                    $tree_data[] = $result;
                }
            }
        }

        return (isset($tree_data)) ? $tree_data : false;
    }

    /**
     * Get specific manufacturer data
     *
     * @param int $manufacturer_id
     * @return string/false
     */
    public function getManufacturer($manufacturer_id)
    {
        if (($manufacturers = $GLOBALS['db']->select('CubeCart_manufacturers', array('name', 'URL'), array('id' => $manufacturer_id))) !== false) {
            if (filter_var($manufacturers[0]['URL'], FILTER_VALIDATE_URL)) {
                return '<a href="'.$manufacturers[0]['URL'].'" target="_blank">'.$manufacturers[0]['name'].'</a>';
            } else {
                return $manufacturers[0]['name'];
            }
        } else {
            return false;
        }
    }

    /**
     * Get specific product option data
     *
     * @param int $option_id
     * @param int $assign_id
     * @return array/false
     */
    public function getOptionData($option_id, $assign_id)
    {
        if (($category = $GLOBALS['db']->select('CubeCart_option_group', false, array('option_id' => (int)$option_id))) !== false) {
            // Is it assigned, or was it from an option set?
            if (is_int($assign_id) && $assign_id < 0) {
                // Option Set
                if (($value = $GLOBALS['db']->select('CubeCart_option_value', false, array('value_id' => abs($assign_id)))) !== false) {
                    return array_merge($category[0], $value[0]);
                }
            } else {
                $assigned = $GLOBALS['db']->select('CubeCart_option_assign', false, array('assign_id' => (int)$assign_id));

                foreach ($GLOBALS['hooks']->load('class.catalogue.option_data') as $hook) {
                    include $hook;
                }
                
                if ($assigned) {
                    if ($GLOBALS['config']->get('config', 'catalogue_sale_mode') == 2 && $GLOBALS['config']->get('config', 'catalogue_sale_percentage')>0) {
                        $assigned[0]['option_price'] = $assigned[0]['option_price'] - ($assigned[0]['option_price'] / 100) * $GLOBALS['config']->get('config', 'catalogue_sale_percentage');
                    }
                }
                
                if (in_array($category[0]['option_type'], $this->_options_selectable)) {
                    // Select
                    if (($value = $GLOBALS['db']->select('CubeCart_option_value', false, array('option_id' => $category[0]['option_id'], 'value_id' => $assigned[0]['value_id']))) !== false) {
                        return array_merge($category[0], $assigned[0], $value[0]);
                    }
                } else {
                    // Text
                    if (isset($assigned[0])) {
                        return array_merge($category[0], $assigned[0]);
                    } else {
                        return $category[0];
                    }
                }
            }
        }
        return false;
    }

    /**
     * Get product option price
     *
     * @return float
     */
    public function getOptionsLinePrice()
    {
        return (float)$this->_options_line_price;
    }

    /**
     * See if product option is required
     *
     * @return bool/array
     */
    public function getOptionRequired()
    {
        // If there is only one value OR a default value for every option assign them
        if (isset($_POST['add'])) {
            $add_options = true;
            $single_fixed_options = false;

            $assigned_options = $GLOBALS['db']->select('CubeCart_option_assign', false, array('product' => (int)$_POST['add']));

            if ($assigned_options) {
                $single_fixed_options = true;
                $forced_options = array();
                $default_options = array();
                // First find any default option values
                foreach ($assigned_options as $assigned_option) {
                    // If the store owner set multiple defaults for the same option, only the last one will be used
                    if (!empty($assigned_option['option_default'])) {
                        $default_options[$assigned_option['option_id']] = $assigned_option;
                    }
                }
                foreach ($assigned_options as $assigned_option) {
                    // Always use the default option value if it exists
                    if (isset($default_options[$assigned_option['option_id']])) {
                        $assigned_option = $default_options[$assigned_option['option_id']];
                    }
                    if (isset($forced_options[$assigned_option['option_id']])) {
                        if (empty($default_options[$assigned_option['option_id']])) {
                            $single_fixed_options = false;
                            break;
                        } else {
                            continue; // this option was already handled
                        }
                    }

                    $forced_options[$assigned_option['option_id']] = $assigned_option['assign_id'];

                    $group = $GLOBALS['db']->select('CubeCart_option_group', array('option_type', 'option_required'), array('option_id' => $assigned_option['option_id']));
                
                    if ($group[0]['option_required']=="0") {
                        $single_fixed_options = false;
                        break;
                    }

                    if ($group && in_array($group[0]['option_type'], array(1, 2))) {
                        $single_fixed_options = false;
                        break;
                    }
                }
            }
            if ($single_fixed_options && is_array($forced_options) && count($forced_options)>0) {
                return $forced_options;
            }
        }

        return $this->_option_required;
    }

    /**
     * Get product data
     *
     * @param int $product_id
     * @param int $quantity
     * @param bool $order
     * @param int $per_page
     * @param int $page
     * @param bool $category
     * @param string $options_identifier
     * @param int $assign_id
     * @return array/false
     */
    public function getProductData($product_id, $quantity = 1, $order = false, $per_page = 10, $page = 1, $category = false, $options_identifier = null)
    {
        if (!is_array($product_id)) {
            $category_data = $this->getCategoryStatusByProductID($product_id);
            $category_status = false;
            if (is_array($category_data)) {
                foreach ($category_data as $trash => $data) {
                    if ($data['status'] == 1) {
                        $category_status = true;
                    }
                }
            }
            if (!$category_status) {
                return false;
            }
        }

        $where = $this->outOfStockWhere(array('product_id' => $product_id, 'status' => 1));

        if (is_array($order) && isset($order['price']) && $GLOBALS['config']->get('config', 'catalogue_sale_mode')) {
            if (!empty($page) && is_numeric($page)) {
                $query = 'SELECT *, IF(`sale_price` > 0, `sale_price`, `price`) AS price_sort FROM '.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_inventory WHERE '.$where.' ORDER BY `price_sort` '.$order['price'].' LIMIT '.$per_page.' OFFSET '.(int)($page-1)*$per_page;
            } else {
                $query = 'SELECT *, IF(`sale_price` > 0, `sale_price`, `price`) AS price_sort FROM '.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_inventory WHERE '.$where.' ORDER BY `price_sort` '.$order['price'];
            }
            $result = $GLOBALS['db']->query($query);
        } else {
            $result = $GLOBALS['db']->select('CubeCart_inventory', false, $where, $order, $per_page, $page);
        }

        // Get product option specific data
        $products_matrix_data = $GLOBALS['db']->select('CubeCart_option_matrix', array('stock_level' , 'product_code', 'upc', 'jan', 'isbn', 'image'), array('product_id' => (int)$product_id, 'options_identifier' => $options_identifier, 'status' => 1), false, false, false, false);
        if ($products_matrix_data) {
            foreach ($products_matrix_data[0] as $key => $value) {
                if (!is_null($value) && !empty($value)) {
                    $result[0][$key] = $value;
                }
            }
        }
        if ($result !== false) {
            $count = count($result);
            $data = array();
            foreach ($result as $product) {
                $product['product_weight'] = (float)$product['product_weight'];
                $GLOBALS['language']->translateProduct($product);
                $this->getProductPrice($product, $quantity);
                if (!$category && $count == 1) {
                    $data = $product;
                    break;
                } else {
                    $data[$product['product_id']] = $product;
                }
            }
            foreach ($GLOBALS['hooks']->load('class.catalogue.product_data') as $hook) {
                include $hook;
            }
            return $data;
        }

        return false;
    }
    /**
     * Get product hash identifier
     *
     * @param int $product_id
     * @param int $id
     * @return string/false
     */
    public function getProductHash($product_id, $id)
    {
        $inventory = $GLOBALS['db']->select('CubeCart_inventory', false, array('product_id' => $product_id));
        
        if ($inventory == false) {
            return false;
        }

        $data = array(
            $inventory,
            $GLOBALS['db']->select('CubeCart_category_index', array('cat_id','primary'), array('product_id' => $product_id)),
            $GLOBALS['db']->select('CubeCart_option_assign', false, array('product' => $product_id)),
            $GLOBALS['db']->select('CubeCart_option_matrix', false, array('product_id' => $product_id)),
            $GLOBALS['db']->select('CubeCart_reviews', false, array('product_id' => $product_id)),
            $GLOBALS['db']->select('CubeCart_image_index', false, array('product_id' => $product_id)),
            $GLOBALS['db']->select('CubeCart_pricing_group', false, array('product_id' => $product_id)),
            $GLOBALS['db']->select('CubeCart_pricing_quantity', false, array('product_id' => $product_id)),
            $GLOBALS['db']->select('CubeCart_inventory_language', false, array('product_id' => $product_id)),
            $GLOBALS['db']->select('CubeCart_options_set_product', false, array('product_id' => $product_id)),
            $GLOBALS['db']->select('CubeCart_seo_urls', false, array('type' => 'prod', 'item_id' => $product_id))
        );
        return $this->_productHash[$id] = md5(serialize($data));
    }

    /**
     * Get options for specific product
     *
     * @param int $product_id
     * @return array/false
     */
    public function getProductOptions($product_id = null)
    {
        $sale_percent = ($GLOBALS['config']->get('config', 'catalogue_sale_mode') == 2 && $GLOBALS['config']->get('config', 'catalogue_sale_percentage')>0) ? $GLOBALS['config']->get('config', 'catalogue_sale_percentage') : false;

        if (($setlist = $GLOBALS['db']->select('CubeCart_options_set_product', array('set_id'), array('product_id' => (int)$product_id))) !== false) {
            // Fetch Option Sets
            foreach ($setlist as $set_data) {
                if (($sets = $GLOBALS['db']->select('CubeCart_options_set_member', false, array('set_id' => (int)$set_data['set_id']))) !== false) {
                    foreach ($sets as $set) {
                        $set_members[] = $set['set_member_id'];
                        $set_groups[] = $set['option_id'];
                        $set_values[$set['option_id']][] = $set['value_id'];
                    }
                    if (($groups = $GLOBALS['db']->select('CubeCart_option_group', false, array('option_id' => $set_groups), array('priority' => 'ASC', 'option_name' => 'ASC'))) !== false) {
                        foreach ($groups as $group) {
                            if ($group['option_required']) {
                                $this->_option_required = true;
                            }
                            if ($group['option_type'] == 0 || $group['option_type'] == 4) {
                                if (isset($set_values[$group['option_id']]) && !empty($set_values[$group['option_id']])) {
                                    $value_id = $set_values[$group['option_id']];
                                }
                                if (is_array($value_id) && ($values = $GLOBALS['db']->select('CubeCart_option_value', false, array('value_id' => $value_id), array('priority' => 'ASC', 'value_name' => 'ASC'))) !== false) {
                                    foreach ($values as $value) {
                                        if (($assigns = $GLOBALS['db']->select('CubeCart_option_assign', false, array('value_id' => $value['value_id'], 'option_id' => $value['option_id'], 'product' => (int)$product_id, 'set_member_id' => $set_members))) !== false) {
                                            foreach ($assigns as $assign) {
                                                if (!$assign['set_enabled']) {
                                                    continue;
                                                }
                                                if ($sale_percent) {
                                                    $assign['option_price'] = $assign['option_price'] - ($assign['option_price'] / 100) * $sale_percent;
                                                }
                                                $option_array[$group['option_type']][$value['option_id']][] = array_merge($group, $value, $assign);
                                            }
                                        } else {
                                            ## Unassigned, default option from set
                                            $option_array[$group['option_type']][$value['option_id']][] = array_merge($group, $value, array('assign_id' => (int)($value['value_id']*(-1))));
                                        }
                                    }
                                }
                            } else {
                                // Text option
                                if (($assigns = $GLOBALS['db']->select('CubeCart_option_assign', false, array('option_id' => $group['option_id'], 'product' => (int)$product_id))) !== false) {
                                    if ($sale_percent) {
                                        $assigns[0]['option_price'] = $assigns[0]['option_price'] - ($assigns[0]['option_price'] / 100) * $sale_percent;
                                    }
                                    $assign = $assigns[0];
                                } else {
                                    $assign = array();
                                }
                                $assign['assign_id'] = $product_id.$set_data['set_id'].$group['option_id'];
                                $option_array[$group['option_type']][$group['option_id']][] = array_merge($group, $assign);
                            }
                            $option_array[$group['option_type']][$group['option_id']]['priority'] = $group['priority'];
                            unset($group);
                        }
                    }
                    unset($set_members, $set_groups, $set_values);
                }
            }
        }

        if (($products = $GLOBALS['db']->select('CubeCart_option_assign', false, array('product' => (int)$product_id, 'set_member_id' => 0, 'set_enabled' => '1'))) !== false) {
            $option = array();

            $sale_percent = ($GLOBALS['config']->get('config', 'catalogue_sale_mode') == 2 && $GLOBALS['config']->get('config', 'catalogue_sale_percentage')>0) ? $GLOBALS['config']->get('config', 'catalogue_sale_percentage') : false;

            foreach ($products as $assigned) {
                if ($sale_percent) {
                    $assigned['option_price'] = $assigned['option_price'] - ($assigned['option_price'] / 100) * $sale_percent;
                }

                if ($assigned['option_id'] > 0) {
                    $option[$assigned['option_id']][] = $assigned;
                    $top[] = $assigned['option_id'];
                    $mid[] = $assigned['value_id'];
                }
            }
            if (($categories = $GLOBALS['db']->select('CubeCart_option_group', false, array('option_id' => $top), array('priority' => 'ASC', 'option_name' => 'ASC'))) !== false) {
                foreach ($categories as $category) {
                    $array = false;
                    if ($category['option_required']) {
                        $this->_option_required = true;
                    }
                    if ($category['option_type'] == 0 || $category['option_type'] == 4) {
                        // Get Option Values
                        if (($values = $GLOBALS['db']->select('CubeCart_option_value', false, array('option_id' => $category['option_id'], 'value_id' => $mid), array('priority' => 'ASC', 'value_name' => 'ASC'))) !== false) {
                            foreach ($values as $value) {
                                foreach ($option[$value['option_id']] as $opt) {
                                    if ($opt['value_id'] == $value['value_id']) {
                                        $option_array[$category['option_type']][$category['option_id']][] = array_merge($category, $value, $opt);
                                    }
                                }
                            }
                        }
                    } else {
                        // Text Options
                        foreach ($option[$category['option_id']] as $opt) {
                            $option_array[$category['option_type']][$category['option_id']][] = array_merge($category, $opt);
                            break;
                        }
                    }
                    $option_array[$category['option_type']][$category['option_id']]['priority'] = $category['priority'];
                }
            }
        }
        // Sort option values
        if (isset($option_array) && is_array($option_array)) {
            foreach ($option_array as $type => $option_list) {
                if (is_array($option_list)) {
                    foreach ($option_list as $oid => $array) {
                        uasort($array, 'cmpmc');
                        $option_array[$type][$oid] = $array;
                    }
                }
            }
        }


        if (isset($option_array) && is_array($option_array)) {
            foreach ($GLOBALS['hooks']->load('class.catalogue.product_options') as $hook) {
                include $hook;
            }
            return $option_array;
        }

        return false;
    }

    /**
     * Get product price
     *
     * @param array $product_data
     * @return array/false
     */
    public function getProductPrice(&$product_data, $quantity = 1, $retail_only = false)
    {
        if (isset($product_data['product_id']) && is_numeric($product_data['product_id'])) {
            $product_id = (int)$product_data['product_id'];
            $group_id = 0;

            // Check for group pricing
            if ($retail_only === false) {
                if (isset($GLOBALS['user']) && $GLOBALS['user']->is() && ($memberships = $GLOBALS['user']->getMemberships()) !== false) {
                    $group_id = array();
                    foreach ($memberships as $membership) {
                        $group_id[] = $membership['group_id'];
                    }
                    if (($pricing_group = $GLOBALS['db']->select('CubeCart_pricing_group', false, array('product_id' => $product_id, 'group_id' => $group_id), array('price' => 'ASC'), 1)) !== false) {
                        $product_data['price'] = $pricing_group[0]['price'];
                        $product_data['sale_price'] = $pricing_group[0]['sale_price'];
                        $product_data['tax_inclusive'] = $pricing_group[0]['tax_inclusive']; # do not rely on retail price setting!
                        $product_data['tax_type'] = $pricing_group[0]['tax_type'];
                    }
                }
            }

            //Are we in sale mode?
            $sale = false;
            $product_data['ctrl_sale'] = false;
            $product_data['price_to_pay'] = $product_data['price'];
            $product_data['full_base_price'] = $product_data['price'];

            switch ((int)$GLOBALS['config']->get('config', 'catalogue_sale_mode')) {
            case 0:
                break;
            case 1:
                if ($product_data['sale_price'] && ($product_data['sale_price'] > 0 && $product_data['sale_price'] != Tax::getInstance()->priceFormatHidden())) {
                    $product_data['price_to_pay'] = $product_data['sale_price'];
                    $product_data['ctrl_sale'] = true;
                }
                $sale = true;
                break;
            case 2:
                if (!$GLOBALS['config']->isEmpty('config', 'catalogue_sale_percentage')) {
                    $product_price = $product_data['price'];
                    //Make sure the first character is a digit
                    $product_price = preg_replace('/[^0-9.]*/', '', $product_price);

                    $product_data['sale_price'] = $product_price - ($product_price / 100) * $GLOBALS['config']->get('config', 'catalogue_sale_percentage');

                    $product_data['ctrl_sale'] = ($product_data['sale_price'] > 0 && $product_data['sale_price'] != Tax::getInstance()->priceFormatHidden()) ? true : false;
                    $product_data['price_to_pay'] = $product_data['sale_price'];
                    $sale = true;
                }
                break;
            }


            $search = array('product_id' => $product_id, 'group_id' => $group_id);

            if (($pricing = $GLOBALS['db']->select('CubeCart_pricing_quantity', array('quantity', 'price'), $search, array('quantity' => 'ASC', 'price' => 'ASC'))) !== false) {
                foreach ($pricing as $price) {
                    $prices[$price['quantity']] = ($GLOBALS['config']->get('config', 'catalogue_sale_mode')==2) ? ($price['price'] - ($price['price'] / 100) * $GLOBALS['config']->get('config', 'catalogue_sale_percentage')) : $price['price'];
                }
                krsort($prices);
                // Ok so we need to get quantity for other items with same product ID for quantity discounts.
                // e.g. 1 x Blue Widget + 2 x Red Widget
                $original_quantity = $quantity;
                if (is_array($GLOBALS['cart']->basket['contents'])) {
                    $quantity = 0;
                    foreach ($GLOBALS['cart']->basket['contents'] as $hash => $item) {
                        if ($item['id']==$product_id) {
                            $quantity += $item['quantity'];
                        }
                    }
                }
                $quantity = ($quantity==0) ? $original_quantity : $quantity;

                foreach ($prices as $quant => $price) {
                    if ($quant > $quantity) {
                        continue;
                    } else {
                        //If the sale price is still better than the quantity price use the sale price
                        if (!$sale || ((double)$product_data['sale_price'] == 0) || ($sale && $product_data['sale_price'] > $price)) {
                            $product_data['price'] = $price;
                            $product_data['sale_price'] = $price;
                            $product_data['price_to_pay'] = $price;
                        }
                        break;
                    }
                }
            }

            foreach ($GLOBALS['hooks']->load('class.cubecart.product_price') as $hook) {
                include $hook;
            }

            if ($sale && $product_data['sale_price'] >= $product_data['price']) {
                $product_data['ctrl_sale'] = false;
            }
            return $product_data;
        }

        return false;
    }

    /**
     * Check product stock level
     *
     * @param array $product_id
     * @param string $options_identifier_string
     * @param bool $return_max
     * @return array/false
     */
    public function getProductStock($product_id = null, $options_identifier_string = null, $return_max = false, $check_existing = false, $quantity = false)
    {
        // Choose option combination specific stock
        if (is_numeric($product_id) && (!empty($options_identifier_string) || $return_max == true)) {
            if ($return_max) {
                $rows = 'MAX(stock_level) AS `stock_level`';
                $where = array('product_id' => (int)$product_id, 'status' => 1, 'use_stock' => 1);
            } else {
                $rows = array('stock_level', 'restock_note');
                $where = array('product_id' => (int)$product_id, 'options_identifier' => $options_identifier_string, 'status' => 1, 'use_stock' => 1);
            }
            $products_matrix = $GLOBALS['db']->select('CubeCart_option_matrix', $rows, $where, false, 1, false, false);
            if (is_numeric($products_matrix[0]['stock_level'])) {
                if (!empty($products_matrix[0]['restock_note'])) {
                    $GLOBALS['session']->set('restock_note', $products_matrix[0]['restock_note']);
                }
                return $products_matrix[0]['stock_level'];
            }
        }

        // Fall back to traditional stock check if there are no results for the combination or it is not used
        if (is_numeric($product_id) && ($products = $GLOBALS['db']->select('CubeCart_inventory', array('stock_level'), array('product_id' => (int)$product_id), false, 1, false, false)) !== false) {
            
            // Check this product id isn't already in the cart with different options identifier
            if ($check_existing) {
                if (is_array($check_existing)) {
                    foreach ($check_existing as $key => $value) {
                        if ($value['id'] == $product_id) {
                            $products[0]['stock_level'] -= 	$quantity;
                        }
                    }
                }
            }

            return $products[0]['stock_level'];
        }

        return false;
    }

    /**
     * Get image path
     *
     * @param int/string $input
     * @param string $mode
     * @param string $path
     * @param bool $return_placeholder
     * @return string
     */
    public function imagePath($input, $mode = 'medium', $path = 'relative', $return_placeholder = true)
    {
        foreach ($GLOBALS['hooks']->load('class.catalogue.imagepath') as $hook) {
            include $hook;
        }
        
        $defaults = true;
        if (is_numeric($input)) {
            if (($result = $GLOBALS['db']->select('CubeCart_filemanager', false, array('file_id' => (int)$input))) !== false) {
                $file  = $result[0]['filepath'].$result[0]['filename'];
                $defaults = false;
            } else {
                $return_placeholder = true;
            }
        } elseif (!empty($input)) {
            $file  = str_replace(array('images/cache/', 'images/uploads/'), '', $input);
            $defaults = false;
        }

        $skins = $GLOBALS['gui']->getSkinData();
        // Fetch a default image, just in case...
        if (is_array($mode)) {
            foreach ($mode as $mode_name) {
                if (isset($skins['images'][$mode_name])) {
                    $mode = $mode_name;
                    break;
                }
            }
        }

        if ($return_placeholder && isset($skins['images'][$mode])) {
            $default = (string)$skins['images'][$mode]['default'];
            
            if (isset($skins['styles'][$GLOBALS['gui']->getStyle()]['images']) && !empty($skins['styles'][$GLOBALS['gui']->getStyle()]['images'])) { // do we use a separate style folder for images?
                $files = glob('skins/'.$GLOBALS['gui']->getSkin().'/'.'images/{common,'.$GLOBALS['gui']->getStyle().'}/'.$default, GLOB_BRACE);
            } else {
                $files = glob('skins/'.$GLOBALS['gui']->getSkin().'/'.'images/'.$default, GLOB_BRACE);
            }
            if ($files && !empty($files[0])) {
                $placeholder_image = $files[0];
            }
        }

        if (isset($file) && !empty($file) && !preg_match('/^skins\//', $file) && file_exists(CC_ROOT_DIR.'/images/source/'.$file)) {
            $source = CC_ROOT_DIR.'/images/source/'.$file;
        } else {
            $source = CC_ROOT_DIR.'/'.$placeholder_image;
            $file = $placeholder_image;
        }

        if (!is_dir($source) && file_exists($source)) {
            if ($mode == 'source') {
                $folder  = 'source';
                $filename = $file;
            } else {
                $folder = 'cache';
                if (isset($skins['images'][$mode])) {
                    $data = $skins['images'][$mode];
                    preg_match('#(.*)(\.\w+)$#', $file, $match);
                    $size  = (int)$data['maximum'];
                    $filename = sprintf('%s.%d%s', $match[1], $size, $match[2]);
                    ## Find the source
                    $image  = CC_ROOT_DIR.'/images/'.$folder.'/'.$filename;
                    
                    if (!file_exists($image)) {
                        ## Check if the target folder exists - if not, create it!
                        if (!file_exists(dirname($image))) {
                            mkdir(dirname($image), chmod_writable(), true);
                        }
                        ## Generate the image
                        $gd  = new GD(dirname($image), $size, (int)$data['quality']);
                        if (!$gd->gdLoadFile($source)) {
                            $GLOBALS['gui']->setError(sprintf($GLOBALS['language']->catalogue['gd_memory_error'], $file), true);
                            // Return source instead
                            return $this->imagePath($input, 'source', $path, $return_placeholder);
                        }
                        $gd->gdSave(basename($image));
                    }
                } else {
                    trigger_error('No image mode set', E_USER_NOTICE);
                    return false;
                }
            }
            ## Generate the required path
            switch (strtolower($path)) {
                case 'filename': ## Calculate the from source folder
                    $img = $filename;
                    break;
                case 'root':  ## Calculate the absolute filesystem path
                    $img = CC_ROOT_DIR.'/images/'.$folder.'/'.$filename;
                    break;
                case 'url':   ## Calculate the absolute url
                    $img = $GLOBALS['storeURL'].'/images/'.$folder.'/'.$filename;
                    break;
                case 'rel':
                case 'relative': ## Calculate the relative web path
                    $img = $GLOBALS['rootRel'].'images/'.$folder.'/'.$filename;
                    break;
                default:
                    trigger_error('No image path set', E_USER_NOTICE);
                    return false;
            }
            return $img;
        } else {
            return '';
        }
    }

    /**
     * Work out SQL where clause
     *
     * @param bool $original
     * @param bool $label
     * @param bool $force
     * @return string
     */
    public function outOfStockWhere($original = false, $label = false, $force = false)
    {
        $def = $original ? str_replace('WHERE ', '', $GLOBALS['db']->where('CubeCart_inventory', $original, $label)) : '';
        $def .= $this->_where_live_from;

        if ($GLOBALS['config']->get('config', 'hide_out_of_stock') && !Admin::getInstance()->is()) {
            $def .= ($force || $def) ? ' AND' : '';
            $oos = sprintf('%1$s ((%2$s.stock_level > 0 AND %2$s.use_stock_level = 1) OR %2$s.use_stock_level = 0)', $def, ($label ? $label : sprintf('%sCubeCart_inventory', $GLOBALS['config']->get('config', 'dbprefix'))));
        }
        return ($GLOBALS['config']->get('config', 'hide_out_of_stock') && !Admin::getInstance()->is()) ? $oos : $def;
    }

    /**
     * Assign product into product display
     *
     * @param int $product
     * @param bool $product_view
     */
    public function productAssign(&$product, $product_view = true)
    {
        $product['description_short'] = $this->descriptionShort($product);

        $product['price_unformatted']  = $product['price'];
        $product['sale_price_unformatted'] = $product['sale_price'];

        $product['price']  = $GLOBALS['tax']->priceFormat($product['price']);
        $product['sale_price'] = $GLOBALS['tax']->priceFormat($product['sale_price']);

        $product['ctrl_purchase'] = ($GLOBALS['session']->get('hide_prices')) ? false : true;
        $product['out'] = false;
        if ($product['use_stock_level']) {
            // Get Stock Level
            $stock_level = $this->getProductStock($product['product_id'], null, true);
            if ((int)$stock_level <= 0) {
                // Out of Stock
                if (!$GLOBALS['config']->get('config', 'basket_out_of_stock_purchase')) {
                    // Not Allowed
                    $product['ctrl_purchase'] = false;
                    $product['out'] = true;
                }
            }
        }

        $skins = $GLOBALS['gui']->getSkinData();
        if (isset($skins['images'])) {
            $image_types = $skins['images'];
            if (!isset($image_types['source'])) {
                $image_types['source'] = array();
            }
            foreach ($image_types as $image_key => $values) {
                $product[$image_key] = $GLOBALS['gui']->getProductImage($product['product_id'], $image_key);
                if ($image_key == 'medium') {
                    if (strpos($product[$image_key], 'noimage') !== false) {
                        $product['magnify'] = false;
                    } else {
                        $product['magnify'] = true;
                    }
                }
            }
        }

        ## Calculate average review score
        if ($GLOBALS['config']->get('config', 'enable_reviews') && ($reviews = $GLOBALS['db']->select('CubeCart_reviews', array('rating'), array('product_id' => (int)$product['product_id'], 'approved' => '1'))) !== false) {
            $score = 0;
            $count = 0;
            foreach ($reviews as $review) {
                $score += $review['rating'];
                $count++;
            }
            $product['review_score'] = round($score/$count, 1);
            if (!$product_view) {
                $link = $GLOBALS['seo']->buildURL('prod', $product['product_id'], '&') . '#reviews';
            } else {
                $link = '#reviews';
            }
            $score = number_format(($score/$count), 1);
            if ($product_view) {
                $GLOBALS['smarty']->assign('LANG_REVIEW_INFO', sprintf($GLOBALS['language']->catalogue['review_info'], $score, $count, $link));
            } else {
                $product['review_info'] = sprintf($GLOBALS['language']->catalogue['review_info'], $score, $count, $link);
            }
            unset($score, $count);
        } else {
            $product['review_score'] = false;
        }

        if ($product_view) {
            // Price by quantity
            $user = (array)$GLOBALS['user']->get();
            if (($memberships = $GLOBALS['user']->getMemberships()) !== false) {
                foreach ($memberships as $membership) {
                    $group_id[] = $membership['group_id'];
                }
            } else {
                $group_id = 0;
            }
            // Limit by membership
            if (($prices = $GLOBALS['db']->select('CubeCart_pricing_quantity', false, array('product_id' => $product['product_id'], 'group_id' => $group_id), array('quantity' => 'ASC'))) !== false) {
                foreach ($prices as $price) {
                    $price['price'] = ($GLOBALS['config']->get('config', 'catalogue_sale_mode')==2) ? ($price['price'] - ($price['price'] / 100) * $GLOBALS['config']->get('config', 'catalogue_sale_percentage')) : $price['price'];
                    $price['price'] = $GLOBALS['tax']->priceFormat($price['price'], true);
                    $product['discounts'][] = $price;
                }
            }
        }
        foreach ($GLOBALS['hooks']->load('class.catalogue.productassign') as $hook) {
            include $hook;
        }
        return true;
    }

    /**
     * Count products in a category
     *
     * @param int $cat_id
     * @return int
     */
    public function productCount($cat_id, $inc_children = true)
    {
        $products = $GLOBALS['db']->select('CubeCart_category_index', array('id'), array('cat_id' => $cat_id));
        $count  = ($products) ? count($products) : 0;
        if ($inc_children) {
            $children = $GLOBALS['db']->select('CubeCart_category', array('cat_id'), array('cat_parent_id' => (int)$cat_id));
            if ($children) {
                foreach ($children as $child) {
                    $count += $this->productCount($child['cat_id']);
                }
            }
        }
        return (int)$count;
    }

    /**
     * Check two hashes match
     *
     * @param string $hash1
     * @param string $hash2
     * @return bool
     */
    public function productHashMatch($hash1, $hash2)
    {
        if ($this->_productHash[$hash1] === $this->_productHash[$hash2]) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Search product catalog
     *
     * @param string $search_data
     * @param int $page
     * @param int $per_page
     * @param string $search_mode
     * @return bool
     */
    public function searchCatalogue($search_data = null, $page = 1, $per_page = 10, $search_mode = 'fulltext')
    {
        $per_page = (!is_numeric($per_page) || $per_page < 1) ? 10 : $per_page;

        $original_search_data = $search_data;

        /*	Allow plugins to add to conditions and joins or change the search_data
            Where conditions may be added to the $where variable and must be self contained (e.g. no AND prefix or suffix) since they will be ANDed together below
            $where[] = "I.price > 100";
            Joins may be added to the $joins variable - keep in mind the need for unique table aliases as appropriate
            $joins[] = "`plugin_myPlugin` as P ON P.`product_id`=I.`product_id` AND P.`my_field`='some_value'";
            The only guaranteed table alias is I for CubeCart_inventory
            G for CubeCart_pricing_group
            CI for CubeCart_category_index
            C for CubeCart_category
        */
        $where = array();
        $joins = array();
        foreach ($GLOBALS['hooks']->load('class.catalogue.pre_search') as $hook) {
            include $hook;
        }

        $sale_mode = $GLOBALS['config']->get('config', 'catalogue_sale_mode');

        if ($sale_mode == 2) {
            $sale_percentage = $GLOBALS['config']->get('config', 'catalogue_sale_percentage');
        }
        $user = (array)$GLOBALS['user']->get();
        $group_id = 'WHERE group_id = 0';
        if (($memberships = $GLOBALS['user']->getMemberships()) !== false) {
            $group_id = 'WHERE ';
            foreach ($memberships as $membership) {
                $group_id .= 'group_id = '.$membership['group_id'].' OR ';
            }
            $group_id = substr($group_id, 0, -4);
        }

        if (strtolower($page) != 'all') {
            $page = (is_numeric($page)) ? $page : 1;
            $limit = sprintf('LIMIT %d OFFSET %d', (int)$per_page, $per_page*($page-1));
        } else {
            $limit = 'LIMIT 100';
        }

        // Presence of a join is similar to presence of a search keyword
        if (!empty($joins) || is_array($search_data)) {
            if (!empty($search_data['priceVary'])) {
                // Allow for a 5% variance in prices
                if (!empty($search_data['priceMin']) && is_numeric($search_data['priceMin'])) {
                    $price = round($GLOBALS['tax']->priceConvertFX($search_data['priceMin'])/1.05, 3);
                    if ($sale_mode == 1) {
                        $where[] = 'AND (IF (G.product_id IS NULL, IF (I.sale_price IS NULL OR I.sale_price = 0, I.price, I.sale_price) >= '.$price.', IF (G.sale_price IS NULL OR G.sale_price = 0, G.price, G.sale_price) >= '.$price.'))';
                    } elseif ($sale_mode == 2) {
                        $where[] = 'AND (IF (G.price IS NULL, (I.price - ((I.price / 100) * '.$sale_percentage.')) >= '.$price.', (G.price - ((G.price / 100) * '.$sale_percentage.')) >= '.$price.'))';
                    } else {
                        $where[] = 'AND (IF (G.price IS NULL, I.price >= '.$price.', G.price >= '.$price.'))';
                    }
                }

                if (!empty($search_data['priceMax']) && is_numeric($search_data['priceMax'])) {
                    $price = round($GLOBALS['tax']->priceConvertFX($search_data['priceMax'])*1.05, 3);
                    if ($sale_mode == 1) {
                        $where[] = 'AND (IF (G.product_id IS NULL, IF (I.sale_price IS NULL OR I.sale_price = 0, I.price, I.sale_price) <= '.$price.', IF (G.sale_price IS NULL OR G.sale_price = 0, G.price, G.sale_price) <= '.$price.'))';
                    } elseif ($sale_mode == 2) {
                        $where[] = 'AND (IF (G.price IS NULL, (I.price - ((I.price / 100) * '.$sale_percentage.')) <= '.$price.', (G.price - ((G.price / 100) * '.$sale_percentage.')) <= '.$price.'))';
                    } else {
                        $where[] = 'AND (IF (G.price IS NULL, I.price <= '.$price.', G.price <= '.$price.'))';
                    }
                }
            } else {
                ## Basic price searching
                if (!empty($search_data['priceMin']) && is_numeric($search_data['priceMin']) &&
                    !empty($search_data['priceMax']) && is_numeric($search_data['priceMax']) &&
                    $search_data['priceMax'] == $search_data['priceMin']) {
                    $price = round($GLOBALS['tax']->priceConvertFX($search_data['priceMin']), 3);
                    if ($sale_mode == 1) {
                        $where[] = 'AND (IF (G.product_id IS NULL, IF (I.sale_price IS NULL OR I.sale_price = 0, I.price, I.sale_price) = '.$price.', IF (G.sale_price IS NULL OR G.sale_price = 0, G.price, G.sale_price) = '.$price.'))';
                    } elseif ($sale_mode == 2) {
                        $where[] = 'AND (IF (G.price IS NULL, (I.price - ((I.price / 100) * '.$sale_percentage.')) = '.$price.', (G.price - ((G.price / 100) * '.$sale_percentage.')) = '.$price.'))';
                    } else {
                        $where[] = 'AND (IF (G.price IS NULL, I.price = '.$price.', G.price = '.$price.'))';
                    }
                } else {
                    if (!empty($search_data['priceMin']) && is_numeric($search_data['priceMin'])) {
                        $price = round($GLOBALS['tax']->priceConvertFX($search_data['priceMin']), 3);
                        if ($sale_mode == 1) {
                            $where[] = 'AND (IF (G.product_id IS NULL, IF (I.sale_price = 0, I.price, I.sale_price) >= '.$price.', IF (G.sale_price = 0, G.price, G.sale_price) >= '.$price.'))';
                        } elseif ($sale_mode == 2) {
                            $where[] = 'AND (IF (G.price IS NULL, (I.price - ((I.price / 100) * '.$sale_percentage.')) >= '.$price.', (G.price - ((G.price / 100) * '.$sale_percentage.')) >= '.$price.'))';
                        } else {
                            $where[] = 'AND (IF (G.price IS NULL, I.price >= '.$price.', G.price >= '.$price.'))';
                        }
                    }
                    if (!empty($search_data['priceMax']) && is_numeric($search_data['priceMax'])) {
                        $price = round($GLOBALS['tax']->priceConvertFX($search_data['priceMax']), 3);
                        if ($sale_mode == 1) {
                            $where[] = 'AND (IF (G.product_id IS NULL, IF (I.sale_price IS NULL OR I.sale_price = 0, I.price, I.sale_price) <= '.$price.', IF (G.sale_price IS NULL OR G.sale_price = 0, G.price, G.sale_price) <= '.$price.'))';
                        } elseif ($sale_mode == 2) {
                            $where[] = 'AND (IF (G.price IS NULL, (I.price - ((I.price / 100) * '.$sale_percentage.')) <= '.$price.', (G.price - ((G.price / 100) * '.$sale_percentage.')) <= '.$price.'))';
                        } else {
                            $where[] = 'AND (IF (G.price IS NULL, I.price <= '.$price.', G.price <= '.$price.'))';
                        }
                    }
                }
            }
            // Manufacturer
            if (isset($search_data['manufacturer']) && is_array($search_data['manufacturer']) && count($search_data['manufacturer'])>0) {
                $where[] = 'I.manufacturer IN ('.implode(',', $this->get_int_array($search_data['manufacturer'])).')';
                //    $where[] = 'I.manufacturer IN ('.implode(',', '\''.$search_data['manufacturer']).'\')';
            }

            $order = array();
            
            if (isset($_GET['sort']) && is_array($_GET['sort'])) {
                foreach ($_GET['sort'] as $field => $direction) {
                    if (strtolower($field) == 'relevance' && $search_mode !== 'fulltext') {
                        break;
                    }
                    $order['field'] = $field;
                    if ($field == 'price') {
                        if ($sale_mode == 1) {
                            $order['field'] = 'IF (G.product_id IS NULL, IF (I.sale_price IS NULL OR I.sale_price = 0, I.price, I.sale_price), IF (G.sale_price IS NULL OR G.sale_price = 0, G.price, G.sale_price))';
                        } else {
                            $order['field'] = 'IFNULL (G.price, I.price)';
                        }
                    }
                    $order['sort'] = (strtolower($direction) == 'asc') ? 'ASC' : 'DESC';
                    break;
                }
            } elseif ($search_mode == 'fulltext') {
                $order['field'] = 'Relevance';
                $order['sort'] = 'DESC';
            }
            // Use store settings for sort order if none designated
            if (empty($order)) {
                $order['field'] = $GLOBALS['config']->get('config', 'product_sort_column');
                $order['sort'] = $GLOBALS['config']->get('config', 'product_sort_direction');
                if (empty($order['field']) || empty($order['sort'])) {
                    unset($order); // store settings were somehow invalid
                }
            }
            if (empty($search_data['keywords']) && $order['field'] == 'Relevance') {
                if ($sale_mode == 1) {
                    $order['field'] = 'IF (G.product_id IS NULL, IF (I.sale_price IS NULL OR I.sale_price = 0, I.price, I.sale_price), IF (G.sale_price IS NULL OR G.sale_price = 0, G.price, G.sale_price))';
                } else {
                    $order['field'] = 'IFNULL (G.price, I.price)';
                }
            }
            if (is_array($order)) {
                $field_format = preg_match('/\s/', $order['field']) ? $order['field'] : '`'.$order['field'].'`';
                $order_string = 'ORDER BY '.$field_format.' '.$order['sort'];
            }

            if (isset($search_data['featured'])) {
                $where[] = "AND I.featured = '1'";
            }
            // Only look for items that are in stock
            if (isset($search_data['inStock'])) {
                $where[] = $this->outOfStockWhere();
            }

            if(!isset($search_data['manufacturer']) && $manufacturers  = $GLOBALS['db']->select('CubeCart_manufacturers', array('id'), "`name` LIKE '%".$search_data['keywords']."%'")) {
                $ids = array();
                foreach($manufacturers as $manufacturer) {
                    $ids[] = $manufacturer['id'];
                }
                $manufacturers = implode(',',$ids);
                $where[] = "OR `I`.`manufacturer` IN($manufacturers)";
            }

            $whereString = (isset($where) && is_array($where)) ? implode(' ', $where) : '';
            $whereString .= $this->_where_live_from;

            $joinString = (isset($joins) && is_array($joins)) ? implode(' JOIN ', $joins) : '';
            if (!empty($joinString)) {
                $joinString = ' JOIN '.$joinString;
            }

            $indexes = $GLOBALS['db']->getFulltextIndex('CubeCart_inventory', 'I');

            if (!empty($joins) || isset($search_data['keywords']) && is_array($indexes) && !empty($search_data['keywords'])) {
                if ($search_mode == 'fulltext') {
                    $max_word_len = $GLOBALS['db']->getSearchWordLen();
                    $words = explode(' ', $search_data['keywords']);
                    if (is_array($words)) {
                        $search_str_len = 0;
                        foreach ($words as $word) {
                            $search_str_len = ($search_str_len < strlen($word)) ? strlen($word) : $search_str_len;
                        }
                    } else {
                        $search_str_len = strlen($search_data['keywords']);
                    }
                }

                if ($search_mode == 'fulltext') {
                    if ($search_str_len < $max_word_len) {
                        return $this->searchCatalogue($original_search_data, $page, $per_page, 'rlike');
                    }

                    switch (true) {
                    case (preg_match('#[\+\-\>\<][\w]+#iu', $search_data['keywords'])):
                        ## Switch to bolean mode
                        $mode = 'IN BOOLEAN MODE';
                        break;
                    default:
                        $search_data['keywords'] = str_replace(' ', '*) +(*', $search_data['keywords']);
                        $search_data['keywords'] .= '*)';
                        $search_data['keywords'] = '+(*'.$search_data['keywords'];
                        $mode = 'IN BOOLEAN MODE';
                        break;
                    }
                    $words = preg_replace('/[^\p{Greek}a-zA-Z0-9\s]+/u', '', $search_data['keywords']);
                    $words = $GLOBALS['db']->sqlSafe($words);
                    // Score matching string
                    $match = sprintf("MATCH (%s) AGAINST('%s' %s)", implode(',', $indexes), $words, $mode);
                    $match_val = '0.5';

                    $query = sprintf("SELECT I.*, %2\$s AS Relevance FROM %1\$sCubeCart_inventory AS I LEFT JOIN (SELECT product_id, MAX(price) as price, MAX(sale_price) as sale_price FROM %1\$sCubeCart_pricing_group $group_id GROUP BY product_id) as G ON G.product_id = I.product_id $joinString WHERE I.product_id IN (SELECT product_id FROM `%1\$sCubeCart_category_index` as CI INNER JOIN %1\$sCubeCart_category as C where CI.cat_id = C.cat_id AND C.status = 1) AND I.status = 1 AND (%2\$s) >= %4\$s %3\$s %5\$s %6\$s", $GLOBALS['config']->get('config', 'dbprefix'), $match, $whereString, $match_val, $order_string, $limit);
                    
                    if ($search = $GLOBALS['db']->query($query)) {
                        $q2 = sprintf("SELECT COUNT(I.product_id) as count, %2\$s AS Relevance FROM %1\$sCubeCart_inventory AS I LEFT JOIN (SELECT product_id, MAX(price) as price, MAX(sale_price) as sale_price FROM %1\$sCubeCart_pricing_group $group_id GROUP BY product_id) as G ON G.product_id = I.product_id $joinString WHERE I.product_id IN (SELECT product_id FROM `%1\$sCubeCart_category_index` as CI INNER JOIN %1\$sCubeCart_category as C where CI.cat_id = C.cat_id AND C.status = 1) AND I.status = 1 AND (%2\$s) >= %4\$s %3\$s GROUP BY I.product_id %5\$s", $GLOBALS['config']->get('config', 'dbprefix'), $match, $whereString, $match_val, $order_string);
                        $count = $GLOBALS['db']->query($q2);
                        $this->_category_count  = (int)count($count);
                        $this->_category_products = $search;
                        $this->_sort_by_relevance = true;
                        if ($page == 1 && count($this->_category_products)==1 && ctype_digit($this->_category_products[0]['product_id']) && $_SERVER['HTTP_X_REQUESTED_WITH']!=='XMLHttpRequest') {
                            $GLOBALS['gui']->setNotify(sprintf($GLOBALS['language']->catalogue['notify_product_search_one'], $_REQUEST['search']['keywords']));
                            httpredir('?_a=product&product_id='.$this->_category_products[0]['product_id']);
                        }
                        return true;
                    } elseif ($search_mode == 'fulltext') {
                        return $this->searchCatalogue($original_search_data, 1, $per_page, 'rlike');
                    }
                } else {
                    $search_mode = in_array($search_mode, array('rlike','like')) ? $search_mode : 'rlike';
                    $this->_sort_by_relevance = false;
                    $like = '';
                    if (!empty($search_data['keywords'])) {
                        $searchwords = preg_split('/[\s,]+/', $GLOBALS['db']->sqlSafe($search_data['keywords']));
                        $searchArray = array();
                        foreach ($searchwords as $word) {
                            if (empty($word) && !is_numeric($word)) {
                                continue;
                            }
                            $searchArray[] = $word;
                        }

                        $noKeys = count($searchArray);
                        $regexp = $regexp_desc = '';
                        
                        $search_mode = in_array($search_mode, array('rlike','like')) ? $search_mode : 'rlike';

                        if ($search_mode == 'rlike') {
                            $like_keyword = "RLIKE";
                            $like_prefix = '[[:<:]]';
                            $like_postfix = '[[:>:]].*';
                        } else {
                            $like_keyword = "LIKE";
                            $like_prefix = '%';
                            $like_postfix = '%';
                        }
                        for ($i=0; $i<$noKeys; ++$i) {
                            $ucSearchTerm = strtoupper($searchArray[$i]);
                            if (($ucSearchTerm != 'AND') && ($ucSearchTerm != 'OR')) {
                                $regexp .= $like_prefix.$searchArray[$i].$like_postfix;
                                $regexp_desc .= $like_prefix.htmlentities(html_entity_decode($searchArray[$i], ENT_COMPAT, 'UTF-8'), ENT_QUOTES, 'UTF-8', false).$like_postfix;
                            }
                        }
                        if ($search_mode == 'rlike') {
                            $regexp = substr($regexp, 0, strlen($regexp)-2);
                            $regexp_desc = substr($regexp_desc, 0, strlen($regexp_desc)-2);
                        }
                        $like = " AND (I.name ".$like_keyword." '".$regexp."' OR I.description ".$like_keyword." '".$regexp_desc."' OR I.product_code ".$like_keyword." '".$regexp."')";
                    }

                    $q2 = "SELECT I.* FROM ".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_inventory AS I LEFT JOIN (SELECT product_id, MAX(price) as price, MAX(sale_price) as sale_price FROM ".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_pricing_group $group_id GROUP BY product_id) as G ON G.product_id = I.product_id $joinString WHERE I.product_id IN (SELECT product_id FROM `".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_category_index` as CI INNER JOIN ".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_category as C where CI.cat_id = C.cat_id AND C.status = 1) AND I.status = 1 ".$whereString.$like;
                    $query = $q2.' '.$order_string.' '.$limit;
                    $search = $GLOBALS['db']->query($query);
                    if (count($search)>0) {
                        $count = $GLOBALS['db']->query($q2);
                        $this->_category_count  = (int)count($count);
                        $this->_category_products = $search;
                        if ($page == 1 && count($this->_category_products)==1 && ctype_digit($this->_category_products[0]['product_id']) && $_SERVER['HTTP_X_REQUESTED_WITH']!=='XMLHttpRequest') {
                            $GLOBALS['gui']->setNotify(sprintf($GLOBALS['language']->catalogue['notify_product_search_one'], $_REQUEST['search']['keywords']));
                            httpredir('?_a=product&product_id='.$this->_category_products[0]['product_id']);
                        }
                        return true;
                    } elseif ($search_mode=="rlike") {
                        return $this->searchCatalogue($original_search_data, 1, $per_page, 'like');
                    }
                }
            }
        } else {
            if (is_numeric($search_data)) {
                if (($category = $this->getCategoryData((int)$search_data)) !== false) {
                    if (($products = $this->getCategoryProducts((int)$search_data, $page, $per_page)) !== false) {
                        $this->_category_products = $products;
                        return true;
                    }
                }
            } elseif (strtolower($search_data) == 'sale') {
                if (isset($_GET['sort']) && is_array($_GET['sort'])) {
                    foreach ($_GET['sort'] as $field => $direction) {
                        $order[$field] = (strtolower($direction) == 'asc') ? 'ASC' : 'DESC';
                        break;
                    }
                } else {
                    $order['price'] = 'DESC';
                }

                if (is_array($order)) {
                    if (key($order) == "price") {
                        if ($GLOBALS['config']->get('config', 'catalogue_sale_mode') == '1') {
                            $order_string = 'ORDER BY (I.price-I.sale_price) '.current($order);
                        } elseif ($GLOBALS['config']->get('config', 'catalogue_sale_mode') == '2' && $GLOBALS['config']->get('config', 'catalogue_sale_percentage'>0)) {
                            $order_string = 'ORDER BY (I.price - (I.price / 100) * '.$GLOBALS['config']->get('config', 'catalogue_sale_percentage').') '.current($order);
                        }
                        $_GET['sort']['price'] = current($order);
                    } else {
                        $_GET['sort'][key($order)] = current($order);
                        $order_string = 'ORDER BY `'.key($order).'` '.current($order);
                    }
                }
                $where2 = $this->outOfStockWhere(false, 'I', true);
                $whereString = 'IF (G.sale_price IS NULL, I.sale_price, G.sale_price) > 0'.$where2;
                if ($GLOBALS['config']->get('config', 'catalogue_sale_mode') == '1') {
                    $query = sprintf("SELECT I.* FROM %1\$sCubeCart_inventory AS I LEFT JOIN (SELECT product_id, MAX(price) as price, MAX(sale_price) as sale_price FROM %1\$sCubeCart_pricing_group $group_id GROUP BY product_id) as G ON G.product_id = I.product_id WHERE I.product_id IN (SELECT product_id FROM `%1\$sCubeCart_category_index` as CI INNER JOIN %1\$sCubeCart_category as C where CI.cat_id = C.cat_id AND C.status = 1) AND I.status = 1 AND %2\$s %3\$s %4\$s", $GLOBALS['config']->get('config', 'dbprefix'), $whereString, $order_string, $limit);
                } elseif ($GLOBALS['config']->get('config', 'catalogue_sale_mode') == '2') {
                    $decimal_percent = $GLOBALS['config']->get('config', 'catalogue_sale_percentage')/100;
                    $query = sprintf("SELECT I.* FROM %1\$sCubeCart_inventory AS I LEFT JOIN (SELECT product_id, MAX(price) as price, price*%4\$s as sale_price FROM %1\$sCubeCart_pricing_group $group_id GROUP BY product_id) as G ON G.product_id = I.product_id WHERE I.product_id IN (SELECT product_id FROM `%1\$sCubeCart_category_index` as CI INNER JOIN %1\$sCubeCart_category as C where CI.cat_id = C.cat_id AND C.status = 1) AND I.status = 1 %2\$s %3\$s", $GLOBALS['config']->get('config', 'dbprefix'), $order_string, $limit, $decimal_percent);
                } else {
                    return false;
                }
                foreach ($GLOBALS['hooks']->load('class.cubecart.search_catalogue') as $hook) {
                    include $hook;
                }
                if (($sale = $GLOBALS['db']->query($query)) !== false) {
                    $q2 = sprintf("SELECT SQL_CALC_FOUND_ROWS I.* FROM %1\$sCubeCart_inventory AS I LEFT JOIN (SELECT product_id, MAX(price) as price, MAX(sale_price) as sale_price FROM %1\$sCubeCart_pricing_group $group_id GROUP BY product_id) as G ON G.product_id = I.product_id WHERE I.product_id IN (SELECT product_id FROM `%1\$sCubeCart_category_index` as CI INNER JOIN %1\$sCubeCart_category as C where CI.cat_id = C.cat_id AND C.status = 1) AND I.status = 1 AND %2\$s %3\$s", $GLOBALS['config']->get('config', 'dbprefix'), $whereString, $order_string);
                    $count = $GLOBALS['db']->query($q2);
                    $this->_category_count  = (int)count($count);
                    $this->_category_products = $sale;
                    foreach ($GLOBALS['hooks']->load('class.catalogue.search_catalogue.sale_items.post') as $hook) {
                        include $hook;
                    }
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Set category id/name
     *
     * @param string $id
     * @param string $name
     */
    public function setCategory($id, $name)
    {
        $this->_categoryData[$id] = $name;
    }

    //=====[ Private ]=======================================

    /**
     * Translate a category if a translation exists
     *
     * @return bool
     */
    private function _categoryTranslation()
    {
        if (isset($GLOBALS['language']) && !empty($GLOBALS['language'])) {
            if (($result = $GLOBALS['db']->select('CubeCart_category_language', array('cat_name', 'cat_desc'), array('cat_id' => $this->_categoryData['cat_id'], 'language' => $GLOBALS['language']))) !== false) {
                $this->_categoryData['cat_name'] = $result[0]['cat_name'];
                $this->_categoryData['cat_desc'] = $result[0]['cat_desc'];
                return true;
            }
        }
        return false;
    }

    /**
     * Get product image gallery
     *
     * @param int $product_id
     * @return array/false
     */
    private function _productGallery($product_id = false)
    {
        if (isset($product_id) && is_numeric($product_id)) {
            $skins = $GLOBALS['gui']->getSkinData();
            if (isset($skins['images'])) {
                $image_types[] = 'source';
                foreach ($skins['images'] as $name => $values) {
                    $image_types[] = $name;
                }
            }
            $image_types[] = 'source';

            // Look for images
            if (($gallery = $GLOBALS['db']->select('`'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_image_index` AS `i` INNER JOIN `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_filemanager` AS `f` ON i.file_id = f.file_id', false, 'i.product_id = '.$product_id, 'ORDER BY i.main_img DESC'))) {
                $duplicates = array();
                foreach ($gallery as $key => $image) {
                    if (is_array($image_types) && !in_array($image['file_id'], $duplicates)) {
                        $duplicates[] = $image['file_id'];
                        foreach ($image_types as $type) {
                            $image[$type] = $this->imagePath($image['file_id'], $type);
                        }
                        $return[] = $image;
                        $json['image_'.$image['id']] = $image;
                    }
                }
                foreach ($GLOBALS['hooks']->load('class.cubecart.gallery') as $hook) {
                    include $hook;
                }
                $GLOBALS['smarty']->assign('GALLERY_JSON', json_encode($json));
                return $return;
            }
        }
        $GLOBALS['smarty']->assign('GALLERY_JSON', "''");

        return false;
    }

    /**
     * Increment product views
     *
     * @param int $product_id
     * @return bool
     */
    private function _productPopularity($product_id = false)
    {
        if ($product_id && is_numeric($product_id)) {
            $GLOBALS['db']->update('CubeCart_inventory', array('popularity' => '+1'), array('product_id' => (int)$product_id), false);
            return true;
        }
        return false;
    }
}
