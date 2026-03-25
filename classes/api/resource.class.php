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
 * Abstract base class for API resource controllers
 */
abstract class ApiResource
{
    protected $_auth;
    protected $_db;
    protected $_resourceName = '';

    /**
     * Maximum items per page
     */
    const MAX_PER_PAGE = 100;

    /**
     * Default items per page
     */
    const DEFAULT_PER_PAGE = 20;

    public function __construct(ApiAuth $auth)
    {
        $this->_auth = $auth;
        $this->_db   = Database::getInstance();
    }

    /**
     * GET collection - must be implemented by subclass
     */
    public function listing()
    {
        ApiResponse::error('Listing not implemented for this resource', 'NOT_IMPLEMENTED', 501);
    }

    /**
     * GET single item - must be implemented by subclass
     */
    public function get($id)
    {
        ApiResponse::error('Get not implemented for this resource', 'NOT_IMPLEMENTED', 501);
    }

    /**
     * POST create - must be implemented by subclass
     */
    public function create()
    {
        ApiResponse::error('Create not implemented for this resource', 'NOT_IMPLEMENTED', 501);
    }

    /**
     * PUT/PATCH update - must be implemented by subclass
     */
    public function update($id)
    {
        ApiResponse::error('Update not implemented for this resource', 'NOT_IMPLEMENTED', 501);
    }

    /**
     * PUT/PATCH batch update - override in subclass if supported
     */
    public function updateBatch()
    {
        ApiResponse::error('Batch update not supported for this resource', 'METHOD_NOT_ALLOWED', 405);
    }

    /**
     * DELETE - must be implemented by subclass
     */
    public function delete($id)
    {
        ApiResponse::error('Delete not implemented for this resource', 'NOT_IMPLEMENTED', 501);
    }

    /**
     * Sub-resource list (e.g. GET /products/42/images)
     */
    public function subList($id, $subResource)
    {
        ApiResponse::error("Sub-resource '$subResource' not found", 'NOT_FOUND', 404);
    }

    /**
     * Sub-resource get single (e.g. GET /products/42/images/5)
     */
    public function subGet($id, $subResource, $subId)
    {
        ApiResponse::error("Sub-resource '$subResource' not found", 'NOT_FOUND', 404);
    }

    /**
     * Sub-resource create (e.g. POST /products/42/images)
     */
    public function subCreate($id, $subResource)
    {
        ApiResponse::error("Sub-resource '$subResource' not found", 'NOT_FOUND', 404);
    }

    /**
     * Sub-resource update (e.g. PUT /products/42/images/5)
     */
    public function subUpdate($id, $subResource, $subId)
    {
        ApiResponse::error("Sub-resource '$subResource' not found", 'NOT_FOUND', 404);
    }

    /**
     * Sub-resource delete (e.g. DELETE /products/42/images/5)
     */
    public function subDelete($id, $subResource, $subId)
    {
        ApiResponse::error("Sub-resource '$subResource' not found", 'NOT_FOUND', 404);
    }

    // ======= Helper methods =======

    /**
     * Parse and validate pagination parameters from query string
     * @return array ['page' => int, 'per_page' => int, 'offset' => int]
     */
    protected function _getPagination()
    {
        $page    = max(1, (int)(isset($_GET['page']) ? $_GET['page'] : 1));
        $perPage = (int)(isset($_GET['per_page']) ? $_GET['per_page'] : self::DEFAULT_PER_PAGE);
        $perPage = max(1, min($perPage, self::MAX_PER_PAGE));
        $offset  = ($page - 1) * $perPage;

        return array(
            'page'     => $page,
            'per_page' => $perPage,
            'offset'   => $offset,
        );
    }

    /**
     * Parse sort parameters
     * @param array $allowedFields Fields that can be sorted by
     * @param string $defaultSort Default sort field
     * @return array|false Sort array for DB query or false
     */
    protected function _getSort($allowedFields, $defaultSort = null)
    {
        $sort  = isset($_GET['sort']) ? $_GET['sort'] : $defaultSort;
        $order = isset($_GET['order']) ? strtoupper($_GET['order']) : 'ASC';

        if ($sort === null) {
            return false;
        }
        if (!in_array($sort, $allowedFields)) {
            return false;
        }
        if (!in_array($order, array('ASC', 'DESC'))) {
            $order = 'ASC';
        }

        return array($sort => $order);
    }

    /**
     * Get the JSON request body
     * @return array Decoded JSON body
     */
    protected function _getRequestBody()
    {
        $input = file_get_contents('php://input');
        if (empty($input)) {
            return array();
        }
        $data = json_decode($input, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            ApiResponse::error('Invalid JSON in request body: ' . json_last_error_msg(), 'INVALID_JSON', 400);
        }
        return is_array($data) ? $data : array();
    }

    /**
     * Validate that required fields are present in data
     * @param array $data Input data
     * @param array $required Required field names
     */
    protected function _validateRequired($data, $required)
    {
        $missing = array();
        foreach ($required as $field) {
            if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
                $missing[] = $field;
            }
        }
        if (!empty($missing)) {
            ApiResponse::error('Missing required fields: ' . implode(', ', $missing), 'VALIDATION_ERROR', 422);
        }
    }

    /**
     * Filter input data to only include allowed fields
     * @param array $data Input data
     * @param array $allowed Allowed field names
     * @return array Filtered data
     */
    protected function _filterFields($data, $allowed)
    {
        return array_intersect_key($data, array_flip($allowed));
    }

    /**
     * Apply sparse fieldsets - filter output to requested fields only
     * @param array $data Output data
     * @return array Filtered data
     */
    protected function _applySparseFields($data)
    {
        if (!isset($_GET['fields']) || empty($_GET['fields'])) {
            return $data;
        }
        $fields = array_map('trim', explode(',', $_GET['fields']));
        return array_intersect_key($data, array_flip($fields));
    }

    /**
     * Build a WHERE clause array from query string filters
     * @param array $filterMap Map of query param => DB column
     * @return array WHERE clause for DB queries
     */
    protected function _buildFilters($filterMap)
    {
        $where = array();
        foreach ($filterMap as $param => $column) {
            if (isset($_GET[$param]) && $_GET[$param] !== '') {
                $where[$column] = $_GET[$param];
            }
        }
        return $where;
    }

    /**
     * Build search WHERE clause
     * @param array $searchColumns Columns to search in
     * @return array WHERE clause for LIKE search or empty array
     */
    protected function _buildSearch($searchColumns)
    {
        if (!isset($_GET['search']) || trim($_GET['search']) === '') {
            return array();
        }
        $term = trim($_GET['search']);
        return array('~' . $term => $searchColumns);
    }

    /**
     * Build range filter WHERE clauses
     * @param array $rangeMap Map of query param => ['column' => col, 'op' => '>' or '<']
     * @return array WHERE clauses
     */
    protected function _buildRangeFilters($rangeMap)
    {
        $where = array();
        foreach ($rangeMap as $param => $config) {
            if (isset($_GET[$param]) && $_GET[$param] !== '') {
                $where[$config['op']] = array($config['column'] => $_GET[$param]);
            }
        }
        return $where;
    }

    /**
     * Require read permission for this resource
     */
    protected function _requireRead()
    {
        $this->_auth->checkPermission($this->_resourceName, 'r');
    }

    /**
     * Require write permission for this resource
     */
    protected function _requireWrite()
    {
        $this->_auth->checkPermission($this->_resourceName, 'w');
    }

    /**
     * Require delete permission for this resource
     */
    protected function _requireDelete()
    {
        $this->_auth->checkPermission($this->_resourceName, 'd');
    }
}
