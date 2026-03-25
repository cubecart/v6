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
 * Categories API resource controller
 */
class ApiResource_Categories extends ApiResource
{
    protected $_resourceName = 'categories';

    private $_writableFields = array(
        'cat_name', 'cat_desc', 'cat_parent_id', 'status', 'hide', 'priority',
        'seo_meta_title', 'seo_meta_description', 'seo_meta_keywords',
        'cat_image', 'guest_access',
    );

    /**
     * GET /categories - list categories (flat) or GET /categories/tree for nested tree
     */
    public function listing()
    {
        $this->_requireRead();
        $pagination = $this->_getPagination();

        $where = array();
        if (isset($_GET['status'])) {
            $where['status'] = (int)$_GET['status'];
        }
        if (isset($_GET['parent_id'])) {
            $where['cat_parent_id'] = (int)$_GET['parent_id'];
        }

        $search = $this->_buildSearch(array('cat_name', 'cat_desc'));
        $where = array_merge($where, $search);

        $sort = $this->_getSort(array('cat_id', 'cat_name', 'priority', 'status'), 'priority');

        $total = $this->_db->count('CubeCart_category', 'cat_id', $where);
        $categories = $this->_db->select('CubeCart_category', false, $where, $sort, $pagination['per_page'], $pagination['page']);

        $data = array();
        if ($categories) {
            foreach ($categories as $cat) {
                $data[] = $this->_formatCategory($cat);
            }
        }

        ApiResponse::paginated($data, $pagination['page'], $pagination['per_page'], (int)$total);
    }

    /**
     * GET /categories/{id}
     * Special case: GET /categories/tree returns nested tree
     */
    public function get($id)
    {
        $this->_requireRead();

        // Handle /categories/tree
        if ($id === 'tree') {
            $this->_getTree();
            return;
        }

        if (!is_numeric($id)) {
            ApiResponse::error('Invalid category ID', 'BAD_REQUEST', 400);
        }

        $result = $this->_db->select('CubeCart_category', false, array('cat_id' => (int)$id));
        if (!$result) {
            ApiResponse::error('Category not found', 'NOT_FOUND', 404);
        }

        $category = $this->_formatCategory($result[0]);

        // Include product count
        $category['product_count'] = (int)$this->_db->count('CubeCart_category_index', 'product_id', array('cat_id' => (int)$id));

        ApiResponse::success($this->_applySparseFields($category));
    }

    /**
     * POST /categories - create
     */
    public function create()
    {
        $this->_requireWrite();
        $data = $this->_getRequestBody();
        $this->_validateRequired($data, array('cat_name'));

        $record = $this->_filterFields($data, $this->_writableFields);
        if (!isset($record['cat_parent_id'])) {
            $record['cat_parent_id'] = 0;
        }
        if (!isset($record['status'])) {
            $record['status'] = 1;
        }

        $catId = $this->_db->insert('CubeCart_category', $record);
        if (!$catId) {
            ApiResponse::error('Failed to create category', 'INTERNAL_ERROR', 500);
        }

        $result = $this->_db->select('CubeCart_category', false, array('cat_id' => $catId));
        ApiResponse::created($this->_formatCategory($result[0]));
    }

    /**
     * PUT /categories/{id} - update
     */
    public function update($id)
    {
        $this->_requireWrite();

        if (!is_numeric($id)) {
            ApiResponse::error('Invalid category ID', 'BAD_REQUEST', 400);
        }

        $existing = $this->_db->select('CubeCart_category', array('cat_id'), array('cat_id' => (int)$id));
        if (!$existing) {
            ApiResponse::error('Category not found', 'NOT_FOUND', 404);
        }

        $data   = $this->_getRequestBody();
        $record = $this->_filterFields($data, $this->_writableFields);
        if (empty($record)) {
            ApiResponse::error('No valid fields to update', 'BAD_REQUEST', 400);
        }

        $this->_db->update('CubeCart_category', $record, array('cat_id' => (int)$id));

        $result = $this->_db->select('CubeCart_category', false, array('cat_id' => (int)$id));
        ApiResponse::success($this->_formatCategory($result[0]));
    }

    /**
     * DELETE /categories/{id}
     */
    public function delete($id)
    {
        $this->_requireDelete();

        if (!is_numeric($id)) {
            ApiResponse::error('Invalid category ID', 'BAD_REQUEST', 400);
        }

        $existing = $this->_db->select('CubeCart_category', false, array('cat_id' => (int)$id));
        if (!$existing) {
            ApiResponse::error('Category not found', 'NOT_FOUND', 404);
        }

        // Reassign children to parent
        $parentId = (int)$existing[0]['cat_parent_id'];
        $this->_db->update('CubeCart_category', array('cat_parent_id' => $parentId), array('cat_parent_id' => (int)$id));

        // Remove category index entries
        $this->_db->delete('CubeCart_category_index', array('cat_id' => (int)$id));

        // Delete category
        $this->_db->delete('CubeCart_category', array('cat_id' => (int)$id));

        ApiResponse::noContent();
    }

    /**
     * Sub-resource: GET /categories/{id}/products
     */
    public function subList($id, $subResource)
    {
        if ($subResource === 'products') {
            $this->_requireRead();
            $this->_listProducts((int)$id);
            return;
        }
        parent::subList($id, $subResource);
    }

    // ======= Private helpers =======

    /**
     * Get nested category tree
     */
    private function _getTree()
    {
        $parentId = isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : 0;
        $allCats = $this->_db->select('CubeCart_category', false, array('status' => 1), array('priority' => 'ASC', 'cat_name' => 'ASC'));

        if (!$allCats) {
            ApiResponse::success(array());
            return;
        }

        // Build lookup by parent
        $byParent = array();
        foreach ($allCats as $cat) {
            $pid = (int)$cat['cat_parent_id'];
            if (!isset($byParent[$pid])) {
                $byParent[$pid] = array();
            }
            $byParent[$pid][] = $this->_formatCategory($cat);
        }

        $tree = $this->_buildTree($byParent, $parentId);
        ApiResponse::success($tree);
    }

    /**
     * Recursively build category tree
     */
    private function _buildTree(&$byParent, $parentId)
    {
        if (!isset($byParent[$parentId])) {
            return array();
        }
        $tree = array();
        foreach ($byParent[$parentId] as $cat) {
            $cat['children'] = $this->_buildTree($byParent, $cat['cat_id']);
            $tree[] = $cat;
        }
        return $tree;
    }

    /**
     * List products in a category
     */
    private function _listProducts($catId)
    {
        $pagination = $this->_getPagination();
        $prefix = $GLOBALS['config']->get('config', 'dbprefix');

        $whereStr = '`CI`.`cat_id` = ' . (int)$catId . ' AND `CI`.`product_id` = `I`.`product_id` AND `I`.`status` = 1';

        $total = $this->_db->count('`' . $prefix . 'CubeCart_category_index` AS `CI` INNER JOIN `' . $prefix . 'CubeCart_inventory` AS `I`', '`CI`.`product_id`', $whereStr);

        $result = $this->_db->select(
            '`' . $prefix . 'CubeCart_category_index` AS `CI` INNER JOIN `' . $prefix . 'CubeCart_inventory` AS `I`',
            '`I`.*',
            $whereStr,
            array('name' => 'ASC'),
            $pagination['per_page'],
            $pagination['page']
        );

        $data = array();
        if ($result) {
            foreach ($result as $product) {
                $data[] = $product;
            }
        }

        ApiResponse::paginated($data, $pagination['page'], $pagination['per_page'], (int)$total);
    }

    /**
     * Format category for API output
     */
    private function _formatCategory($cat)
    {
        $intFields = array('cat_id', 'cat_parent_id', 'status', 'hide', 'priority');
        foreach ($intFields as $f) {
            if (isset($cat[$f])) {
                $cat[$f] = (int)$cat[$f];
            }
        }
        return $cat;
    }
}
