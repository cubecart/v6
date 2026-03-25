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

/**
 * Products API resource controller
 */
class ApiResource_Products extends ApiResource
{
    protected $_resourceName = 'products';

    /**
     * Sortable fields
     */
    private $_sortableFields = array(
        'product_id', 'name', 'price', 'sale_price', 'stock_level',
        'date_added', 'updated', 'product_code', 'popularity', 'status',
    );

    /**
     * Fields allowed for create/update
     */
    private $_writableFields = array(
        'name', 'description', 'description_short', 'product_code', 'upc',
        'ean', 'jan', 'isbn', 'mpn', 'price', 'sale_price', 'cost_price',
        'stock_level', 'stock_warning', 'product_weight', 'tax_type',
        'tax_inclusive', 'status', 'digital', 'digital_path', 'manufacturer',
        'condition', 'seo_meta_title', 'seo_meta_description', 'seo_meta_keywords',
        'featured', 'latest', 'noindex', 'live_from', 'spec_copy',
    );

    /**
     * Fields to exclude from API output
     */
    private $_excludeFields = array('product_parse');

    /**
     * GET /products - list products
     */
    public function listing()
    {
        $this->_requireRead();
        $pagination = $this->_getPagination();

        // Build WHERE
        $where = $this->_buildFilters(array(
            'status'       => 'status',
            'cat_id'       => false, // handled separately
            'manufacturer' => 'manufacturer',
            'featured'     => 'featured',
            'digital'      => 'digital',
        ));

        // Remove false entries
        unset($where[false]);
        if (isset($_GET['status'])) {
            $where['status'] = (int)$_GET['status'];
        }
        if (isset($_GET['manufacturer'])) {
            $where['manufacturer'] = (int)$_GET['manufacturer'];
        }
        if (isset($_GET['featured'])) {
            $where['featured'] = (int)$_GET['featured'];
        }

        // Search
        $search = $this->_buildSearch(array('name', 'product_code', 'description', 'description_short'));
        $where = array_merge($where, $search);

        // Sort
        $sort = $this->_getSort($this->_sortableFields, 'product_id');

        // Category filter - requires a JOIN
        if (isset($_GET['cat_id']) && is_numeric($_GET['cat_id'])) {
            return $this->_listByCategory((int)$_GET['cat_id'], $where, $sort, $pagination);
        }

        // Count
        $total = $this->_db->count('CubeCart_inventory', 'product_id', $where);

        // Fetch
        $products = $this->_db->select('CubeCart_inventory', false, $where, $sort, $pagination['per_page'], $pagination['page'], false);

        $data = array();
        if ($products) {
            foreach ($products as $product) {
                $data[] = $this->_formatProduct($product);
            }
        }

        ApiResponse::paginated($data, $pagination['page'], $pagination['per_page'], $total);
    }

    /**
     * GET /products/{id} - get single product
     */
    public function get($id)
    {
        $this->_requireRead();

        if (!is_numeric($id)) {
            ApiResponse::error('Invalid product ID', 'BAD_REQUEST', 400);
        }

        $result = $this->_db->select('CubeCart_inventory', false, array('product_id' => (int)$id));
        if (!$result) {
            ApiResponse::error('Product not found', 'NOT_FOUND', 404);
        }

        $product = $this->_formatProduct($result[0], true);

        // Include categories
        $cats = $this->_db->select('CubeCart_category_index', false, array('product_id' => (int)$id));
        $product['categories'] = array();
        if ($cats) {
            foreach ($cats as $cat) {
                $product['categories'][] = array(
                    'cat_id'  => (int)$cat['cat_id'],
                    'primary' => (int)$cat['primary'],
                );
            }
        }

        // Include images
        $images = $this->_db->select('CubeCart_image_index', false, array('product_id' => (int)$id), array('main_img' => 'DESC'));
        $product['images'] = array();
        if ($images) {
            foreach ($images as $img) {
                $fileData = $this->_db->select('CubeCart_filemanager', false, array('file_id' => $img['file_id']));
                $product['images'][] = array(
                    'file_id'  => (int)$img['file_id'],
                    'main_img' => (int)$img['main_img'],
                    'filepath' => $fileData ? $fileData[0]['filepath'] : '',
                    'filename' => $fileData ? $fileData[0]['filename'] : '',
                );
            }
        }

        // Include options
        $options = $this->_db->select('CubeCart_option_assign', false, array('product' => (int)$id));
        $product['options'] = array();
        if ($options) {
            foreach ($options as $opt) {
                $group = $this->_db->select('CubeCart_option_group', false, array('option_id' => $opt['option_id']));
                $values = $this->_db->select('CubeCart_option_value', false, array('option_id' => $opt['option_id'], 'status' => 1), array('priority' => 'ASC'));
                $product['options'][] = array(
                    'option_id'    => (int)$opt['option_id'],
                    'option_name'  => $group ? $group[0]['option_name'] : '',
                    'option_type'  => $group ? (int)$group[0]['option_type'] : 0,
                    'option_required' => (int)$opt['option_required'],
                    'values'       => $values ?: array(),
                );
            }
        }

        ApiResponse::success($this->_applySparseFields($product));
    }

    /**
     * POST /products - create product
     */
    public function create()
    {
        $this->_requireWrite();
        $data = $this->_getRequestBody();
        $this->_validateRequired($data, array('name', 'price'));

        $record = $this->_filterFields($data, $this->_writableFields);
        $record['date_added'] = time();
        $record['updated']    = time();

        if (empty($record['product_code'])) {
            $record['product_code'] = $this->_generateProductCode($record['name']);
        }

        $productId = $this->_db->insert('CubeCart_inventory', $record);
        if (!$productId) {
            ApiResponse::error('Failed to create product', 'INTERNAL_ERROR', 500);
        }

        // Handle categories
        if (isset($data['categories']) && is_array($data['categories'])) {
            foreach ($data['categories'] as $cat) {
                $catId   = is_array($cat) ? (int)$cat['cat_id'] : (int)$cat;
                $primary = (is_array($cat) && isset($cat['primary'])) ? (int)$cat['primary'] : 0;
                $this->_db->insert('CubeCart_category_index', array(
                    'product_id' => $productId,
                    'cat_id'     => $catId,
                    'primary'    => $primary,
                ));
            }
        }

        // Return created product
        $result = $this->_db->select('CubeCart_inventory', false, array('product_id' => $productId));
        ApiResponse::created($this->_formatProduct($result[0]));
    }

    /**
     * PUT /products/{id} - update product
     */
    public function update($id)
    {
        $this->_requireWrite();

        if (!is_numeric($id)) {
            ApiResponse::error('Invalid product ID', 'BAD_REQUEST', 400);
        }

        // Check exists
        $existing = $this->_db->select('CubeCart_inventory', array('product_id'), array('product_id' => (int)$id));
        if (!$existing) {
            ApiResponse::error('Product not found', 'NOT_FOUND', 404);
        }

        $data   = $this->_getRequestBody();
        $record = $this->_filterFields($data, $this->_writableFields);
        if (empty($record)) {
            ApiResponse::error('No valid fields to update', 'BAD_REQUEST', 400);
        }

        $record['updated'] = time();
        $this->_db->update('CubeCart_inventory', $record, array('product_id' => (int)$id));

        // Update categories if provided
        if (isset($data['categories']) && is_array($data['categories'])) {
            $this->_db->delete('CubeCart_category_index', array('product_id' => (int)$id));
            foreach ($data['categories'] as $cat) {
                $catId   = is_array($cat) ? (int)$cat['cat_id'] : (int)$cat;
                $primary = (is_array($cat) && isset($cat['primary'])) ? (int)$cat['primary'] : 0;
                $this->_db->insert('CubeCart_category_index', array(
                    'product_id' => (int)$id,
                    'cat_id'     => $catId,
                    'primary'    => $primary,
                ));
            }
        }

        $result = $this->_db->select('CubeCart_inventory', false, array('product_id' => (int)$id));
        ApiResponse::success($this->_formatProduct($result[0]));
    }

    /**
     * DELETE /products/{id}
     */
    public function delete($id)
    {
        $this->_requireDelete();

        if (!is_numeric($id)) {
            ApiResponse::error('Invalid product ID', 'BAD_REQUEST', 400);
        }

        $existing = $this->_db->select('CubeCart_inventory', array('product_id'), array('product_id' => (int)$id));
        if (!$existing) {
            ApiResponse::error('Product not found', 'NOT_FOUND', 404);
        }

        // Delete related data
        $this->_db->delete('CubeCart_category_index', array('product_id' => (int)$id));
        $this->_db->delete('CubeCart_image_index', array('product_id' => (int)$id));
        $this->_db->delete('CubeCart_option_assign', array('product' => (int)$id));
        $this->_db->delete('CubeCart_reviews', array('product_id' => (int)$id));
        $this->_db->delete('CubeCart_inventory', array('product_id' => (int)$id));

        ApiResponse::noContent();
    }

    /**
     * Sub-resource routing
     */
    public function subList($id, $subResource)
    {
        $this->_requireRead();
        if (!is_numeric($id)) {
            ApiResponse::error('Invalid product ID', 'BAD_REQUEST', 400);
        }

        switch ($subResource) {
            case 'images':
                $this->_listImages((int)$id);
                return;
            case 'options':
                $this->_listOptions((int)$id);
                return;
            case 'reviews':
                $this->_listReviews((int)$id);
                return;
        }
        parent::subList($id, $subResource);
    }

    public function subCreate($id, $subResource)
    {
        $this->_requireWrite();
        if (!is_numeric($id)) {
            ApiResponse::error('Invalid product ID', 'BAD_REQUEST', 400);
        }

        switch ($subResource) {
            case 'images':
                $this->_addImage((int)$id);
                return;
            case 'options':
                $this->_addOption((int)$id);
                return;
        }
        parent::subCreate($id, $subResource);
    }

    public function subDelete($id, $subResource, $subId)
    {
        $this->_requireDelete();
        if (!is_numeric($id)) {
            ApiResponse::error('Invalid product ID', 'BAD_REQUEST', 400);
        }

        switch ($subResource) {
            case 'images':
                if (!$subId || !is_numeric($subId)) {
                    ApiResponse::error('Image file_id required', 'BAD_REQUEST', 400);
                }
                $this->_db->delete('CubeCart_image_index', array('product_id' => (int)$id, 'file_id' => (int)$subId));
                ApiResponse::noContent();
                return;
            case 'options':
                if (!$subId || !is_numeric($subId)) {
                    ApiResponse::error('Option assign ID required', 'BAD_REQUEST', 400);
                }
                $this->_db->delete('CubeCart_option_assign', array('product' => (int)$id, 'assign_id' => (int)$subId));
                ApiResponse::noContent();
                return;
        }
        parent::subDelete($id, $subResource, $subId);
    }

    // ======= Private helpers =======

    /**
     * List products filtered by category (requires JOIN)
     */
    private function _listByCategory($catId, $where, $sort, $pagination)
    {
        $prefix = $GLOBALS['config']->get('config', 'dbprefix');
        $whereStr = '`CI`.`cat_id` = ' . (int)$catId . ' AND `CI`.`product_id` = `I`.`product_id`';
        if (isset($where['status'])) {
            $whereStr .= ' AND `I`.`status` = ' . (int)$where['status'];
        }

        $total = $this->_db->count('`' . $prefix . 'CubeCart_category_index` AS `CI` INNER JOIN `' . $prefix . 'CubeCart_inventory` AS `I`', '`CI`.`product_id`', $whereStr);

        $result = $this->_db->select(
            '`' . $prefix . 'CubeCart_category_index` AS `CI` INNER JOIN `' . $prefix . 'CubeCart_inventory` AS `I`',
            '`I`.*',
            $whereStr,
            $sort,
            $pagination['per_page'],
            $pagination['page']
        );

        $data = array();
        if ($result) {
            foreach ($result as $product) {
                $data[] = $this->_formatProduct($product);
            }
        }

        ApiResponse::paginated($data, $pagination['page'], $pagination['per_page'], (int)$total);
    }

    /**
     * Format product data for API output
     */
    private function _formatProduct($product, $full = false)
    {
        // Remove excluded fields
        foreach ($this->_excludeFields as $field) {
            unset($product[$field]);
        }

        // Cast numeric fields
        $intFields = array('product_id', 'stock_level', 'stock_warning', 'status', 'digital',
            'featured', 'latest', 'manufacturer', 'tax_type', 'tax_inclusive', 'popularity', 'noindex');
        foreach ($intFields as $f) {
            if (isset($product[$f])) {
                $product[$f] = (int)$product[$f];
            }
        }

        $floatFields = array('price', 'sale_price', 'cost_price', 'product_weight');
        foreach ($floatFields as $f) {
            if (isset($product[$f])) {
                $product[$f] = (float)$product[$f];
            }
        }

        // Decode spec_array if present
        if (isset($product['spec_array']) && !empty($product['spec_array']) && !is_array($product['spec_array'])) {
            $product['spec_array'] = json_decode(base64_decode($product['spec_array']), true);
            if (!is_array($product['spec_array'])) {
                $product['spec_array'] = array();
            }
        }

        return $product;
    }

    /**
     * List product images
     */
    private function _listImages($productId)
    {
        $images = $this->_db->select('CubeCart_image_index', false, array('product_id' => $productId), array('main_img' => 'DESC'));
        $data = array();
        if ($images) {
            foreach ($images as $img) {
                $fileData = $this->_db->select('CubeCart_filemanager', false, array('file_id' => $img['file_id']));
                $data[] = array(
                    'file_id'  => (int)$img['file_id'],
                    'main_img' => (int)$img['main_img'],
                    'filepath' => $fileData ? $fileData[0]['filepath'] : '',
                    'filename' => $fileData ? $fileData[0]['filename'] : '',
                );
            }
        }
        ApiResponse::success($data);
    }

    /**
     * Add image to product
     */
    private function _addImage($productId)
    {
        $data = $this->_getRequestBody();
        $this->_validateRequired($data, array('file_id'));
        $mainImg = isset($data['main_img']) ? (int)$data['main_img'] : 0;

        // If setting as main, clear existing main
        if ($mainImg) {
            $this->_db->update('CubeCart_image_index', array('main_img' => 0), array('product_id' => $productId));
        }

        $this->_db->insert('CubeCart_image_index', array(
            'product_id' => $productId,
            'file_id'    => (int)$data['file_id'],
            'main_img'   => $mainImg,
        ));

        ApiResponse::created(array(
            'product_id' => $productId,
            'file_id'    => (int)$data['file_id'],
            'main_img'   => $mainImg,
        ));
    }

    /**
     * List product options
     */
    private function _listOptions($productId)
    {
        $assigns = $this->_db->select('CubeCart_option_assign', false, array('product' => $productId));
        $data = array();
        if ($assigns) {
            foreach ($assigns as $opt) {
                $group = $this->_db->select('CubeCart_option_group', false, array('option_id' => $opt['option_id']));
                $data[] = array(
                    'assign_id'       => (int)$opt['assign_id'],
                    'option_id'       => (int)$opt['option_id'],
                    'option_name'     => $group ? $group[0]['option_name'] : '',
                    'option_type'     => $group ? (int)$group[0]['option_type'] : 0,
                    'option_required' => (int)$opt['option_required'],
                );
            }
        }
        ApiResponse::success($data);
    }

    /**
     * Add option to product
     */
    private function _addOption($productId)
    {
        $data = $this->_getRequestBody();
        $this->_validateRequired($data, array('option_id'));

        $assignId = $this->_db->insert('CubeCart_option_assign', array(
            'product'         => $productId,
            'option_id'       => (int)$data['option_id'],
            'option_required' => isset($data['option_required']) ? (int)$data['option_required'] : 0,
        ));

        ApiResponse::created(array(
            'assign_id' => (int)$assignId,
            'product'   => $productId,
            'option_id' => (int)$data['option_id'],
        ));
    }

    /**
     * List product reviews
     */
    private function _listReviews($productId)
    {
        $pagination = $this->_getPagination();
        $total = $this->_db->count('CubeCart_reviews', 'id', array('product_id' => $productId));
        $reviews = $this->_db->select('CubeCart_reviews', false, array('product_id' => $productId), array('time' => 'DESC'), $pagination['per_page'], $pagination['page']);

        $data = array();
        if ($reviews) {
            foreach ($reviews as $r) {
                $r['id'] = (int)$r['id'];
                $r['product_id'] = (int)$r['product_id'];
                $r['rating'] = (int)$r['rating'];
                $r['approved'] = (int)$r['approved'];
                $data[] = $r;
            }
        }

        ApiResponse::paginated($data, $pagination['page'], $pagination['per_page'], (int)$total);
    }

    /**
     * Generate a product code from name
     */
    private function _generateProductCode($name)
    {
        $code = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 6));
        $code .= '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        return $code;
    }
}
