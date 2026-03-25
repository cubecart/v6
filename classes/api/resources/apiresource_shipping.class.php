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
 * Shipping API resource controller
 */
class ApiResource_Shipping extends ApiResource
{
    protected $_resourceName = 'shipping';

    /**
     * GET /shipping - list shipping methods and rates
     */
    public function listing()
    {
        $this->_requireRead();

        $data = array(
            'methods' => $this->_getMethods(),
        );

        ApiResponse::success($data);
    }

    /**
     * GET /shipping/{id}
     * Routes: methods, rates
     */
    public function get($id)
    {
        $this->_requireRead();

        switch ($id) {
            case 'methods':
                ApiResponse::success($this->_getMethods());
                return;
            case 'rates':
                $this->_listRates();
                return;
        }

        // Treat as a specific method lookup
        $result = $this->_db->select('CubeCart_modules', false, array('module_id' => (int)$id, 'module' => 'shipping'));
        if (!$result) {
            ApiResponse::error('Shipping method not found', 'NOT_FOUND', 404);
        }
        ApiResponse::success($result[0]);
    }

    /**
     * POST /shipping - create shipping rate
     */
    public function create()
    {
        $this->_requireWrite();
        $data = $this->_getRequestBody();

        $allowed = array('name', 'value', 'status', 'weight_min', 'weight_max', 'countries', 'zones');
        $record = $this->_filterFields($data, $allowed);

        $rateId = $this->_db->insert('CubeCart_shipping_prices', $record);
        if (!$rateId) {
            ApiResponse::error('Failed to create shipping rate', 'INTERNAL_ERROR', 500);
        }

        $result = $this->_db->select('CubeCart_shipping_prices', false, array('id' => $rateId));
        ApiResponse::created($result ? $result[0] : array('id' => $rateId));
    }

    /**
     * PUT /shipping/{id} - update rate
     */
    public function update($id)
    {
        $this->_requireWrite();

        if (!is_numeric($id)) {
            ApiResponse::error('Invalid rate ID', 'BAD_REQUEST', 400);
        }

        $existing = $this->_db->select('CubeCart_shipping_prices', false, array('id' => (int)$id));
        if (!$existing) {
            ApiResponse::error('Shipping rate not found', 'NOT_FOUND', 404);
        }

        $data = $this->_getRequestBody();
        $allowed = array('name', 'value', 'status', 'weight_min', 'weight_max', 'countries', 'zones');
        $record = $this->_filterFields($data, $allowed);

        if (empty($record)) {
            ApiResponse::error('No valid fields to update', 'BAD_REQUEST', 400);
        }

        $this->_db->update('CubeCart_shipping_prices', $record, array('id' => (int)$id));

        $result = $this->_db->select('CubeCart_shipping_prices', false, array('id' => (int)$id));
        ApiResponse::success($result[0]);
    }

    /**
     * DELETE /shipping/{id}
     */
    public function delete($id)
    {
        $this->_requireDelete();

        if (!is_numeric($id)) {
            ApiResponse::error('Invalid rate ID', 'BAD_REQUEST', 400);
        }

        $existing = $this->_db->select('CubeCart_shipping_prices', false, array('id' => (int)$id));
        if (!$existing) {
            ApiResponse::error('Shipping rate not found', 'NOT_FOUND', 404);
        }

        $this->_db->delete('CubeCart_shipping_prices', array('id' => (int)$id));
        ApiResponse::noContent();
    }

    /**
     * Sub-resource: POST /shipping/quote
     */
    public function subCreate($id, $subResource)
    {
        if ($id === 'quote' || $subResource === 'quote') {
            $this->_requireRead();
            $data = $this->_getRequestBody();
            // Return available shipping methods - basic implementation
            $methods = $this->_getMethods();
            ApiResponse::success(array(
                'methods' => $methods,
                'note'    => 'Full quote calculation requires basket context. Use the storefront for accurate quotes.',
            ));
            return;
        }
        parent::subCreate($id, $subResource);
    }

    // ======= Private helpers =======

    private function _getMethods()
    {
        $modules = $this->_db->select('CubeCart_modules', false, array('module' => 'shipping'));
        return $modules ?: array();
    }

    private function _listRates()
    {
        $pagination = $this->_getPagination();
        $total = $this->_db->count('CubeCart_shipping_prices', 'id');
        $rates = $this->_db->select('CubeCart_shipping_prices', false, false, false, $pagination['per_page'], $pagination['page']);
        ApiResponse::paginated($rates ?: array(), $pagination['page'], $pagination['per_page'], (int)$total);
    }
}
